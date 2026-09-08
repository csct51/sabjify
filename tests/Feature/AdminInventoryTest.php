<?php

use App\Livewire\Admin\LowStockBell;
use App\Livewire\Admin\ProductForm;
use App\Livewire\Admin\Purchases\Create as PurchasesCreate;
use App\Livewire\Admin\Purchases\Edit as PurchasesEdit;
use App\Livewire\Admin\Purchases\Index as PurchasesIndex;
use App\Livewire\Admin\Reports\Purchases as PurchasesReport;
use App\Livewire\Admin\Reports\Stock;
use App\Livewire\Admin\Reports\Wastage as WastageReport;
use App\Livewire\Admin\Units\Create as UnitsCreate;
use App\Livewire\Admin\Units\Edit as UnitsEdit;
use App\Livewire\Admin\Units\Index as UnitsIndex;
use App\Livewire\Admin\Wastages\Create as WastagesCreate;
use App\Livewire\Admin\Wastages\Edit as WastagesEdit;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Unit;
use App\Models\Wastage;
use App\Models\WastageItem;
use Livewire\Livewire;

test('product form persists base unit', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    Unit::create(['name' => '1 pc', 'base_unit' => 'piece', 'to_base_factor' => 1, 'sort_order' => 0]);

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class)
        ->set('categoryId', $category->id)
        ->set('name', 'Coconut')
        ->set('slug', 'coconut')
        ->set('baseUnit', 'piece')
        ->set('unitRows', [
            ['unit' => '1 pc', 'price' => '40', 'mrp' => null, 'in_stock' => true],
        ])
        ->call('save')
        ->assertRedirect(route('admin.products.index'));

    expect(Product::where('slug', 'coconut')->first()?->base_unit)->toBe('piece');
});

test('product form rejects rows mixing base units', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 0]);
    Unit::create(['name' => '1 pc', 'base_unit' => 'piece', 'to_base_factor' => 1, 'sort_order' => 1]);

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class)
        ->set('categoryId', $category->id)
        ->set('name', 'Mixed')
        ->set('slug', 'mixed')
        ->set('baseUnit', 'g')
        ->set('unitRows', [
            ['unit' => '1 kg', 'price' => '100', 'mrp' => null, 'in_stock' => true],
            ['unit' => '1 pc', 'price' => '40', 'mrp' => null, 'in_stock' => true],
        ])
        ->call('save')
        ->assertHasErrors('unitRows.1.unit');

    expect(Product::where('slug', 'mixed')->exists())->toBeFalse();
});

test('base unit falls back to derivation when column is null', function () {
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 0]);
    $product = Product::factory()->create(['unit' => '1 kg', 'base_unit' => null]);

    expect($product->baseUnit())->toBe('g')
        ->and($product->purchaseUnit())->toBe('kg');
});

test('stock in unit converts base stock per selling unit', function () {
    Unit::create(['name' => '500 g', 'base_unit' => 'g', 'to_base_factor' => 500, 'sort_order' => 0]);
    $product = Product::factory()->create(['base_unit' => 'g']);
    $product->update(['current_stock' => 2500]);

    expect($product->fresh()->stockInUnit('500 g'))->toBe(5.0);
});

test('unit conversion falls back when unit row is missing', function () {
    Unit::whereIn('name', ['kg', 'piece'])->delete();

    expect(Unit::where('name', 'kg')->exists())->toBeFalse();

    expect(Unit::toBaseQty('kg', 0.5))->toBe(500.0)
        ->and(Unit::toBaseQty('piece', 3))->toBe(3.0);
});

test('purchase show page renders items', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'base_unit' => 'g']);
    $purchase = Purchase::create([
        'purchase_number' => 'PUR-901',
        'supplier_name' => 'Cash',
        'purchase_date' => now()->format('Y-m-d'),
        'total_amount' => 50,
    ]);
    PurchaseItem::create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'unit' => 'kg',
        'rate' => 100,
        'qty' => 0.5,
        'line_total' => 50,
        'base_qty' => 500,
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.purchases.show', $purchase))
        ->assertOk()
        ->assertSee('PUR-901')
        ->assertSee($product->name);
});

