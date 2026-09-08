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

it('seeds the wellness baskets with dummy products for admin to refine', function () {
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
        ->and($basket->products)->not->toBeEmpty()
        ->and($basket->products->pluck('slug'))
        ->toContain('apple', 'carrot');

    $baskets->each(
        fn (Basket $basket) => expect($basket->products)->not->toBeEmpty()
            ->and($basket->is_active)->toBeTrue()
    );
});

it('seeds the sabjify baskets with fixed prices', function () {
    $this->seed(BasketSeeder::class);

    $baskets = Basket::where('type', Basket::TYPE_SABJIFY)->get();

    expect($baskets)->toHaveCount(4)
        ->and($baskets->pluck('name'))->toContain(
            'Essential Basket',
            'Smart Basket',
            'Premium Basket',
            'Complete Basket'
        );

    $prices = $baskets->pluck('price', 'name');

    expect($prices['Essential Basket'])->toBe(299)
        ->and($prices['Smart Basket'])->toBe(499)
        ->and($prices['Premium Basket'])->toBe(799)
        ->and($prices['Complete Basket'])->toBe(999);

    $baskets->each(
        fn (Basket $basket) => expect($basket->products)->not->toBeEmpty()
            ->and($basket->is_active)->toBeTrue()
    );
});

it('is idempotent when run twice', function () {
    $this->seed(BasketSeeder::class);
    $this->seed(BasketSeeder::class);

    expect(Basket::count())->toBe(10)
        ->and(Basket::where('slug', 'essential-basket')->first()->products)->toHaveCount(4);
});

it('re-seed preserves admin-added links', function () {
    $this->seed(BasketSeeder::class);

    $basket = Basket::where('slug', 'essential-basket')->first();
    $lemon = Product::where('slug', 'lemon')->first();
    $basket->products()->syncWithoutDetaching([$lemon->id]);

    $this->seed(BasketSeeder::class);

    expect($basket->fresh()->products->pluck('slug'))
        ->toContain('tomato', 'onion', 'carrot', 'cucumber', 'lemon');
});
