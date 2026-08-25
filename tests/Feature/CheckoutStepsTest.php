<?php

use App\Livewire\Checkout;
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