test('wastage show page renders items', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'base_unit' => 'g']);
    $wastage = Wastage::create([
        'wastage_number' => 'WST-901',
        'wastage_date' => now()->format('Y-m-d'),
        'reason' => 'Expired',
        'total_qty' => 0.25,
    ]);
    WastageItem::create([
        'wastage_id' => $wastage->id,
        'product_id' => $product->id,
        'unit' => 'kg',
        'qty' => 0.25,
        'base_qty' => 250,
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.wastages.show', $wastage))
        ->assertOk()
        ->assertSee('WST-901')
        ->assertSee($product->name);
});

test('deleting a purchase from the list floors stock at zero', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 0]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formRate', '100')
        ->set('formQty', '1')
        ->call('addProduct')
        ->call('save')
        ->assertRedirect(route('admin.purchases.index'));

    Livewire::actingAs($admin, 'admin')
        ->test(WastagesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formQty', '0.9')
        ->call('addProduct')
        ->call('save');

    expect((float) $product->fresh()->current_stock)->toBe(100.0);

    $purchase = Purchase::latest()->first();

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesIndex::class)
        ->call('delete', $purchase);

    expect((float) $product->fresh()->current_stock)->toBe(0.0)
        ->and(Purchase::count())->toBe(0);
});

test('deleting a purchase from its edit page floors stock at zero', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 0]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formRate', '100')
        ->set('formQty', '1')
        ->call('addProduct')
        ->call('save');

    Livewire::actingAs($admin, 'admin')
        ->test(WastagesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formQty', '0.9')
        ->call('addProduct')
        ->call('save');

    $purchase = Purchase::latest()->first();

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesEdit::class, ['purchase' => $purchase->id])
        ->call('delete')
        ->assertRedirect(route('admin.purchases.index'));

    expect((float) $product->fresh()->current_stock)->toBe(0.0)
        ->and(Purchase::count())->toBe(0);
});

test('deleting a fully stocked purchase reverts exactly', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 0]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formRate', '100')
        ->set('formQty', '2')
        ->call('addProduct')
        ->call('save');

    expect((float) $product->fresh()->current_stock)->toBe(2000.0);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesIndex::class)
        ->call('delete', Purchase::latest()->first());

    expect((float) $product->fresh()->current_stock)->toBe(0.0);
});

test('product form sets opening stock on create', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 0]);

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class)
        ->set('categoryId', $category->id)
        ->set('name', 'Stocked Mango')
        ->set('slug', 'stocked-mango')
        ->set('baseUnit', 'g')
        ->set('currentStock', '2500.5')
        ->set('unitRows', [
            ['unit' => '1 kg', 'price' => '120', 'mrp' => null, 'in_stock' => true],
        ])
        ->call('save')
        ->assertRedirect(route('admin.products.index'));

    expect((float) Product::where('slug', 'stocked-mango')->first()?->current_stock)->toBe(2500.5);
});

test('product form never touches stock on edit', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 0]);
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'base_unit' => 'g']);
    $product->update(['current_stock' => 1000]);

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class, ['product' => $product])
        ->set('unitRows', [
            ['unit' => '1 kg', 'price' => '120', 'mrp' => null, 'in_stock' => true],
        ])
        ->call('save')
        ->assertRedirect(route('admin.products.index'));

    expect((float) $product->fresh()->current_stock)->toBe(1000.0);
});

