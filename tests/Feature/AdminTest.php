<?php

use App\Livewire\Admin\Auth\AdminLogin;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\CategoryForm;
use App\Livewire\Admin\Customers;
use App\Livewire\Admin\CustomerShow;
use App\Livewire\Admin\NotificationBell;
use App\Livewire\Admin\OrderShow;
use App\Livewire\Admin\Prices;
use App\Livewire\Admin\ProductForm;
use App\Livewire\Admin\Products;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\User;
use Livewire\Livewire;

test('guest is redirected to admin login when accessing admin', function () {
    $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
});

test('customer cannot access admin panel', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->get('/admin/dashboard')->assertRedirect(route('admin.login'));
});

test('admin can access the dashboard', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->get('/admin/dashboard')->assertOk()->assertSee('Total Revenue');
});

test('admin login page renders', function () {
    $this->get('/admin/login')->assertOk()->assertSee('Admin Login');
});

test('admin can log in with valid credentials', function () {
    Admin::factory()->create(['username' => 'admin', 'password' => 'password']);

    Livewire::test(AdminLogin::class)
        ->set('username', 'admin')
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('admin.dashboard'));

    expect(auth('admin')->check())->toBeTrue();
});

test('admin login rejects invalid credentials', function () {
    Admin::factory()->create(['username' => 'admin', 'password' => 'password']);

    Livewire::test(AdminLogin::class)
        ->set('username', 'admin')
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('username');

    expect(auth('admin')->check())->toBeFalse();
});

test('admin login is rate limited after repeated failures', function () {
    Admin::factory()->create(['username' => 'ratelimit', 'password' => 'password']);

    $last = null;
    for ($i = 0; $i < 6; $i++) {
        $last = Livewire::test(AdminLogin::class)
            ->set('username', 'ratelimit')
            ->set('password', 'wrong')
            ->call('login');
    }

    $last->assertHasErrors('username');
});

test('admin can create a category', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(CategoryForm::class)
        ->set('name', 'Frozen Foods')
        ->set('slug', 'frozen-foods')
        ->call('save')
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', ['name' => 'Frozen Foods', 'slug' => 'frozen-foods']);
});

test('category slug is auto-generated from the name', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(CategoryForm::class)
        ->set('name', 'Frozen Foods')
        ->call('save')
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', ['name' => 'Frozen Foods', 'slug' => 'frozen-foods']);
});

test('category slug is auto-generated when editing the name', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

    Livewire::actingAs($admin, 'admin')
        ->test(CategoryForm::class, ['category' => $category])
        ->set('name', 'New Name')
        ->set('slug', '')
        ->call('save')
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'slug' => 'new-name']);
});

test('admin can create a product', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class)
        ->set('categoryId', $category->id)
        ->set('name', 'Fresh Mango')
        ->set('slug', 'fresh-mango')
        ->set('unitRows', [
            ['unit' => '1 kg', 'price' => '120', 'mrp' => '150'],
        ])
        ->call('save')
        ->assertRedirect(route('admin.products.index'));

    $this->assertDatabaseHas('products', [
        'name' => 'Fresh Mango',
        'price' => 120,
        'category_id' => $category->id,
    ]);

    $this->assertDatabaseHas('product_units', [
        'unit' => '1 kg',
        'price' => 120,
    ]);
});

test('product slug is auto-generated from the name', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class)
        ->set('categoryId', $category->id)
        ->set('name', 'Fresh Mango')
        ->set('unitRows', [
            ['unit' => '1 kg', 'price' => '120', 'mrp' => null],
        ])
        ->call('save')
        ->assertRedirect(route('admin.products.index'));

    $this->assertDatabaseHas('products', ['name' => 'Fresh Mango', 'slug' => 'fresh-mango']);
});

test('admin can update product unit stock', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class, ['product' => $product])
        ->set('unitRows', [
            ['unit' => '1 kg', 'price' => '50', 'mrp' => null, 'in_stock' => false],
        ])
        ->call('save')
        ->assertRedirect(route('admin.products.index'));

    expect($product->fresh()->units()->where('in_stock', false)->exists())->toBeTrue();
});

