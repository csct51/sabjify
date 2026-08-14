<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromCart(User $user, array $data): Order
    {
        $cartItems = $user->cartItems()->with('product', 'productUnit', 'basket')->get();

        if ($cartItems->isEmpty()) {
            throw new \RuntimeException('Your cart is empty.');
        }

        $outOfStock = $cartItems
            ->filter(fn ($item) => $item->product && ! $item->product->inStock())
            ->map(fn ($item) => $item->product->name)
            ->unique()
            ->values();

        if ($outOfStock->isNotEmpty()) {
            throw new \RuntimeException('Some items are out of stock: '.$outOfStock->implode(', '));
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->total());
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
                'payment_status' => 'pending',
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
                if ($cartItem->basket) {
                    $basket = $cartItem->basket;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => null,
                        'basket_id' => $basket->id,
                        'product_name' => $basket->name,
                        'unit' => null,
                        'price' => $basket->price,
                        'quantity' => $cartItem->quantity,
                        'total' => $basket->price * $cartItem->quantity,
                    ]);

                    continue;
                }

                $product = $cartItem->product;
                $unit = $cartItem->productUnit;
                $price = $unit?->price ?? $product->price;
                $unitName = $unit?->unit ?? $product->unit;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit' => $unitName,
                    'price' => $price,
                    'quantity' => $cartItem->quantity,
                    'total' => $price * $cartItem->quantity,
                ]);
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

        return true;
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'ORD-';

        $maxSuffix = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->pluck('order_number')
            ->map(fn (string $number): ?int => ($suffix = substr($number, strlen($prefix))) !== '' && ctype_digit($suffix) ? (int) $suffix : null)
            ->filter()
            ->max() ?? 0;

        return $prefix.str_pad((string) ($maxSuffix + 1), 3, '0', STR_PAD_LEFT);
    }
}
