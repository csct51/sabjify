<?php

use App\Livewire\Admin\Categories as AdminCategories;
use App\Livewire\Admin\CategoryForm as AdminCategoryForm;
use App\Livewire\Admin\Customers as AdminCustomers;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Orders as AdminOrders;
use App\Livewire\Admin\OrderShow as AdminOrderShow;
use App\Livewire\Admin\ProductForm as AdminProductForm;
use App\Livewire\Admin\Products as AdminProducts;
use App\Livewire\Auth\PhoneLogin;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\Home;
use App\Livewire\Orders\Index as OrdersIndex;
use App\Livewire\Orders\Show as OrdersShow;
use App\Livewire\ProductDetail;
use App\Livewire\Profile;
use App\Livewire\Shop;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Home::class)->name('home');
Route::livewire('/shop', Shop::class)->name('shop');
Route::livewire('/product/{product:slug}', ProductDetail::class)->name('product.show');
Route::livewire('/login', PhoneLogin::class)->name('login')->middleware('guest');

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::livewire('/cart', Cart::class)->name('cart');
    Route::livewire('/checkout', Checkout::class)->name('checkout');
    Route::livewire('/profile', Profile::class)->name('profile');
    Route::livewire('/orders', OrdersIndex::class)->name('orders.index');
    Route::livewire('/orders/{order}', OrdersShow::class)->name('orders.show');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/dashboard', AdminDashboard::class)->name('dashboard');
    Route::livewire('/categories', AdminCategories::class)->name('categories.index');
    Route::livewire('/categories/create', AdminCategoryForm::class)->name('categories.create');
    Route::livewire('/categories/{category}/edit', AdminCategoryForm::class)->name('categories.edit');
    Route::livewire('/products', AdminProducts::class)->name('products.index');
    Route::livewire('/products/create', AdminProductForm::class)->name('products.create');
    Route::livewire('/products/{product}/edit', AdminProductForm::class)->name('products.edit');
    Route::livewire('/orders', AdminOrders::class)->name('orders.index');
    Route::livewire('/orders/{order}', AdminOrderShow::class)->name('orders.show');
    Route::livewire('/customers', AdminCustomers::class)->name('customers.index');
});
