<?php

use App\Models\Product;
use App\Models\Recipe;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RecipeSeeder;
use Illuminate\Database\Eloquent\Collection;

beforeEach(function () {
    $this->seed(CategorySeeder::class);
    $this->seed(ProductSeeder::class);
});

it('seeds recipes with their products', function () {
    $this->seed(RecipeSeeder::class);

    expect(Recipe::count())->toBeGreaterThan(0);

    $recipe = Recipe::where('slug', 'green-detox-smoothie')->first();

    expect($recipe)->not->toBeNull()
        ->and($recipe->description)->not->toBeNull()->not->toBe('')
        ->and($recipe->products)->toHaveCount(5)
        ->and($recipe->products->pluck('slug'))
        ->toContain('fresh-apple', 'spinach-palak', 'mint-leaves');
});

it('is idempotent when run twice', function () {
    $this->seed(RecipeSeeder::class);
    $this->seed(RecipeSeeder::class);

    expect(Recipe::count())->toBe(4);
});

it('links recipes only to products that exist', function () {
    $this->seed(RecipeSeeder::class);

    Recipe::with('products')->get()->each(function (Recipe $recipe) {
        expect($recipe->products)->toBeInstanceOf(Collection::class);
        $recipe->products->each(fn (Product $product) => expect($product)->toBeInstanceOf(Product::class));
    });
});
