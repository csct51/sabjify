<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentController extends Controller
{
    public function verify(Order $order, Request $request): JsonResponse
    {
        abort_unless($order->user_id === auth('web')->id(), 403);
        abort_unless(
            $order->payment_method === 'online'
            && in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED], true),
            422
        );

        if ($order->payment_status === 'paid') {
            return response()->json(['success' => true]);
        }

        $validated = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        if ($validated['razorpay_order_id'] !== $order->payment_reference) {
            Log::warning('Razorpay order id mismatch.', ['order_id' => $order->id]);

            return response()->json(['success' => false, 'message' => 'Payment could not be verified.'], 422);
        }

        $verified = app(RazorpayService::class)->verifySignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature'],
        );

        if (! $verified) {
            Log::warning('Invalid Razorpay signature.', ['order_id' => $order->id]);

            return response()->json(['success' => false, 'message' => 'Payment could not be verified.'], 422);
        }

        $order->update([
            'payment_status' => 'paid',
            'payment_id' => $validated['razorpay_payment_id'],
            'payment_details' => $this->paymentDetails($validated['razorpay_payment_id']),
        ]);

        return response()->json(['success' => true]);
    }

    public function verifyCheckout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $payload = session('pending_payment_'.$validated['razorpay_order_id']);

        abort_unless(is_array($payload), 422);
        abort_unless(($payload['user_id'] ?? null) === auth('web')->id(), 403);

        $verified = app(RazorpayService::class)->verifySignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature'],
        );

        if (! $verified) {
            Log::warning('Invalid Razorpay signature during checkout.', ['user_id' => auth('web')->id()]);

            return response()->json(['success' => false, 'message' => 'Payment could not be verified.'], 422);
        }

        if (isset($payload['order_id']) && is_int($payload['order_id'])) {
            $order = Order::find($payload['order_id']);

            if ($order && $order->user_id === auth('web')->id()) {
                session()->flash('success', 'Payment successful. Order no: '.$order->order_number);

                return response()->json([
                    'success' => true,
                    'order_id' => $order->id,
                    'redirect' => route('orders.show', $order),
                ]);
            }
        }

        $user = auth('web')->user();

        if ($user->cartItems()->count() === 0) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 422);
        }

        $data = $payload['address'];
        $data['payment_method'] = 'online';

        $order = app(OrderService::class)->createFromCart($user, $data);

        $order->update([
            'payment_status' => 'paid',
            'payment_reference' => $validated['razorpay_order_id'],
            'payment_id' => $validated['razorpay_payment_id'],
            'payment_details' => $this->paymentDetails($validated['razorpay_payment_id']),
        ]);

        session(['pending_payment_'.$validated['razorpay_order_id'] => array_merge($payload, ['order_id' => $order->id])]);

        session()->flash('success', 'Payment successful. Order no: '.$order->order_number);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'redirect' => route('orders.show', $order),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function paymentDetails(string $paymentId): ?array
    {
        try {
            return app(RazorpayService::class)->fetchPayment($paymentId);
        } catch (Throwable $e) {
            Log::warning('Could not fetch Razorpay payment details.', ['payment_id' => $paymentId]);

            return null;
        }
    }
}
