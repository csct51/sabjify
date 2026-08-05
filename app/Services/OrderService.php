<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromCart(User $user, array $data): Order
    {
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            throw new \RuntimeException('Your cart is empty.');
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);
        $deliveryFee = $subtotal >= config('mart.free_delivery_threshold') ? 0 : (int) config('mart.delivery_fee');

        return DB::transaction(function () use ($user, $cartItems, $subtotal, $deliveryFee, $data) {
            $order = $user->orders()->create([
                'order_number' => $this->generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount' => 0,
                'total' => $subtotal + $deliveryFee,
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_method'] === 'cod' ? 'pending' : 'paid',
                'receiver_name' => $data['receiver_name'],
                'receiver_phone' => $data['receiver_phone'],
                'address_line' => $data['address_line'],
                'city' => $data['city'],
                'state' => $data['state'],
                'pincode' => $data['pincode'],
                'label' => $data['label'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit' => $product->unit,
                    'price' => $product->price,
                    'quantity' => $cartItem->quantity,
                    'total' => $product->price * $cartItem->quantity,
                ]);

                $product->decrement('stock', $cartItem->quantity);
            }

            $user->cartItems()->delete();

            return $order;
        });
    }

    public function cancel(Order $order, ?string $reason = null, ?string $cancelledBy = null): bool
    {
        if (! in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED], true)) {
            return false;
        }

        $order->update([
            'status' => Order::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_reason' => $reason,
            'cancelled_by' => $cancelledBy,
        ]);

        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        return true;
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.strtoupper(Str::random(8));
    }
}