test('product form low stock is entered in purchase units', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 0]);
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'base_unit' => 'g']);
    $product->update(['current_stock' => 5000, 'low_stock' => 1000]);

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class, ['product' => $product])
        ->assertSet('lowStock', '1')
        ->set('lowStock', '2')
        ->set('unitRows', [
            ['unit' => '1 kg', 'price' => '120', 'mrp' => null, 'in_stock' => true],
        ])
        ->call('save')
        ->assertRedirect(route('admin.products.index'));

    expect((float) $product->fresh()->current_stock)->toBe(5000.0)
        ->and((float) $product->fresh()->low_stock)->toBe(2000.0);
});

test('product form rejects fractional low stock for piece products', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    Unit::create(['name' => '1 pc', 'base_unit' => 'piece', 'to_base_factor' => 1, 'sort_order' => 0]);
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 pc', 'base_unit' => 'piece']);

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class, ['product' => $product])
        ->set('baseUnit', 'piece')
        ->set('lowStock', '1.5')
        ->set('unitRows', [
            ['unit' => '1 pc', 'price' => '40', 'mrp' => null, 'in_stock' => true],
        ])
        ->call('save')
        ->assertHasErrors('lowStock');
});

test('base unit rows are seeded with purchase config', function () {
    $g = Unit::where('name', 'g')->first();
    $ml = Unit::where('name', 'ml')->first();
    $piece = Unit::where('name', 'piece')->first();

    expect($g)->not->toBeNull()
        ->and($g->is_base)->toBeTrue()
        ->and($g->base_unit)->toBeNull()
        ->and($g->purchase_unit)->toBe('kg')
        ->and($g->integer_only)->toBeFalse()
        ->and($ml)->not->toBeNull()
        ->and($ml->is_base)->toBeTrue()
        ->and($ml->purchase_unit)->toBe('litre')
        ->and($piece->integer_only)->toBeTrue();
});

test('product form rejects unknown base unit', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class)
        ->set('categoryId', $category->id)
        ->set('name', 'Weird')
        ->set('slug', 'weird')
        ->set('baseUnit', 'ounce')
        ->set('unitRows', [
            ['unit' => '1 kg', 'price' => '100', 'mrp' => null, 'in_stock' => true],
        ])
        ->call('save')
        ->assertHasErrors('baseUnit');

    expect(Product::where('slug', 'weird')->exists())->toBeFalse();
});

test('new base unit works end to end without deploys', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(UnitsCreate::class)
        ->set('name', 'meter')
        ->set('is_base', true)
        ->set('purchase_unit', '')
        ->set('to_base_factor', '1')
        ->call('save')
        ->assertRedirect(route('admin.units.index'));

    $meter = Unit::where('name', 'meter')->first();

    expect($meter)->not->toBeNull()
        ->and($meter->is_base)->toBeTrue()
        ->and($meter->purchase_unit)->toBe('meter');

    Livewire::actingAs($admin, 'admin')
        ->test(UnitsCreate::class)
        ->set('name', '5 meter')
        ->set('base_unit', 'meter')
        ->set('to_base_factor', '5')
        ->call('save')
        ->assertRedirect(route('admin.units.index'));

    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '5 meter', 'base_unit' => 'meter']);
    $product->update(['current_stock' => 0]);

    expect($product->fresh()->purchaseUnit())->toBe('meter');

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesCreate::class)
        ->set('formProductId', $product->id)
        ->set('formRate', '200')
        ->set('formQty', '2')
        ->call('addProduct')
        ->assertHasNoErrors()
        ->call('save')
        ->assertRedirect(route('admin.purchases.index'));

    expect((float) $product->fresh()->current_stock)->toBe(2.0)
        ->and($product->fresh()->displayStock())->toBe('2 meter');
});

test('base unit cannot be unflagged while in use', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id, 'base_unit' => 'g']);
    $g = Unit::where('name', 'g')->firstOrFail();

    Livewire::actingAs($admin, 'admin')
        ->test(UnitsEdit::class, ['unit' => $g])
        ->set('is_base', false)
        ->call('save')
        ->assertHasErrors('is_base');

    expect($g->fresh()->is_base)->toBeTrue();
});

