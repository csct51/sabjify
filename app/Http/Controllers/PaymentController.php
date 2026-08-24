<?php

namespace App\Http\Controllers;

use App\Models\DeliveryLocation;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\RazorpayService;
use App\Support\Geo;
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

        $capturedAmount = $this->paymentDetails($validated['razorpay_payment_id'])['amount'] ?? null;

        if ($capturedAmount !== null && $capturedAmount !== $order->total * 100) {
            Log::warning('Razorpay captured amount mismatch.', ['order_id' => $order->id, 'expected' => $order->total * 100, 'captured' => $capturedAmount]);

            return response()->json(['success' => false, 'message' => 'Payment amount could not be verified.'], 422);
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

        $cartItems = $user->cartItems()->with('product', 'productUnit', 'basket')->get();
        $cartItems = $cartItems->filter(fn ($item) => $item->basket || $item->product);

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 422);
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->total());
        $deliveryFee = $subtotal === 0 ? 0 : ($subtotal >= (int) config('mart.free_delivery_threshold') ? 0 : (int) config('mart.delivery_fee'));
        $total = $subtotal + $deliveryFee;

        $expectedAmount = $payload['amount'] ?? null;
        if ($expectedAmount !== null && $expectedAmount !== $total * 100) {
            return response()->json(['success' => false, 'message' => 'Your cart changed during payment. Please try again.'], 422);
        }

        $capturedAmount = $this->paymentDetails($validated['razorpay_payment_id'])['amount'] ?? null;
        if ($capturedAmount !== null && $capturedAmount !== $total * 100) {
            Log::warning('Razorpay captured amount mismatch during checkout.', ['user_id' => $user->id, 'expected' => $total * 100, 'captured' => $capturedAmount]);

            return response()->json(['success' => false, 'message' => 'Payment amount could not be verified.'], 422);
        }

        $minimum = (int) config('mart.minimum_order_amount');
        if ($minimum > 0 && $subtotal < $minimum) {
            return response()->json(['success' => false, 'message' => 'Your order is below the minimum order amount.'], 422);
        }

        $latitude = $payload['address']['latitude'] ?? null;
        $longitude = $payload['address']['longitude'] ?? null;

        if ($latitude === null || $longitude === null) {
            return response()->json(['success' => false, 'message' => 'Delivery location is missing.'], 422);
        }

        if (! $this->isDeliverable((float) $latitude, (float) $longitude)) {
            return response()->json(['success' => false, 'message' => 'We don\'t deliver to this location yet.'], 422);
        }

        $data = $payload['address'];
        $data['payment_method'] = 'online';

        try {
            $order = app(OrderService::class)->createFromCart($user, $data);
        } catch (\RuntimeException $e) {
            Log::warning('Order creation failed during checkout verification.', ['user_id' => $user->id, 'message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

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

    private function isDeliverable(float $latitude, float $longitude): bool
    {
        $locations = DeliveryLocation::active()->get();

        if ($locations->isEmpty()) {
            return true;
        }

        return $locations->contains(
            fn (DeliveryLocation $location): bool => Geo::distanceKm($latitude, $longitude, (float) $location->latitude, (float) $location->longitude) <= (float) $location->radius_km,
        );
    }
}
