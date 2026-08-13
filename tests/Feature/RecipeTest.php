<?php

use App\Livewire\Admin\RecipeForm;
use App\Livewire\Admin\Recipes;
use App\Livewire\RecipeShow;
use App\Models\Admin;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\User;
use Livewire\Livewire;

test('guest is redirected to admin login when accessing recipes', function () {
    $this->get('/admin/recipes')->assertRedirect(route('admin.login'));
});

test('admin can create a recipe with products', function () {
    $admin = Admin::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango']);
    $mint = Product::factory()->create(['name' => 'Mint']);

    Livewire::actingAs($admin, 'admin')
        ->test(RecipeForm::class)
        ->set('title', 'Fresh Mango Salad')
        ->set('slug', 'fresh-mango-salad')
        ->set('productIds', [$mango->id, $mint->id])
        ->call('save')
        ->assertRedirect(route('admin.recipes.index'));

    $recipe = Recipe::where('slug', 'fresh-mango-salad')->first();

    expect($recipe)->not->toBeNull();
    expect($recipe->products()->pluck('products.id')->all())->toEqualCanonicalizing([$mango->id, $mint->id]);
});

test('recipe slug is auto-generated from the title', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(RecipeForm::class)
        ->set('title', 'Fresh Mango Salad')
        ->set('productIds', [$product->id])
        ->call('save')
        ->assertRedirect(route('admin.recipes.index'));

    $this->assertDatabaseHas('recipes', ['title' => 'Fresh Mango Salad', 'slug' => 'fresh-mango-salad']);
});

test('selected product appears in the selected products panel immediately', function () {
    $admin = Admin::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango']);

    Livewire::actingAs($admin, 'admin')
        ->test(RecipeForm::class)
        ->set('productIds', [$mango->id])
        ->assertSet('productIds', [$mango->id])
        ->assertSee('Mango');
});

test('admin can create a recipe with description', function () {
    $admin = Admin::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango']);

    Livewire::actingAs($admin, 'admin')
        ->test(RecipeForm::class)
        ->set('title', 'Fresh Mango Salad')
        ->set('slug', 'fresh-mango-salad')
        ->set('description', 'A light and refreshing salad for warm days.')
        ->set('productIds', [$mango->id])
        ->call('save')
        ->assertRedirect(route('admin.recipes.index'));

    $this->assertDatabaseHas('recipes', ['slug' => 'fresh-mango-salad', 'description' => 'A light and refreshing salad for warm days.']);
});

test('recipe detail page shows the description', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango']);
    $recipe = Recipe::factory()->create(['title' => 'Mango Salad', 'description' => 'A light and refreshing salad for warm days.']);
    $recipe->products()->attach($mango);

    Livewire::actingAs($user)
        ->test(RecipeShow::class, ['recipe' => $recipe])
        ->assertOk()
        ->assertSee('Mango Salad')
        ->assertSee('A light and refreshing salad for warm days.');
});

test('recipe detail page hides an empty description', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create(['title' => 'Mango Salad', 'description' => null]);

    Livewire::actingAs($user)
        ->test(RecipeShow::class, ['recipe' => $recipe])
        ->assertOk()
        ->assertSee('Mango Salad');
});

test('admin can update a recipe with description', function () {
    $admin = Admin::factory()->create();
    $oldProduct = Product::factory()->create();
    $newProduct = Product::factory()->create();
    $recipe = Recipe::factory()->create(['title' => 'Old Title']);
    $recipe->products()->attach($oldProduct);

    Livewire::actingAs($admin, 'admin')
        ->test(RecipeForm::class, ['recipe' => $recipe])
        ->set('title', 'New Title')
        ->set('description', 'An updated description.')
        ->set('productIds', [$newProduct->id])
        ->call('save')
        ->assertRedirect(route('admin.recipes.index'));

    expect($recipe->fresh()->title)->toBe('New Title');
    expect($recipe->fresh()->description)->toBe('An updated description.');
    expect($recipe->fresh()->products()->pluck('products.id')->all())->toEqualCanonicalizing([$newProduct->id]);
});

test('admin can toggle recipe visibility', function () {
    $admin = Admin::factory()->create();
    $recipe = Recipe::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin, 'admin')
        ->test(Recipes::class)
        ->call('toggleActive', $recipe)
        ->assertOk();

    expect($recipe->fresh()->is_active)->toBeFalse();
});

test('admin can delete a recipe', function () {
    $admin = Admin::factory()->create();
    $recipe = Recipe::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Recipes::class)
        ->call('delete', $recipe)
        ->assertOk();

    $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
});

