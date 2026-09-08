<?php

use App\Livewire\Admin\Purchases\Create as PurchasesCreate;
use App\Livewire\Admin\Wastages\Create as WastagesCreate;
use App\Models\Admin;
use App\Models\Product;
use Livewire\Livewire;

test('selecting a product autofills the rate from its default unit price', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg', 'price' => 140]);
    $product->update(['current_stock' => 5000]);
    $expected = (string) $product->defaultUnit()->price;

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->set('formProductId', $product->id)
        ->assertSet('formRate', $expected);
});

test('a manually changed rate survives adding the product', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg', 'price' => 140]);
    $product->update(['current_stock' => 5000]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formRate', '120')
        ->set('formQty', '2')
        ->call('addProduct')
        ->assertHasNoErrors()
        ->assertSet('rows.0.rate', '120');
});

test('purchase add dispatches product-added so the picker clears', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 5000]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formRate', '100')
        ->set('formQty', '1')
        ->call('addProduct')
        ->assertDispatched('product-added')
        ->assertSet('formProductId', null);
});

test('wastage add dispatches product-added so the picker clears', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 5000]);

    Livewire::actingAs($admin, 'admin')
        ->test(WastagesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formQty', '1')
        ->call('addProduct')
        ->assertDispatched('product-added')
        ->assertSet('formProductId', null);
});
