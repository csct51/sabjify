<?php

use App\Enums\DeliverySlot;
use App\Livewire\Checkout;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('checkout defaults delivery slot to morning', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->assertSet('deliverySlot', 'morning');
});

test('placing an order persists delivery slot', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('paymentMethod', 'cod')
        ->set('deliverySlot', 'evening')
        ->call('placeOrder')
        ->assertHasNoErrors();

    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->delivery_slot)->toBe('evening');
});

test('placing an order defaults to morning when not changed', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertHasNoErrors();

    expect(Order::first()->delivery_slot)->toBe('morning');
});

test('checkout rejects invalid delivery slot', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('paymentMethod', 'cod')
        ->set('deliverySlot', 'night')
        ->call('placeOrder')
        ->assertHasErrors('deliverySlot');
});

test('delivery slot enum labels are correct', function () {
    expect(DeliverySlot::Morning->label())->toBe('Morning — 8 AM to 12 PM')
        ->and(DeliverySlot::Evening->label())->toBe('Evening — 6 PM to 9 PM')
        ->and(DeliverySlot::tryFrom('morning'))->toBe(DeliverySlot::Morning)
        ->and(DeliverySlot::tryFrom('evening'))->toBe(DeliverySlot::Evening);
});
