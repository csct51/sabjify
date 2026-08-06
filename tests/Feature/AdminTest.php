<?php

use App\Livewire\Admin\Auth\AdminLogin;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\CategoryForm;
use App\Livewire\Admin\Customers;
use App\Livewire\Admin\CustomerShow;
use App\Livewire\Admin\NotificationBell;
use App\Livewire\Admin\OrderShow;
use App\Livewire\Admin\ProductForm;
use App\Livewire\Admin\Products;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
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

test('admin can create a product', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class)
        ->set('categoryId', $category->id)
        ->set('name', 'Fresh Mango')
        ->set('slug', 'fresh-mango')
        ->set('price', 120)
        ->set('mrp', 150)
        ->set('unit', '1 kg')
        ->set('stock', 20)
        ->call('save')
        ->assertRedirect(route('admin.products.index'));

    $this->assertDatabaseHas('products', [
        'name' => 'Fresh Mango',
        'price' => 120,
        'category_id' => $category->id,
    ]);
});

test('product slug is auto-generated from the name', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class)
        ->set('categoryId', $category->id)
        ->set('name', 'Fresh Mango')
        ->set('price', 120)
        ->set('unit', '1 kg')
        ->set('stock', 20)
        ->call('save')
        ->assertRedirect(route('admin.products.index'));

    $this->assertDatabaseHas('products', ['name' => 'Fresh Mango', 'slug' => 'fresh-mango']);
});

test('admin can update product stock', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 5]);

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class, ['product' => $product])
        ->set('stock', 50)
        ->call('save')
        ->assertRedirect(route('admin.products.index'));

    expect($product->fresh()->stock)->toBe(50);
});

test('admin can update an order status', function () {
    $admin = Admin::factory()->create();
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($admin, 'admin')
        ->test(OrderShow::class, ['order' => $order])
        ->set('status', 'out_for_delivery')
        ->call('updateStatus')
        ->assertOk();

    expect($order->fresh()->status)->toBe('out_for_delivery');
});

test('admin can toggle product visibility', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin, 'admin')
        ->test(Products::class)
        ->call('toggleActive', $product)
        ->assertOk();

    expect($product->fresh()->is_active)->toBeFalse();
});

test('admin can block and unblock a customer', function () {
    $admin = Admin::factory()->create();
    $customer = User::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Customers::class)
        ->call('toggleActive', $customer)
        ->assertOk();

    expect($customer->fresh()->is_active)->toBeFalse();

    Livewire::actingAs($admin, 'admin')
        ->test(Customers::class)
        ->call('toggleActive', $customer)
        ->assertOk();

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
