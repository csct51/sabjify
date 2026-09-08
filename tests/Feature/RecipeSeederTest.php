<?php

use App\Models\Product;
use App\Models\Recipe;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RecipeSeeder;

beforeEach(function () {
    $this->seed(CategorySeeder::class);
    $this->seed(ProductSeeder::class);
});

it('seeds recipes with dummy ingredients for admin to refine', function () {
    $this->seed(RecipeSeeder::class);

    expect(Recipe::count())->toBe(4);

    $recipe = Recipe::where('slug', 'green-detox-smoothie')->first();

    expect($recipe)->not->toBeNull()
        ->and($recipe->description)->not->toBeNull()->not->toBe('')
        ->and($recipe->products)->toHaveCount(4)
        ->and($recipe->products->pluck('slug'))
        ->toContain('apple', 'banana', 'ginger', 'lemon')
        ->and($recipe->is_active)->toBeTrue();
});

it('is idempotent when run twice', function () {
    $this->seed(RecipeSeeder::class);
    $this->seed(RecipeSeeder::class);

    expect(Recipe::count())->toBe(4);
});

it('re-seed preserves admin-added links', function () {
    $this->seed(RecipeSeeder::class);

    $recipe = Recipe::where('slug', 'fruit-energy-bowl')->first();
    $mango = Product::where('slug', 'mango')->first();
    $recipe->products()->detach($mango->id);
    $cabbage = Product::where('slug', 'cabbage')->first();
    $recipe->products()->attach($cabbage->id);

    $this->seed(RecipeSeeder::class);

    expect($recipe->fresh()->products->pluck('slug'))
        ->toContain('cabbage')
        ->not->toContain('mango');
});
