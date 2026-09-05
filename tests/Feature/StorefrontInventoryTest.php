<?php

use App\Livewire\Admin\Reports\Selling;
use App\Livewire\Cart;
use App\Livewire\ProductCard;
use App\Livewire\ProductDetail;
use App\Livewire\ProductUnitPicker;
use App\Models\Admin;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use App\Models\User;
use App\Services\OrderService;
use Livewire\Livewire;

beforeEach(function () {
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 1]);
});

test('picker blocks adding more packs than stock covers', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 500]);

    Livewire::actingAs($user)
        ->test(ProductUnitPicker::class)
        ->call('open', $product->id)
        ->call('addToCart')
        ->assertHasErrors('stock');

    expect($user->cartItems()->count())->toBe(0);
});

test('picker stepper caps at sellable packs', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 2500]);

    Livewire::actingAs($user)
        ->test(ProductUnitPicker::class)
        ->call('open', $product->id)
        ->call('incrementQuantity')
        ->assertSet('quantity', 2)
        ->call('incrementQuantity')
        ->assertSet('quantity', 2);
});

test('cart increment stops at sellable packs', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 1000]);
    $unit = $product->units()->where('unit', '1 kg')->first() ?? $product->defaultUnit();

    $item = CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_unit_id' => $unit?->id,
        'quantity' => 1,
    ]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->call('increment', $item->id);

    expect($item->fresh()->quantity)->toBe(1);
});

test('order service refuses short stock and decrements on placement', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 1000]);
    $unit = $product->units()->where('unit', '1 kg')->first() ?? $product->defaultUnit();

    CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_unit_id' => $unit?->id,
        'quantity' => 2,
    ]);

    $data = [
        'payment_method' => 'cod',
        'receiver_name' => 'Rahul Sharma',
        'receiver_phone' => '9876501234',
        'address_line' => '12 Main Street',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '123456',
    ];

    expect(fn () => app(OrderService::class)->createFromCart($user, $data))
        ->toThrow(RuntimeException::class, 'Not enough stock');

    expect((float) $product->fresh()->current_stock)->toBe(1000.0);
});

test('order placement decrements stock and cancel restores it', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 5000]);
    $unit = $product->units()->where('unit', '1 kg')->first() ?? $product->defaultUnit();

    CartItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_unit_id' => $unit?->id,
        'quantity' => 1,
    ]);

    $data = [
        'payment_method' => 'cod',
        'receiver_name' => 'Rahul Sharma',
        'receiver_phone' => '9876501234',
        'address_line' => '12 Main Street',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '123456',
    ];

    $order = app(OrderService::class)->createFromCart($user, $data);

    expect((float) $product->fresh()->current_stock)->toBe(4000.0)
        ->and((float) $order->items->first()->base_qty)->toBe(1000.0);

    app(OrderService::class)->cancel($order, 'test', 'customer');

    expect($order->fresh()->status)->toBe(Order::STATUS_CANCELLED)
        ->and((float) $product->fresh()->current_stock)->toBe(5000.0);
});

test('zero stock shows out of stock instead of only zero left', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 0]);

    Livewire::actingAs($user)
        ->test(ProductCard::class, ['product' => $product])
        ->assertSee('Out of Stock')
        ->assertDontSee('Only 0 left');

    Livewire::actingAs($user)
        ->test(ProductDetail::class, ['product' => $product])
        ->assertSee('Out of Stock')
        ->assertDontSee('Only 0 left');
});

test('picker opens on a sellable unit and marks dead options out of stock', function () {
    $user = User::factory()->create();
    Unit::create(['name' => '500 g', 'base_unit' => 'g', 'to_base_factor' => 500, 'sort_order' => 2]);
    $product = Product::factory()->create(['unit' => '1 kg', 'price' => 100]);
    $product->units()->delete();
    ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '1 kg', 'price' => 100, 'in_stock' => true, 'sort_order' => 0]);
    $sellable = ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '500 g', 'price' => 60, 'in_stock' => true, 'sort_order' => 1]);
    $product->update(['current_stock' => 500]);

    Livewire::actingAs($user)
        ->test(ProductUnitPicker::class)
        ->call('open', $product->id)
        ->assertSet('selectedUnitId', $sellable->id)
        ->assertSee('Out of stock');
});

test('toggle off still blocks adding to cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->outOfStock()->create(['unit' => '1 kg', 'price' => 100]);
    $product->update(['current_stock' => 5000]);

    Livewire::actingAs($user)
        ->test(ProductUnitPicker::class)
        ->call('open', $product->id)
        ->call('addToCart')
        ->assertHasErrors('stock');

    expect($user->cartItems()->count())->toBe(0);
});

test('selling report shows sold products and excludes cancelled orders', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Report Tomato', 'unit' => '1 kg', 'price' => 100]);

    $order = Order::factory()->create(['user_id' => User::factory()->create()->id, 'status' => Order::STATUS_CONFIRMED, 'total' => 200]);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'unit' => '1 kg',
        'price' => 100,
        'quantity' => 2,
        'total' => 200,
        'base_qty' => 2000,
    ]);

    $cancelled = Order::factory()->cancelled()->create(['total' => 100]);
    OrderItem::create([
        'order_id' => $cancelled->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'unit' => '1 kg',
        'price' => 100,
        'quantity' => 1,
        'total' => 100,
        'base_qty' => 1000,
    ]);

    $report = Livewire::actingAs($admin, 'admin')
        ->test(Selling::class)
        ->assertSee('Report Tomato')
        ->assertSee('₹200.00')
        ->assertSee('>kg<', false)
        ->assertDontSee('×')
        ->assertDontSee('Base Qty');

    expect($report->get('rows')[0]['qty'])->toBe(2.0)
        ->and($report->get('rows')[0]['unit'])->toBe('kg');
});

test('selling report date filter excludes old orders', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['name' => 'Old Mango', 'unit' => '1 kg', 'price' => 100]);

    $order = Order::factory()->delivered()->create(['total' => 100]);
    $order->created_at = now()->subDays(60);
    $order->save();
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'unit' => '1 kg',
        'price' => 100,
        'quantity' => 1,
        'total' => 100,
        'base_qty' => 1000,
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(Selling::class)
        ->assertDontSee('Old Mango');
});
