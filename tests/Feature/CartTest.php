<?php

use App\Livewire\Cart;
use App\Livewire\ProductCard;
use App\Livewire\ProductUnitPicker;
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
    $product = Product::factory()->available()->create();

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

test('cart increments quantity without a stock cap', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create();

    $item = CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 3]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->call('increment', $item)
        ->assertOk();

    expect($item->fresh()->quantity)->toBe(4);
});

test('product card dispatches the global unit picker for multi-unit products', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->withUnits(3)->create();

    Livewire::actingAs($user)
        ->test(ProductCard::class, ['product' => $product])
        ->assertSee('product-unit-picker:open', false);
});

test('unit picker opens and lists available units', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->withUnits(3)->create();

    Livewire::actingAs($user)
        ->test(ProductUnitPicker::class)
        ->call('open', $product->id)
        ->assertSet('productId', $product->id)
        ->assertSet('selectedUnitId', $product->defaultUnit()->id)
        ->assertSee($product->name)
        ->assertSee('Select a size');
});

test('unit picker selects a unit and adds the chosen quantity to the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->withUnits(3)->create();
    $secondUnit = $product->units->sortBy('sort_order')->skip(1)->first();

    Livewire::actingAs($user)
        ->test(ProductUnitPicker::class)
        ->call('open', $product->id)
        ->call('selectUnit', $secondUnit->id)
        ->call('incrementQuantity')
        ->call('incrementQuantity')
        ->call('addToCart')
        ->assertSet('productId', null)
        ->assertDispatched('cart-updated')
        ->assertDispatched('product-unit-picker:close');

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_unit_id' => $secondUnit->id,
        'quantity' => 3,
    ]);
});

test('unit picker keeps different units as separate cart items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->withUnits(3)->create();
    $units = $product->units->sortBy('sort_order')->values();
    $firstUnit = $units->get(0);
    $secondUnit = $units->get(1);

    $picker = Livewire::actingAs($user)->test(ProductUnitPicker::class);

    $picker->call('open', $product->id)->call('addToCart');
    $picker->call('open', $product->id)->call('selectUnit', $secondUnit->id)->call('addToCart');

    expect($user->cartItems()->whereNull('recipe_id')->count())->toBe(2);
    expect($user->cartItems()->where('product_unit_id', $firstUnit->id)->first()->quantity)->toBe(1);
    expect($user->cartItems()->where('product_unit_id', $secondUnit->id)->first()->quantity)->toBe(1);
});

test('unit picker merges quantity with an existing cart item of the same unit', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->withUnits(3)->create();

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'product_unit_id' => $product->defaultUnit()->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(ProductUnitPicker::class)
        ->call('open', $product->id)
        ->call('incrementQuantity')
        ->call('incrementQuantity')
        ->call('addToCart');

    expect($user->cartItems()->where('product_unit_id', $product->defaultUnit()->id)->first()->quantity)->toBe(5);
});

test('unit picker redirects guests to login', function () {
    $product = Product::factory()->available()->withUnits(3)->create();

    Livewire::test(ProductUnitPicker::class)
        ->call('open', $product->id)
        ->call('addToCart')
        ->assertRedirect(route('login'));
});

test('product card increments only its mounted unit item', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->withUnits(3)->create();
    $units = $product->units->sortBy('sort_order')->values();
    $firstUnit = $units->get(0);
    $secondUnit = $units->get(1);

    $firstItem = CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'product_unit_id' => $firstUnit->id, 'quantity' => 1]);
    $secondItem = CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'product_unit_id' => $secondUnit->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(ProductCard::class, ['product' => $product])
        ->call('increment')
        ->assertOk();

    expect($firstItem->fresh()->quantity)->toBe(2);
    expect($secondItem->fresh()->quantity)->toBe(1);
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

test('cart groups products under the recipe they came from', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->available()->create(['name' => 'Mango']);
    $mint = Product::factory()->available()->create(['name' => 'Mint']);
    $standalone = Product::factory()->available()->create(['name' => 'Apple']);
    $recipe = Recipe::factory()->create(['title' => 'Mango Salad']);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $mango->id, 'recipe_id' => $recipe->id]);
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $mint->id, 'recipe_id' => $recipe->id]);
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $standalone->id]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->assertOk()
        ->assertSee('Mango Salad')
        ->assertSee('Mango')
        ->assertSee('Mint')
        ->assertSee('Apple')
        ->assertSet('cartGroups', function (array $groups) use ($recipe) {
            expect($groups)->toHaveCount(2);

            $recipeGroup = collect($groups)->first(fn (array $group) => $group['recipe'] !== null);

            expect($recipeGroup['recipe']->id)->toBe($recipe->id);
            expect($recipeGroup['items'])->toHaveCount(2);

            return true;
        });
});