test('base unit row cannot be deleted while in use', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id, 'base_unit' => 'g']);
    $g = Unit::where('name', 'g')->firstOrFail();

    Livewire::actingAs($admin, 'admin')
        ->test(UnitsIndex::class)
        ->call('delete', $g->id)
        ->assertHasErrors('remove');

    expect(Unit::where('name', 'g')->exists())->toBeTrue();
});

test('base unit enum is fully removed', function () {
    expect(class_exists('App\Enums\BaseUnit'))->toBeFalse()
        ->and(file_exists(app_path('Enums/BaseUnit.php')))->toBeFalse();
});

test('product form persists low stock threshold', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 0]);

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class)
        ->set('categoryId', $category->id)
        ->set('name', 'Alert Mango')
        ->set('slug', 'alert-mango')
        ->set('baseUnit', 'g')
        ->set('currentStock', '500')
        ->set('lowStock', '0.8')
        ->set('unitRows', [
            ['unit' => '1 kg', 'price' => '120', 'mrp' => null, 'in_stock' => true],
        ])
        ->call('save')
        ->assertRedirect(route('admin.products.index'));

    $saved = Product::where('slug', 'alert-mango')->first();

    expect((float) $saved?->low_stock)->toBe(800.0)
        ->and($saved?->isLowStock())->toBeTrue();
});

test('null low stock means never low, boundary counts as low', function () {
    $plain = Product::factory()->create(['base_unit' => 'g', 'low_stock' => null]);
    $plain->update(['current_stock' => 5]);

    $exact = Product::factory()->create(['base_unit' => 'piece', 'low_stock' => 10]);
    $exact->update(['current_stock' => 10]);

    $over = Product::factory()->create(['base_unit' => 'piece', 'low_stock' => 10]);
    $over->update(['current_stock' => 11]);

    expect($plain->fresh()->isLowStock())->toBeFalse()
        ->and($exact->fresh()->isLowStock())->toBeTrue()
        ->and($over->fresh()->isLowStock())->toBeFalse();
});

test('factory assigns per-base low stock defaults', function () {
    $gram = Product::factory()->create(['unit' => '1 kg']);
    $piece = Product::factory()->create(['unit' => '1 pc']);

    expect($gram->base_unit)->toBe('g')
        ->and((float) $gram->low_stock)->toBe(1000.0)
        ->and($piece->base_unit)->toBe('piece')
        ->and((float) $piece->low_stock)->toBe(10.0);
});

test('stock report low filter uses per-product thresholds', function () {
    $admin = Admin::factory()->create();
    $low = Product::factory()->create(['name' => 'Low Salt', 'base_unit' => 'g', 'low_stock' => 1000]);
    $low->update(['current_stock' => 500]);
    $fine = Product::factory()->create(['name' => 'Fine Sugar', 'base_unit' => 'g', 'low_stock' => 1000]);
    $fine->update(['current_stock' => 5000]);

    Livewire::actingAs($admin, 'admin')
        ->test(Stock::class)
        ->set('stockFilter', 'low')
        ->assertSee('Low Salt')
        ->assertDontSee('Fine Sugar');
});

test('low stock bell lists low stock products separately from orders', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['name' => 'Bell Pepper', 'base_unit' => 'piece', 'low_stock' => 10]);
    $product->update(['current_stock' => 3]);

    Livewire::actingAs($admin, 'admin')
        ->test(LowStockBell::class)
        ->call('toggle')
        ->assertSee('Low Stock')
        ->assertSee('Bell Pepper');
});

test('low stock bell shows empty state when stocked up', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['base_unit' => 'piece', 'low_stock' => 10]);
    $product->update(['current_stock' => 50]);

    Livewire::actingAs($admin, 'admin')
        ->test(LowStockBell::class)
        ->call('toggle')
        ->assertSee('No low-stock products');
});

