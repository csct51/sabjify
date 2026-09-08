<?php

use App\Livewire\Admin\Purchases\Create as PurchasesCreate;
use App\Livewire\Admin\Purchases\Edit as PurchasesEdit;
use App\Livewire\Admin\Wastages\Create as WastagesCreate;
use App\Livewire\Admin\Wastages\Edit as WastagesEdit;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Livewire\Livewire;

beforeEach(function () {
    // Product display units only. Purchase units ('kg'/'piece') must come from
    // the consolidated seed_inventory_reference_units migration — do NOT
    // create them here, or the regression test below cannot catch a missing seed.
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 1]);
    Unit::create(['name' => '1 pc', 'base_unit' => 'piece', 'to_base_factor' => 1, 'sort_order' => 3]);
});

test('purchase units kg and piece are seeded with correct conversion', function () {
    $kg = Unit::where('name', 'kg')->first();
    $piece = Unit::where('name', 'piece')->first();

    expect($kg)->not->toBeNull()
        ->and((float) $kg->to_base_factor)->toBe(1000.0)
        ->and($kg->base_unit)->toBe('g')
        ->and($piece)->not->toBeNull()
        ->and((float) $piece->to_base_factor)->toBe(1.0)
        ->and($piece->base_unit)->toBeNull()
        ->and($piece->is_base)->toBeTrue();
});

test('purchase accepts decimal kg qty and increments base stock', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 0]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formRate', '100')
        ->set('formQty', '0.5')
        ->call('addProduct')
        ->assertHasNoErrors()
        ->call('save')
        ->assertRedirect(route('admin.purchases.index'));

    $item = $product->fresh();
    expect((float) $item->current_stock)->toBeGreaterThan(499.999)->and((float) $item->current_stock)->toBeLessThan(500.001);

    $this->assertDatabaseHas('purchase_items', [
        'product_id' => $product->id,
        'unit' => 'kg',
    ]);
});

test('purchase rejects fractional piece qty', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 pc', 'price' => 50]);
    $product->update(['current_stock' => 0]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formRate', '50')
        ->set('formQty', '0.5')
        ->call('addProduct')
        ->assertHasErrors('formQty');

    expect((float) $product->fresh()->current_stock)->toBe(0.0);
});

test('wastage accepts decimal kg qty and decrements base stock', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 1000]);

    Livewire::actingAs($admin, 'admin')
        ->test(WastagesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formQty', '0.25')
        ->call('addProduct')
        ->assertHasNoErrors()
        ->call('save')
        ->assertRedirect(route('admin.wastages.index'));

    expect((float) $product->fresh()->current_stock)->toBeGreaterThan(749.999)->and((float) $product->fresh()->current_stock)->toBeLessThan(750.001);
});

test('purchase and wastage forms render qty hints', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->assertSee('Qty (kg)')
        ->assertSee('Enter kg')
        ->assertSee('placeholder="e.g. 0.5"', false);

    Livewire::actingAs($admin, 'admin')
        ->test(WastagesCreate::class)
        ->assertSee('Qty (kg)')
        ->assertSee('Enter kg');

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesEdit::class)
        ->assertSee('Enter kg');

    Livewire::actingAs($admin, 'admin')
        ->test(WastagesEdit::class)
        ->assertSee('Enter kg');
});

test('qty placeholder follows the selected products base unit', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $piece = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 pc', 'base_unit' => 'piece']);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->set('formProductId', $piece->id)
        ->assertSee('Qty (piece)')
        ->assertSee('placeholder="e.g. 2"', false);
});

test('purchase and wastage forms show available qty for selected product', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 2500]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->set('formProductId', $product->id)
        ->assertSee('Available: 2.5 kg (2500 g)');

    Livewire::actingAs($admin, 'admin')
        ->test(WastagesCreate::class)
        ->set('formProductId', $product->id)
        ->assertSee('Available: 2.5 kg (2500 g)');

    $piece = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 pc', 'price' => 50]);
    $piece->update(['current_stock' => 7]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->set('formProductId', $piece->id)
        ->assertSee('Available: 7 piece');
});

test('wastage rejects qty above current stock', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 100]);

    Livewire::actingAs($admin, 'admin')
        ->test(WastagesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formQty', '5')
        ->call('addProduct')
        ->assertHasErrors('formQty');
});
