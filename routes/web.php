<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Livewire\Admin\Auth\AdminLogin;
use App\Livewire\Admin\BasketForm as AdminBasketForm;
use App\Livewire\Admin\Baskets as AdminBaskets;
use App\Livewire\Admin\Categories as AdminCategories;
use App\Livewire\Admin\CategoryForm as AdminCategoryForm;
use App\Livewire\Admin\Customers as AdminCustomers;
use App\Livewire\Admin\CustomerShow as AdminCustomerShow;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\DeliveryLocationForm as AdminDeliveryLocationForm;
use App\Livewire\Admin\DeliveryLocations as AdminDeliveryLocations;
use App\Livewire\Admin\InfoCards\Edit as AdminInfoCardsEdit;
use App\Livewire\Admin\InfoCards\Index as AdminInfoCardsIndex;
use App\Livewire\Admin\Orders as AdminOrders;
use App\Livewire\Admin\OrderShow as AdminOrderShow;
use App\Livewire\Admin\Password as AdminPassword;
use App\Livewire\Admin\Prices as AdminPrices;
use App\Livewire\Admin\ProductForm as AdminProductForm;
use App\Livewire\Admin\Products as AdminProducts;
use App\Livewire\Admin\Purchases\Create as AdminPurchasesCreate;
use App\Livewire\Admin\Purchases\Edit as AdminPurchasesEdit;
use App\Livewire\Admin\Purchases\Index as AdminPurchasesIndex;
use App\Livewire\Admin\Purchases\Show as AdminPurchasesShow;
use App\Livewire\Admin\RecipeForm as AdminRecipeForm;
use App\Livewire\Admin\Recipes as AdminRecipes;
use App\Livewire\Admin\Reports\Purchases as AdminReportsPurchases;
use App\Livewire\Admin\Reports\Selling as AdminReportsSelling;
use App\Livewire\Admin\Reports\Stock as AdminReportsStock;
use App\Livewire\Admin\Reports\Wastage as AdminReportsWastage;
use App\Livewire\Admin\Settings as AdminSettings;
use App\Livewire\Admin\Suppliers\Create as AdminSuppliersCreate;
use App\Livewire\Admin\Suppliers\Edit as AdminSuppliersEdit;
use App\Livewire\Admin\Suppliers\Index as AdminSuppliersIndex;
use App\Livewire\Admin\Suppliers\Show as AdminSuppliersShow;
use App\Livewire\Admin\Units\Create as AdminUnitsCreate;
use App\Livewire\Admin\Units\Edit as AdminUnitsEdit;
use App\Livewire\Admin\Units\Index as AdminUnitsIndex;
use App\Livewire\Admin\Units\Show as AdminUnitsShow;
use App\Livewire\Admin\Wastages\Create as AdminWastagesCreate;
use App\Livewire\Admin\Wastages\Edit as AdminWastagesEdit;
use App\Livewire\Admin\Wastages\Index as AdminWastagesIndex;
use App\Livewire\Admin\Wastages\Show as AdminWastagesShow;
use App\Livewire\Auth\PhoneLogin;
use App\Livewire\Baskets\Index as BasketsIndex;
use App\Livewire\BasketShow;
use App\Livewire\Cart;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Checkout;
use App\Livewire\Home;
use App\Livewire\Orders\Index as OrdersIndex;
use App\Livewire\Orders\Show as OrdersShow;
use App\Livewire\ProductDetail;
use App\Livewire\Profile;
use App\Livewire\Profile\Account as ProfileAccount;
use App\Livewire\Profile\Addresses as ProfileAddresses;
use App\Livewire\Recipes\Index as RecipesIndex;
use App\Livewire\RecipeShow;
use App\Livewire\Search;
use App\Livewire\Shop;
use Illuminate\Support\Facades\Route;

