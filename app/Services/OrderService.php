<?php

namespace App\Services;

use App\Models\Basket;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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
        $cartItems = $cartItems->filter(fn ($item) => $item->basket || $item->product);

        if ($cartItems->isEmpty()) {
            throw new \RuntimeException('Your cart is empty.');
        }

        $outOfStock = $cartItems
            ->filter(fn ($item) => $item->product && ! ($item->productUnit?->in_stock ?? $item->product->inStock()))
            ->map(fn ($item) => $item->product->name)
            ->unique()
            ->values();

        if ($outOfStock->isNotEmpty()) {
            throw new \RuntimeException('Some items are out of stock: '.$outOfStock->implode(', '));
        }

        $shortOnStock = $cartItems
            ->filter(fn ($item) => $item->product && ! $item->basket)
            ->filter(fn ($item) => $item->product->baseNeededFor($item->productUnit, (int) $item->quantity) > (float) $item->product->current_stock + 1e-9)
            ->map(fn ($item) => $item->product->name)
            ->unique()
            ->values();

        if ($shortOnStock->isNotEmpty()) {
            throw new \RuntimeException('Not enough stock for: '.$shortOnStock->implode(', '));
        }

        $shortBaskets = $cartItems
            ->filter(fn ($item) => $item->basket)
            ->filter(fn ($item) => ! $item->basket->is_active || ! $item->basket->constituentsInStock() || (int) $item->quantity > $item->basket->basketsSellable())
            ->map(fn ($item) => $item->basket->name)
            ->unique()
            ->values();

        if ($shortBaskets->isNotEmpty()) {
            throw new \RuntimeException('Not enough stock for baskets: '.$shortBaskets->implode(', '));
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
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'label' => $data['label'] ?? null,
                'notes' => $data['notes'] ?? null,
                'delivery_slot' => $data['delivery_slot'] ?? null,
            ]);

            foreach ($cartItems as $cartItem) {
                if ($cartItem->basket) {
                    $basket = $cartItem->basket;
                    $shares = $basket->constituentShares();

                    foreach ($shares as $productId => $sharePerBasket) {
                        $product = Product::whereKey($productId)->lockForUpdate()->first();

                        if (! $product) {
                            continue;
                        }

                        $need = round($sharePerBasket * (int) $cartItem->quantity, 3);

                        if ($need > (float) $product->current_stock + 1e-9) {
                            throw new \RuntimeException('Not enough stock for basket: '.$basket->name);
                        }

                        $product->decrement('current_stock', $need);
                    }

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

                $product = Product::whereKey($cartItem->product_id)->lockForUpdate()->first() ?? $cartItem->product;
                $unit = $cartItem->productUnit;
                $price = $unit?->price ?? $product->price;
                $unitName = $unit?->unit ?? $product->unit;
                $baseQty = $product->baseNeededFor($unit, (int) $cartItem->quantity);

                if ($baseQty > (float) $product->current_stock + 1e-9) {
                    throw new \RuntimeException('Not enough stock for: '.$product->name);
                }

                $product->decrement('current_stock', $baseQty);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit' => $unitName,
                    'price' => $price,
                    'quantity' => $cartItem->quantity,
                    'total' => $price * $cartItem->quantity,
                    'base_qty' => $baseQty,
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

        DB::transaction(function () use ($order, $reason, $cancelledBy) {
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_reason' => $reason,
                'cancelled_by' => $cancelledBy,
            ]);

            $order->loadMissing('items');

            foreach ($order->items as $item) {
                if ($item->basket_id && ! $item->product_id) {
                    $basket = $item->basket ?? Basket::find($item->basket_id);

                    if ($basket) {
                        foreach ($basket->constituentShares() as $productId => $sharePerBasket) {
                            $product = Product::whereKey($productId)->lockForUpdate()->first();

                            $product?->increment('current_stock', round($sharePerBasket * (int) $item->quantity, 3));
                        }
                    }

                    continue;
                }

                if (! $item->product_id) {
                    continue;
                }

                $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

                $product?->increment('current_stock', $item->soldBaseQty());
            }
        });

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
