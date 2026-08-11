<?php

use App\Models\Basket;
use App\Models\Product;
use Database\Seeders\BasketSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;

beforeEach(function () {
    $this->seed(CategorySeeder::class);
    $this->seed(ProductSeeder::class);
});

it('seeds the wellness baskets with their products', function () {
    $this->seed(BasketSeeder::class);

    $baskets = Basket::where('type', Basket::TYPE_WELLNESS)->get();

    expect($baskets)->toHaveCount(6)
        ->and($baskets->pluck('name'))
        ->toContain(
            'Heart Care Basket',
            'BP-Friendly Basket',
            'Diabetes-Friendly Basket',
            'Weight Management Basket',
            'Liver-Friendly Basket',
            'Family Wellness Basket'
        );

    $basket = Basket::where('slug', 'heart-care-basket')->first();

    expect($basket)->not->toBeNull()
        ->and($basket->products)->toHaveCount(8)
        ->and($basket->price)->toBeGreaterThan(0)
        ->and($basket->products->pluck('slug'))
        ->toContain('fresh-apple', 'spinach-palak', 'avocado');
});

it('is idempotent when run twice', function () {
    $this->seed(BasketSeeder::class);
    $this->seed(BasketSeeder::class);

    expect(Basket::count())->toBe(6);
});

it('links baskets only to products that exist', function () {
    $this->seed(BasketSeeder::class);

    Basket::with('products')->get()->each(function (Basket $basket) {
        $basket->products->each(fn (Product $product) => expect($product)->toBeInstanceOf(Product::class));
    });
});
