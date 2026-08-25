<?php

use App\Livewire\ProductUnitPicker;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('picker preselects the first in-stock unit', function () {
    $product = Product::factory()->available()->create();
    $product->units()->delete();

    $outOfStock = $product->units()->create([
        'unit' => '500 g',
        'price' => 40,
        'in_stock' => false,
        'sort_order' => 1,
    ]);
    $inStock = $product->units()->create([
        'unit' => '1 kg',
        'price' => 80,
        'in_stock' => true,
        'sort_order' => 2,
    ]);

    Livewire::test(ProductUnitPicker::class)
        ->call('open', $product->id)
        ->assertSet('selectedUnitId', $inStock->id)
        ->assertNotSet('selectedUnitId', $outOfStock->id)
        ->assertSet('quantity', 1);
});

test('picker disables add to cart until a unit is chosen', function () {
    $product = Product::factory()->create();
    $product->units()->delete();

    Livewire::test(ProductUnitPicker::class)
        ->call('open', $product->id)
        ->assertSet('selectedUnitId', null);
});
