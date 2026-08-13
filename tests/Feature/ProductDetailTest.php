<?php

use App\Livewire\ProductDetail;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('product detail shows add to cart when the selected unit is not in the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->withUnits(3)->create();
    $units = $product->units->sortBy('sort_order')->values();
    $firstUnit = $units->get(0);
    $secondUnit = $units->get(1);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'product_unit_id' => $firstUnit->id, 'quantity' => 3]);

    Livewire::actingAs($user)
        ->test(ProductDetail::class, ['product' => $product])
        ->call('selectUnit', $secondUnit->id)
        ->assertSet('unitId', $secondUnit->id)
        ->assertSet('inCart', false)
        ->assertSet('quantity', 1)
        ->assertSee('Add to Cart');
});

test('product detail shows the quantity of the selected unit when it is in the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->withUnits(3)->create();
    $units = $product->units->sortBy('sort_order')->values();
    $firstUnit = $units->get(0);
    $secondUnit = $units->get(1);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'product_unit_id' => $firstUnit->id, 'quantity' => 5]);
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'product_unit_id' => $secondUnit->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(ProductDetail::class, ['product' => $product])
        ->call('selectUnit', $firstUnit->id)
        ->assertSet('inCart', true)
        ->assertSet('quantity', 5)
        ->call('selectUnit', $secondUnit->id)
        ->assertSet('inCart', true)
        ->assertSet('quantity', 2)
        ->assertSee('2');
});

test('product detail increments only the selected unit item', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->withUnits(3)->create();
    $units = $product->units->sortBy('sort_order')->values();
    $firstUnit = $units->get(0);
    $secondUnit = $units->get(1);

    $firstItem = CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'product_unit_id' => $firstUnit->id, 'quantity' => 1]);
    $secondItem = CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'product_unit_id' => $secondUnit->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(ProductDetail::class, ['product' => $product])
        ->call('selectUnit', $secondUnit->id)
        ->call('increment')
        ->assertSet('quantity', 2);

    expect($firstItem->fresh()->quantity)->toBe(1);
    expect($secondItem->fresh()->quantity)->toBe(2);
});

test('product detail decrement removes the selected unit item when it reaches zero', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->withUnits(3)->create();
    $units = $product->units->sortBy('sort_order')->values();
    $firstUnit = $units->get(0);
    $secondUnit = $units->get(1);

    $firstItem = CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'product_unit_id' => $firstUnit->id, 'quantity' => 3]);
    $secondItem = CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'product_unit_id' => $secondUnit->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(ProductDetail::class, ['product' => $product])
        ->call('selectUnit', $secondUnit->id)
        ->call('decrement')
        ->assertSet('inCart', false)
        ->assertSet('quantity', 1);

    expect($firstItem->fresh()->quantity)->toBe(3);
    expect(CartItem::find($secondItem->id))->toBeNull();
});