test('purchase report aggregates spend and filters by supplier', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Report Potato', 'unit' => '1 kg', 'price' => 100]);

    $purchase = Purchase::create([
        'purchase_number' => 'PUR-910',
        'supplier_name' => 'Cash',
        'purchase_date' => now()->format('Y-m-d'),
        'total_amount' => 200,
    ]);
    PurchaseItem::create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'unit' => 'kg',
        'rate' => 100,
        'qty' => 2,
        'line_total' => 200,
        'base_qty' => 2000,
    ]);

    $report = Livewire::actingAs($admin, 'admin')
        ->test(PurchasesReport::class)
        ->assertSee('Report Potato')
        ->assertSee('₹200.00')
        ->assertSee('>kg<', false)
        ->assertDontSee('×')
        ->assertDontSee('Base Qty');

    expect($report->get('rows')[0]['qty'])->toBe(2.0)
        ->and($report->get('rows')[0]['unit'])->toBe('kg');

    $report->set('supplier', 'No Such Supplier')
        ->assertDontSee('Report Potato');
});

test('purchase report date filter excludes old entries', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['name' => 'Old Onion', 'unit' => '1 kg', 'price' => 50]);

    $purchase = Purchase::create([
        'purchase_number' => 'PUR-911',
        'supplier_name' => 'Cash',
        'purchase_date' => now()->subDays(60)->format('Y-m-d'),
        'total_amount' => 50,
    ]);
    PurchaseItem::create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'unit' => 'kg',
        'rate' => 50,
        'qty' => 1,
        'line_total' => 50,
        'base_qty' => 1000,
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesReport::class)
        ->assertDontSee('Old Onion');
});

test('wastage report aggregates loss and filters by reason', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Report Spinach', 'unit' => '1 pc', 'price' => 20]);

    $wastage = Wastage::create([
        'wastage_number' => 'WST-910',
        'wastage_date' => now()->format('Y-m-d'),
        'reason' => 'Expired',
        'total_qty' => 3,
    ]);
    WastageItem::create([
        'wastage_id' => $wastage->id,
        'product_id' => $product->id,
        'unit' => 'piece',
        'qty' => 3,
        'base_qty' => 3,
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(WastageReport::class)
        ->assertSee('Report Spinach')
        ->assertSee('× piece')
        ->assertDontSee('Base Qty')
        ->set('reason', 'Damaged')
        ->assertDontSee('Report Spinach');
});

test('purchase header-only edit skips stock movement entirely', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'base_unit' => 'g']);
    $product->update(['current_stock' => 100]);

    $purchase = Purchase::create([
        'purchase_number' => 'PUR-920',
        'supplier_name' => 'Cash',
        'purchase_date' => now()->format('Y-m-d'),
        'total_amount' => 5000,
    ]);
    PurchaseItem::create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'unit' => 'kg',
        'rate' => 100,
        'qty' => 10,
        'line_total' => 1000,
        'base_qty' => 10000,
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesEdit::class, ['purchase' => $purchase->id])
        ->set('remark', 'Typo fix only')
        ->call('save')
        ->assertRedirect(route('admin.purchases.index'));

    expect((float) $product->fresh()->current_stock)->toBe(100.0)
        ->and($purchase->fresh()->remark)->toBe('Typo fix only');
});

test('purchase partial edit moves only the difference', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'base_unit' => 'g']);
    $product->update(['current_stock' => 1000]);

    $purchase = Purchase::create([
        'purchase_number' => 'PUR-921',
        'supplier_name' => 'Cash',
        'purchase_date' => now()->format('Y-m-d'),
        'total_amount' => 50,
    ]);
    PurchaseItem::create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'unit' => 'kg',
        'rate' => 100,
        'qty' => 0.5,
        'line_total' => 50,
        'base_qty' => 500,
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesEdit::class, ['purchase' => $purchase->id])
        ->set('rows.0.qty', '0.3')
        ->call('save')
        ->assertRedirect(route('admin.purchases.index'));

    expect((float) $product->fresh()->current_stock)->toBe(800.0);
});