test('recipe form requires at least one product', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(RecipeForm::class)
        ->set('title', 'Mango Salad')
        ->set('productIds', [])
        ->call('save')
        ->assertHasErrors('productIds');

    $this->assertDatabaseCount('recipes', 0);
});

test('recipe form rejects non-existent product ids', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(RecipeForm::class)
        ->set('title', 'Mango Salad')
        ->set('productIds', [999999])
        ->call('save')
        ->assertHasErrors('productIds.*');

    $this->assertDatabaseCount('recipes', 0);
});

test('recipe form shows selected products and can remove them', function () {
    $admin = Admin::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango']);
    $mint = Product::factory()->create(['name' => 'Mint']);

    Livewire::actingAs($admin, 'admin')
        ->test(RecipeForm::class)
        ->set('productIds', [$mango->id, $mint->id])
        ->assertSee('Selected Products')
        ->assertSee('Mango')
        ->assertSee('Mint')
        ->assertSet('selectedProducts.0.id', $mango->id)
        ->call('removeProduct', $mango->id)
        ->assertSet('productIds', [$mint->id]);
});

test('home page shows active recipes below shop by category', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Mango']);
    $recipe = Recipe::factory()->create(['title' => 'Mango Salad']);
    $recipe->products()->attach($product);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Mango Salad');
});

test('home page hides inactive recipes', function () {
    $user = User::factory()->create();
    Recipe::factory()->create(['title' => 'Hidden Recipe', 'is_active' => false]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('Hidden Recipe');
});

test('recipes index page shows active recipes', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create(['title' => 'Mango Salad']);
    $hidden = Recipe::factory()->create(['title' => 'Hidden Recipe', 'is_active' => false]);

    $this->actingAs($user)
        ->get(route('recipes.index'))
        ->assertOk()
        ->assertSee('Mango Salad')
        ->assertDontSee('Hidden Recipe');
});

test('recipe detail page shows linked products', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango']);
    $recipe = Recipe::factory()->create(['title' => 'Mango Salad']);
    $recipe->products()->attach($mango);

    Livewire::actingAs($user)
        ->test(RecipeShow::class, ['recipe' => $recipe])
        ->assertOk()
        ->assertSee('Mango Salad')
        ->assertSee('Mango')
        ->assertSee('Products in this recipe');
});

test('add all to cart adds every in-stock product from the recipe', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango', 'in_stock' => true]);
    $mint = Product::factory()->create(['name' => 'Mint', 'in_stock' => true]);
    $recipe = Recipe::factory()->create(['title' => 'Mango Salad']);
    $recipe->products()->attach([$mango->id => ['product_unit_id' => $mango->defaultUnit()->id], $mint->id => ['product_unit_id' => $mint->defaultUnit()->id]]);

    Livewire::actingAs($user)
        ->test(RecipeShow::class, ['recipe' => $recipe])
        ->call('addAllToCart')
        ->assertOk()
        ->assertSet('cartMessage', 'Added 2 items from this recipe to your cart.');

    $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'product_id' => $mango->id, 'quantity' => 1, 'recipe_id' => $recipe->id]);
    $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'product_id' => $mint->id, 'quantity' => 1, 'recipe_id' => $recipe->id]);
});

test('add all to cart skips out-of-stock products and reports them', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango', 'in_stock' => true]);
    $mint = Product::factory()->create(['name' => 'Mint', 'in_stock' => false]);
    $recipe = Recipe::factory()->create(['title' => 'Mango Salad']);
    $recipe->products()->attach([$mango->id => ['product_unit_id' => $mango->defaultUnit()->id], $mint->id => ['product_unit_id' => $mint->defaultUnit()->id]]);

    Livewire::actingAs($user)
        ->test(RecipeShow::class, ['recipe' => $recipe])
        ->call('addAllToCart')
        ->assertOk()
        ->assertSet('cartMessage', 'Added 1 item from this recipe to your cart.')
        ->assertSet('cartError', 'Out of stock: Mint.');

    $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'product_id' => $mango->id, 'quantity' => 1]);
    $this->assertDatabaseMissing('cart_items', ['product_id' => $mint->id]);
});

test('guest is redirected to login when adding recipe to cart', function () {
    $mango = Product::factory()->create(['name' => 'Mango', 'in_stock' => true]);
    $recipe = Recipe::factory()->create(['title' => 'Mango Salad']);
    $recipe->products()->attach([$mango->id => ['product_unit_id' => $mango->defaultUnit()->id]]);

    Livewire::test(RecipeShow::class, ['recipe' => $recipe])
        ->call('addAllToCart')
        ->assertRedirect(route('login'));
});

test('inactive recipes cannot be viewed on the store', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create(['title' => 'Hidden Recipe', 'is_active' => false]);

    $this->actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertNotFound();
});