test('admin toggling one unit out of stock keeps product in stock if another unit is in stock', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create();
    $product->units()->delete();
    $product->units()->saveMany([
        ProductUnit::factory()->make(['unit' => '1 kg', 'in_stock' => true]),
        ProductUnit::factory()->make(['unit' => '500 g', 'in_stock' => true]),
    ]);

    $outUnit = $product->units()->where('unit', '500 g')->first();

    Livewire::actingAs($admin, 'admin')
        ->test(Prices::class)
        ->call('toggleUnitStock', $outUnit)
        ->assertDispatched('toast', function ($eventName, $params) use ($product, $outUnit) {
            $message = $params['message'] ?? '';

            return str_contains($message, $product->name)
                && str_contains($message, $outUnit->unit)
                && str_contains($message, 'out of stock');
        });

    expect($outUnit->fresh()->in_stock)->toBeFalse()
        ->and($product->fresh()->inStock())->toBeTrue();
});

test('admin can update an order status', function () {
    $admin = Admin::factory()->create();
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($admin, 'admin')
        ->test(OrderShow::class, ['order' => $order])
        ->set('status', 'confirmed')
        ->call('updateStatus')
        ->assertDispatched('toast', message: 'Order status updated.');

    expect($order->fresh()->status)->toBe('confirmed');
});

test('admin cannot skip order status steps', function () {
    $admin = Admin::factory()->create();
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($admin, 'admin')
        ->test(OrderShow::class, ['order' => $order])
        ->set('status', 'delivered')
        ->call('updateStatus')
        ->assertHasErrors('status');

    expect($order->fresh()->status)->toBe('pending');
});

test('admin cannot mark cash on delivery orders as refunded', function () {
    $admin = Admin::factory()->create();
    $order = Order::factory()->create(['status' => 'pending', 'payment_method' => 'cod', 'payment_status' => 'pending']);

    Livewire::actingAs($admin, 'admin')
        ->test(OrderShow::class, ['order' => $order])
        ->set('paymentStatus', 'refunded')
        ->call('updatePaymentStatus')
        ->assertHasErrors('paymentStatus');

    expect($order->fresh()->payment_status)->toBe('pending');
});

test('admin can toggle product visibility', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin, 'admin')
        ->test(Products::class)
        ->call('toggleActive', $product)
        ->assertDispatched('toast', message: "Product \"{$product->name}\" is now hidden.");

    expect($product->fresh()->is_active)->toBeFalse();
});

test('admin can block and unblock a customer', function () {
    $admin = Admin::factory()->create();
    $customer = User::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Customers::class)
        ->call('toggleActive', $customer)
        ->assertDispatched('toast', message: "Customer \"{$customer->name}\" is now blocked.");

    expect($customer->fresh()->is_active)->toBeFalse();

    Livewire::actingAs($admin, 'admin')
        ->test(Customers::class)
        ->call('toggleActive', $customer)
        ->assertDispatched('toast', message: "Customer \"{$customer->name}\" is now unblocked.");

    expect($customer->fresh()->is_active)->toBeTrue();
});

test('customers table shows order count and total order amount', function () {
    $admin = Admin::factory()->create();
    $customer = User::factory()->create();

    Order::factory()->create(['user_id' => $customer->id, 'total' => 240]);
    Order::factory()->create(['user_id' => $customer->id, 'total' => 360]);

    Livewire::actingAs($admin, 'admin')
        ->test(Customers::class)
        ->assertSee('Number of Orders')
        ->assertSee('Total Order Amount')
        ->assertSee('2')
        ->assertSee('600');
});

test('admin can view customer details', function () {
    $admin = Admin::factory()->create();
    $customer = User::factory()->create();
    Address::factory()->create([
        'user_id' => $customer->id,
        'label' => 'Home',
        'city' => 'Mumbai',
    ]);
    $order = Order::factory()->create(['user_id' => $customer->id, 'total' => 240]);

    Livewire::actingAs($admin, 'admin')
        ->test(CustomerShow::class, ['user' => $customer])
        ->assertOk()
        ->assertSee($customer->name)
        ->assertSee($customer->email)
        ->assertSee('+91 '.$customer->phone)
        ->assertSee('Mumbai')
        ->assertSee($order->order_number)
        ->assertSee('240');
});

test('customer details page filters orders by status', function () {
    $admin = Admin::factory()->create();
    $customer = User::factory()->create();
    $pending = Order::factory()->create(['user_id' => $customer->id, 'status' => 'pending']);
    $delivered = Order::factory()->create(['user_id' => $customer->id, 'status' => 'delivered']);

    Livewire::actingAs($admin, 'admin')
        ->test(CustomerShow::class, ['user' => $customer])
        ->assertSee($pending->order_number)
        ->assertSee($delivered->order_number)
        ->set('status', 'pending')
        ->assertSee($pending->order_number)
        ->assertDontSee($delivered->order_number)
        ->assertSet('counts', fn (array $counts) => $counts['all'] === 2 && $counts['pending'] === 1 && $counts['delivered'] === 1);
});

