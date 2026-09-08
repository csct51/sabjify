<?php

use App\Models\Product;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;

beforeEach(function () {
    $this->seed(CategorySeeder::class);
    $this->seed(ProductSeeder::class);
});

it('seeds the full real catalog with uniform placeholders', function () {
    expect(Product::count())->toBe(91);

    Product::all()->each(
        fn (Product $product) => expect($product->base_unit)->toBe('g')
            ->and((int) $product->price)->toBe(100)
            ->and((float) $product->current_stock)->toBe(0.0)
            ->and($product->units)->toHaveCount(1)
            ->and($product->units->first()->unit)->toBe('1 kg')
            ->and($product->is_active)->toBeTrue()
    );
});

it('seeds hindi and hinglish alternate names', function () {
    $tomato = Product::where('slug', 'tomato')->first();

    expect($tomato)->not->toBeNull()
        ->and($tomato->alternate_names)->toContain('tamatar', 'टमाटर');

    expect(Product::whereNull('alternate_names')->count())->toBe(0);
});

it('splits fruits and vegetables', function () {
    expect(Product::where('slug', 'mango')->first()->category->name)->toBe('Fruits')
        ->and(Product::where('slug', 'tomato')->first()->category->name)->toBe('Vegetables');
});

it('re-seed backfills empty alternate names without clobbering edits', function () {
    $tomato = Product::where('slug', 'tomato')->first();
    $tomato->update(['price' => 250, 'alternate_names' => null]);

    $this->seed(ProductSeeder::class);

    expect($tomato->fresh()->price)->toBe(250)
        ->and($tomato->fresh()->alternate_names)->toContain('tamatar');
});
