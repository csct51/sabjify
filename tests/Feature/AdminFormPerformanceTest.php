<?php

use App\Livewire\Admin\BasketForm;
use App\Livewire\Admin\RecipeForm;
use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('basket form renders with bounded unit-table queries even when multiple products are selected', function () {
    $admin = Admin::factory()->create();

    $productIds = [];

    foreach (range(1, 3) as $index) {
        $product = Product::factory()->create(['unit' => '1 kg', 'base_unit' => 'g', 'price' => 100, 'is_active' => true]);
        $product->units()->delete();
        ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '1 kg', 'price' => 100, 'sort_order' => 0]);
        ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '500 g', 'price' => 60, 'sort_order' => 1]);
        $productIds[] = $product->id;
    }

    $unitTableQueries = 0;
    DB::listen(function ($query) use (&$unitTableQueries) {
        if (stripos($query->sql, 'from "units"') !== false) {
            $unitTableQueries++;
        }
    });

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('productIds', $productIds);

    expect($unitTableQueries)->toBeLessThan(6);
});

test('basket form shows the correct unit context for selected products', function () {
    $admin = Admin::factory()->create();

    $kgProduct = Product::factory()->create(['unit' => '1 kg', 'base_unit' => 'g', 'price' => 100, 'is_active' => true]);
    $kgProduct->units()->delete();
    ProductUnit::factory()->create(['product_id' => $kgProduct->id, 'unit' => '1 kg', 'price' => 100, 'sort_order' => 0]);
    ProductUnit::factory()->create(['product_id' => $kgProduct->id, 'unit' => '500 g', 'price' => 60, 'sort_order' => 1]);

    $pcProduct = Product::factory()->create(['unit' => '1 pc', 'base_unit' => 'piece', 'price' => 50, 'is_active' => true]);
    $pcProduct->units()->delete();
    ProductUnit::factory()->create(['product_id' => $pcProduct->id, 'unit' => '1 pc', 'price' => 50, 'sort_order' => 0]);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('productIds', [$kgProduct->id, $pcProduct->id])
        ->assertSee('Custom qty (kg)')
        ->assertSee('e.g. 0.5')
        ->assertSee('Enter kg — e.g. 0.5 = 500 g.')
        ->assertSee('Custom qty (piece)')
        ->assertSee('e.g. 2')
        ->assertSee('Whole pieces only.');
});

test('basket form debounces name, price, custom fields, and defers description', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->assertSeeHtml('wire:model.live.debounce.300ms="name"')
        ->assertSeeHtml('wire:model.live.debounce.300ms="price"')
        ->assertSeeHtml('wire:model="description"');
});

test('recipe form debounces title and keys picker rows', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin, 'admin')
        ->test(RecipeForm::class)
        ->assertSeeHtml('wire:model.live.debounce.300ms="title"')
        ->assertSeeHtml('wire:key="recipe-product-'.$product->id.'"');
});
