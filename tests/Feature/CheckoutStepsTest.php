<?php

use App\Livewire\Checkout;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('checkout shows address payment review steps', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->available()->create();
    $unit = $product->units()->first();
    CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_unit_id' => $unit->id,
    ]);

    Livewire::test(Checkout::class)
        ->assertSee('Address')
        ->assertSee('Payment')
        ->assertSee('Review');
});

test('checkout wizard navigates through the steps', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Address::factory()->create([
        'user_id' => $user->id,
        'latitude' => 21.25,
        'longitude' => 81.65,
    ]);

    $product = Product::factory()->available()->create();
    $unit = $product->units()->first();
    CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_unit_id' => $unit->id,
    ]);

    Livewire::test(Checkout::class)
        ->assertSet('step', 1)
        ->assertDontSee('Order Summary')
        ->call('nextFromAddress')
        ->assertHasNoErrors()
        ->assertSet('step', 2)
        ->call('nextFromPayment')
        ->assertHasNoErrors()
        ->assertSet('step', 3)
        ->assertSee('Order Summary')
        ->assertSee('Review your order')
        ->assertSee('Deliver to')
        ->assertSee('Back to Payment');
});

test('checkout blocks continuing without an address', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->available()->create();
    $unit = $product->units()->first();
    CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_unit_id' => $unit->id,
    ]);

    Livewire::test(Checkout::class)
        ->set('addressMode', 'existing')
        ->set('addressId', null)
        ->call('nextFromAddress')
        ->assertHasErrors('address')
        ->assertSet('step', 1);
});

test('checkout stepper allows going back to a previous step', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Address::factory()->create([
        'user_id' => $user->id,
        'latitude' => 21.25,
        'longitude' => 81.65,
    ]);

    $product = Product::factory()->available()->create();
    $unit = $product->units()->first();
    CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_unit_id' => $unit->id,
    ]);

    Livewire::test(Checkout::class)
        ->call('nextFromAddress')
        ->assertSet('step', 2)
        ->call('backToStep', 1)
        ->assertSet('step', 1);
});