test('guest is redirected to admin login when accessing customer details', function () {
    $customer = User::factory()->create();

    $this->get(route('admin.customers.show', $customer))->assertRedirect(route('admin.login'));
});

test('admin cannot delete a category that has products', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    Livewire::actingAs($admin, 'admin')
        ->test(Categories::class)
        ->call('delete', $category)
        ->assertHasErrors('delete');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('notification bell shows pending order count', function () {
    $admin = Admin::factory()->create();
    Order::factory()->create(['status' => 'pending']);
    Order::factory()->create(['status' => 'pending']);
    Order::factory()->create(['status' => 'delivered']);

    Livewire::actingAs($admin, 'admin')
        ->test(NotificationBell::class)
        ->assertSet('pendingOrdersCount', 2);
});

test('notification bell lists pending orders in the modal', function () {
    $admin = Admin::factory()->create();
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($admin, 'admin')
        ->test(NotificationBell::class)
        ->call('toggle')
        ->assertSet('show', true)
        ->assertSee($order->order_number);
});

test('admin can update product unit prices', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg']);
    $unit = ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '500 g', 'price' => 100, 'mrp' => 120]);

    Livewire::actingAs($admin, 'admin')
        ->test(Prices::class)
        ->set('prices', [$unit->id => 130])
        ->set('mrps', [$unit->id => 150])
        ->call('save')
        ->assertDispatched('toast', message: 'Prices updated.');

    expect($unit->fresh())
        ->price->toBe(130)
        ->mrp->toBe(150);
});

test('admin can update price and clear mrp', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg']);
    $unit = ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '500 g', 'price' => 100, 'mrp' => 120]);

    Livewire::actingAs($admin, 'admin')
        ->test(Prices::class)
        ->set('prices', [$unit->id => 140])
        ->set('mrps', [$unit->id => ''])
        ->call('save')
        ->assertDispatched('toast', message: 'Prices updated.');

    expect($unit->fresh())
        ->price->toBe(140)
        ->mrp->toBeNull();
});

test('prices page rejects invalid price', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg']);
    $unit = ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '500 g', 'price' => 100]);

    Livewire::actingAs($admin, 'admin')
        ->test(Prices::class)
        ->set('prices', [$unit->id => 0])
        ->call('save')
        ->assertHasErrors('prices.'.$unit->id);

    expect($unit->fresh()->price)->toBe(100);
});

test('prices page filters units by search', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['name' => 'Fresh Mango', 'unit' => '1 kg']);
    $otherProduct = Product::factory()->create(['name' => 'Ripe Banana', 'unit' => '1 kg']);
    $unit = ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '500 g']);
    $otherUnit = ProductUnit::factory()->create(['product_id' => $otherProduct->id, 'unit' => '250 g']);

    Livewire::actingAs($admin, 'admin')
        ->test(Prices::class)
        ->set('search', 'Mango')
        ->assertSet('units', fn ($units) => $units->contains('id', $unit->id) && ! $units->contains('id', $otherUnit->id))
        ->assertDontSee($otherUnit->unit);
});

test('prices page toggles unit stock', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create();
    $unit = $product->units()->first();
    $unit->update(['in_stock' => true]);

    Livewire::actingAs($admin, 'admin')
        ->test(Prices::class)
        ->call('toggleUnitStock', $unit)
        ->assertDispatched('toast', function ($eventName, $params) use ($product, $unit) {
            $message = $params['message'] ?? '';

            return str_contains($message, $product->name)
                && str_contains($message, $unit->unit)
                && str_contains($message, 'out of stock');
        });

    expect($unit->fresh()->in_stock)->toBeFalse();
});

test('prices page filters units by category', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $otherCategory = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'unit' => '1 kg']);
    $otherProduct = Product::factory()->create(['category_id' => $otherCategory->id, 'unit' => '1 kg']);
    $unit = ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '500 g']);
    $otherUnit = ProductUnit::factory()->create(['product_id' => $otherProduct->id, 'unit' => '250 g']);

    Livewire::actingAs($admin, 'admin')
        ->test(Prices::class)
        ->set('category', $category->id)
        ->assertSet('units', fn ($units) => $units->count() === 2 && $units->contains('id', $unit->id))
        ->assertDontSee($otherUnit->unit);
});
