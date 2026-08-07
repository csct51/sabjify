<?php

use App\Livewire\Cart;
use App\Livewire\ProductCard;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\User;
use Livewire\Livewire;

test('guest is redirected to login when adding to cart', function () {
    $product = Product::factory()->available()->create();

    Livewire::test(ProductCard::class, ['product' => $product])
        ->call('addToCart')
        ->assertRedirect(route('login'));
});

test('authenticated user can add a product to the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create();

    Livewire::actingAs($user)
        ->test(ProductCard::class, ['product' => $product])
        ->call('addToCart')
        ->assertSet('inCart', true)
        ->assertSet('quantity', 1);

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
});

test('cart increments and decrements quantity', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['stock' => 10]);

    $item = CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->call('increment', $item)
        ->assertOk();

    expect($item->fresh()->quantity)->toBe(3);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->call('decrement', $item)
        ->assertOk();

    expect($item->fresh()->quantity)->toBe(2);
});

test('cart quantity respects product stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['stock' => 3]);

    $item = CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 3]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->call('increment', $item)
        ->assertHasErrors('quantity');

    expect($item->fresh()->quantity)->toBe(3);
});

test('cart item can be removed', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create();

    $item = CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->call('remove', $item)
        ->assertOk();

    expect(CartItem::find($item->id))->toBeNull();
});

test('cart cannot be modified by another user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $product = Product::factory()->available()->create();

    $item = CartItem::factory()->create(['user_id' => $owner->id, 'product_id' => $product->id]);

    Livewire::actingAs($other)
        ->test(Cart::class)
        ->call('remove', $item)
        ->assertForbidden();

    expect(CartItem::find($item->id))->not->toBeNull();
});

test('cart page shows subtotal and free delivery above threshold', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 600]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->assertOk()
        ->assertSet('subtotal', 600)
        ->assertSet('deliveryFee', 0)
        ->assertSet('total', 600);
});

test('cart shows the recipe a product was added from', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['name' => 'Mango']);
    $recipe = Recipe::factory()->create(['title' => 'Mango Salad']);

    CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'recipe_id' => $recipe->id,
    ]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->assertOk()
        ->assertSee('From Mango Salad');
});