// ---- STOREFRONT: public (no login needed) ----
Route::livewire('/login', PhoneLogin::class)->name('login')->middleware('guest');
Route::livewire('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::livewire('/terms-conditions', 'terms-conditions')->name('terms-conditions');

Route::redirect('/admin', '/admin/login')->name('admin.index');

// ---- ADMIN: entry (login / logout) ----
Route::livewire('/admin/login', AdminLogin::class)->name('admin.login')->middleware('guest:admin');

Route::post('/admin/logout', function () {
    auth('admin')->logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('admin.login');
})->name('admin.logout')->middleware('auth:admin');

Route::livewire('/', Home::class)->name('home');
Route::livewire('/shop', Shop::class)->name('shop');
Route::livewire('/search', Search::class)->name('search');
Route::livewire('/categories', CategoriesIndex::class)->name('categories.index');
Route::livewire('/product/{product:slug}', ProductDetail::class)->name('product.show');
Route::livewire('/recipes', RecipesIndex::class)->name('recipes.index');
Route::livewire('/recipes/{recipe:slug}', RecipeShow::class)->name('recipes.show');
Route::livewire('/baskets', BasketsIndex::class)->name('baskets.index');
Route::livewire('/baskets/{basket:slug}', BasketShow::class)->name('baskets.show');

Route::middleware(['auth', 'user.active'])->group(function () {
    // STOREFRONT: customer account (cart, checkout, profile, orders).
    Route::livewire('/cart', Cart::class)->name('cart');
    Route::livewire('/checkout', Checkout::class)->name('checkout');
    Route::post('/checkout/payment/verify', [PaymentController::class, 'verifyCheckout'])->name('checkout.payment.verify');
    Route::livewire('/profile', Profile::class)->name('profile');
    Route::livewire('/profile/account', ProfileAccount::class)->name('profile.account');
    Route::livewire('/profile/addresses', ProfileAddresses::class)->name('profile.addresses');
    Route::livewire('/orders', OrdersIndex::class)->name('orders.index');
    Route::livewire('/orders/{order}', OrdersShow::class)->name('orders.show');
    Route::post('/orders/{order}/payment/verify', [PaymentController::class, 'verify'])->name('orders.payment.verify');
    Route::get('/orders/{order}/invoice', [InvoiceController::class, 'download'])->name('orders.invoice');
});

Route::middleware(['auth:admin', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/dashboard', AdminDashboard::class)->name('dashboard');

    // ADMIN: catalog (categories, products, recipes, baskets).
    Route::livewire('/categories', AdminCategories::class)->name('categories.index');
    Route::livewire('/categories/create', AdminCategoryForm::class)->name('categories.create');
    Route::livewire('/categories/{category}/edit', AdminCategoryForm::class)->name('categories.edit');
    Route::livewire('/products', AdminProducts::class)->name('products.index');
    Route::livewire('/products/create', AdminProductForm::class)->name('products.create');
    Route::livewire('/products/{product}/edit', AdminProductForm::class)->name('products.edit');
    Route::livewire('/recipes', AdminRecipes::class)->name('recipes.index');
    Route::livewire('/recipes/create', AdminRecipeForm::class)->name('recipes.create');
    Route::livewire('/recipes/{recipe}/edit', AdminRecipeForm::class)->name('recipes.edit');
    Route::livewire('/baskets', AdminBaskets::class)->name('baskets.index');
    Route::livewire('/baskets/create', AdminBasketForm::class)->name('baskets.create');
    Route::livewire('/baskets/{basket}/edit', AdminBasketForm::class)->name('baskets.edit');

    // ADMIN: orders.
    Route::livewire('/orders', AdminOrders::class)->name('orders.index');
    Route::livewire('/orders/{order}', AdminOrderShow::class)->name('orders.show');
    Route::get('/orders/{order}/invoice', [InvoiceController::class, 'download'])->name('orders.invoice');

    // ADMIN: customers.
    Route::livewire('/customers', AdminCustomers::class)->name('customers.index');
    Route::livewire('/customers/{user}', AdminCustomerShow::class)->name('customers.show');

    // ADMIN: catalog — units.
    Route::livewire('/units', AdminUnitsIndex::class)->name('units.index');
    Route::livewire('/units/create', AdminUnitsCreate::class)->name('units.create');
    Route::livewire('/units/{unit}/edit', AdminUnitsEdit::class)->name('units.edit');
    Route::livewire('/units/{unit}', AdminUnitsShow::class)->name('units.show');

    // ADMIN: operations — prices.
    Route::livewire('/prices', AdminPrices::class)->name('prices');

    // ADMIN: inventory — purchases.
    Route::livewire('/purchases', AdminPurchasesIndex::class)->name('purchases.index');
    Route::livewire('/purchases/create', AdminPurchasesCreate::class)->name('purchases.create');
    Route::livewire('/purchases/{purchase}/edit', AdminPurchasesEdit::class)->name('purchases.edit');
    Route::livewire('/purchases/{purchase}', AdminPurchasesShow::class)->name('purchases.show');

    // ADMIN: inventory — wastage.
    Route::livewire('/wastages', AdminWastagesIndex::class)->name('wastages.index');
    Route::livewire('/wastages/create', AdminWastagesCreate::class)->name('wastages.create');
    Route::livewire('/wastages/{wastage}/edit', AdminWastagesEdit::class)->name('wastages.edit');
    Route::livewire('/wastages/{wastage}', AdminWastagesShow::class)->name('wastages.show');

    // ADMIN: reports.
    Route::livewire('/reports/stock', AdminReportsStock::class)->name('reports.stock');
    Route::livewire('/reports/selling', AdminReportsSelling::class)->name('reports.selling');
    Route::livewire('/reports/purchases', AdminReportsPurchases::class)->name('reports.purchases');
    Route::livewire('/reports/wastage', AdminReportsWastage::class)->name('reports.wastage');

    // ADMIN: inventory — suppliers.
    Route::livewire('/suppliers', AdminSuppliersIndex::class)->name('suppliers.index');
    Route::livewire('/suppliers/create', AdminSuppliersCreate::class)->name('suppliers.create');
    Route::livewire('/suppliers/{supplier}/edit', AdminSuppliersEdit::class)->name('suppliers.edit');
    Route::livewire('/suppliers/{supplier}', AdminSuppliersShow::class)->name('suppliers.show');

    // ADMIN: operations — delivery locations.
    Route::livewire('/delivery-locations', AdminDeliveryLocations::class)->name('delivery-locations.index');
    Route::livewire('/delivery-locations/create', AdminDeliveryLocationForm::class)->name('delivery-locations.create');
    Route::livewire('/delivery-locations/{deliveryLocation}/edit', AdminDeliveryLocationForm::class)->name('delivery-locations.edit');

    // ADMIN: system (password, settings, info cards).
    Route::livewire('/password', AdminPassword::class)->name('password');
    Route::livewire('/settings', AdminSettings::class)->name('settings');
    Route::livewire('/info-cards', AdminInfoCardsIndex::class)->name('info-cards.index');
    Route::livewire('/info-cards/{position}/edit', AdminInfoCardsEdit::class)->name('info-cards.edit');
});
