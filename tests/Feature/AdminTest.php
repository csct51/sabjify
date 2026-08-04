<?php

use App\Livewire\Admin\Categories;
use App\Livewire\Admin\CategoryForm;
use App\Livewire\Admin\Customers;
use App\Livewire\Admin\OrderShow;
use App\Livewire\Admin\ProductForm;
use App\Livewire\Admin\Products;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('guest is redirected to login when accessing admin', function () {
    $this->get('/admin/dashboard')->assertRedirect(route('login'));
});

test('customer cannot access admin panel', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)->get('/admin/dashboard')->assertForbidden();
});

test('admin can access the dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('Total Revenue');
});

test('admin can create a category', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CategoryForm::class)
        ->set('name', 'Frozen Foods')
        ->set('slug', 'frozen-foods')
        ->call('save')
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', ['name' => 'Frozen Foods', 'slug' => 'frozen-foods']);
});

test('admin can create a product', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin)
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

test('admin can update product stock', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'stock' => 5]);

    Livewire::actingAs($admin)
        ->test(ProductForm::class, ['product' => $product])
        ->set('stock', 50)
        ->call('save')
        ->assertRedirect(route('admin.products.index'));

    expect($product->fresh()->stock)->toBe(50);
});

test('admin can update an order status', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($admin)
        ->test(OrderShow::class, ['order' => $order])
        ->set('status', 'out_for_delivery')
        ->call('updateStatus')
        ->assertOk();

    expect($order->fresh()->status)->toBe('out_for_delivery');
});

test('admin can toggle product visibility', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin)
        ->test(Products::class)
        ->call('toggleActive', $product)
        ->assertOk();

    expect($product->fresh()->is_active)->toBeFalse();
});

test('admin can block and unblock a customer', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create(['role' => 'customer']);

    Livewire::actingAs($admin)
        ->test(Customers::class)
        ->call('toggleActive', $customer)
        ->assertOk();

    expect($customer->fresh()->is_active)->toBeFalse();

    Livewire::actingAs($admin)
        ->test(Customers::class)
        ->call('toggleActive', $customer)
        ->assertOk();

    expect($customer->fresh()->is_active)->toBeTrue();
});

test('admin cannot delete a category that has products', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    Livewire::actingAs($admin)
        ->test(Categories::class)
        ->call('delete', $category)
        ->assertHasErrors('delete');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});