test('purchase edit with corrupt stored base is blocked', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'base_unit' => 'g']);
    $product->update(['current_stock' => 6000]);

    $purchase = Purchase::create([
        'purchase_number' => 'PUR-922',
        'supplier_name' => 'Cash',
        'purchase_date' => now()->format('Y-m-d'),
        'total_amount' => 550,
    ]);
    PurchaseItem::create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'unit' => 'kg',
        'rate' => 100,
        'qty' => 5.5,
        'line_total' => 550,
        'base_qty' => 5.5,
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesEdit::class, ['purchase' => $purchase->id])
        ->set('remark', 'Any change')
        ->call('save')
        ->assertHasErrors('rows');

    expect((float) $product->fresh()->current_stock)->toBe(6000.0);
});

test('purchase delete of corrupt rows heals instead of under-reverting', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'base_unit' => 'g']);
    $product->update(['current_stock' => 6000]);

    $purchase = Purchase::create([
        'purchase_number' => 'PUR-923',
        'supplier_name' => 'Cash',
        'purchase_date' => now()->format('Y-m-d'),
        'total_amount' => 550,
    ]);
    PurchaseItem::create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'unit' => 'kg',
        'rate' => 100,
        'qty' => 5.5,
        'line_total' => 550,
        'base_qty' => 5.5,
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(PurchasesIndex::class)
        ->call('delete', $purchase);

    expect(Purchase::find($purchase->id))->toBeNull()
        ->and((float) $product->fresh()->current_stock)->toBe(500.0);
});

test('wastage header-only edit skips stock movement entirely', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'base_unit' => 'g']);
    $product->update(['current_stock' => 100]);

    $wastage = Wastage::create([
        'wastage_number' => 'WST-920',
        'wastage_date' => now()->format('Y-m-d'),
        'reason' => 'Expired',
        'total_qty' => 10,
    ]);
    WastageItem::create([
        'wastage_id' => $wastage->id,
        'product_id' => $product->id,
        'unit' => 'kg',
        'qty' => 10,
        'base_qty' => 10000,
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(WastagesEdit::class, ['wastage' => $wastage->id])
        ->set('remark', 'Typo fix only')
        ->call('save')
        ->assertRedirect(route('admin.wastages.index'));

    expect((float) $product->fresh()->current_stock)->toBe(100.0)
        ->and($wastage->fresh()->remark)->toBe('Typo fix only');
});

test('wastage edit with corrupt stored base is blocked', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg', 'base_unit' => 'g']);
    $product->update(['current_stock' => 1000]);

    $wastage = Wastage::create([
        'wastage_number' => 'WST-921',
        'wastage_date' => now()->format('Y-m-d'),
        'reason' => 'Expired',
        'total_qty' => 2,
    ]);
    WastageItem::create([
        'wastage_id' => $wastage->id,
        'product_id' => $product->id,
        'unit' => 'kg',
        'qty' => 2,
        'base_qty' => 2,
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(WastagesEdit::class, ['wastage' => $wastage->id])
        ->set('remark', 'Any change')
        ->call('save')
        ->assertHasErrors('rows');

    expect((float) $product->fresh()->current_stock)->toBe(1000.0);
});

test('consolidated unit seed covers purchase and base rows', function () {
    $kg = Unit::where('name', 'kg')->first();
    $piece = Unit::where('name', 'piece')->first();
    $g = Unit::where('name', 'g')->first();
    $ml = Unit::where('name', 'ml')->first();

    expect($kg->base_unit)->toBe('g')
        ->and((float) $kg->to_base_factor)->toBe(1000.0)
        ->and($piece->is_base)->toBeTrue()
        ->and($piece->integer_only)->toBeTrue()
        ->and($g->is_base)->toBeTrue()
        ->and($g->purchase_unit)->toBe('kg')
        ->and($ml->is_base)->toBeTrue()
        ->and($ml->purchase_unit)->toBe('litre');
});
