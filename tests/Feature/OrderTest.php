<?php

use App\Livewire\Checkout;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Livewire\Livewire;

test('checkout redirects to cart when cart is empty', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->assertRedirect(route('cart'));
});

test('checkout renders with items in cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->assertOk()
        ->assertSet('subtotal', 200);
});

test('placing an order creates order, items, decrements stock and clears cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'stock' => 10]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertRedirect();

    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->user_id)->toBe($user->id)
        ->and($order->subtotal)->toBe(200)
        ->and($order->total)->toBe(240)
        ->and($order->delivery_fee)->toBe(40)
        ->and($order->status)->toBe('pending')
        ->and($order->payment_method)->toBe('cod')
        ->and($order->items->count())->toBe(1)
        ->and($order->items->first()->product_name)->toBe($product->name)
        ->and($product->fresh()->stock)->toBe(8)
        ->and($user->cartItems()->count())->toBe(0);
});

test('free delivery above threshold', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 600]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->assertSet('deliveryFee', 0);
});

test('order can be cancelled and restocks items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'stock' => 5]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertRedirect();

    $order = Order::first();

    expect(app(OrderService::class)->cancel($order))->toBeTrue()
        ->and($order->fresh()->status)->toBe('cancelled')
        ->and($product->fresh()->stock)->toBe(5);
});

test('delivered orders cannot be cancelled', function () {
    $user = User::factory()->create();
    $order = Order::factory()->delivered()->create(['user_id' => $user->id]);

    expect(app(OrderService::class)->cancel($order))->toBeFalse()
        ->and($order->fresh()->status)->toBe('delivered');
});

test('user only sees their own orders', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Order::factory()->create(['user_id' => $user->id]);
    Order::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user)->get('/orders')->assertOk()->assertSee(Order::where('user_id', $user->id)->first()->order_number);
});

test('user cannot view another users order', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)->get('/orders/'.$order->id)->assertForbidden();
});

test('order creation requires a non-empty cart', function () {
    $user = User::factory()->create();

    expect(fn () => app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Rahul',
        'receiver_phone' => '9876501234',
        'address_line' => '12 Main Street',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'pincode' => '400001',
    ]))->toThrow(RuntimeException::class);
});
