<?php

use App\Livewire\Admin\BasketForm;
use App\Livewire\Admin\Baskets;
use App\Livewire\BasketCard;
use App\Livewire\BasketShow;
use App\Livewire\Cart;
use App\Models\Admin;
use App\Models\Basket;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\User;
use App\Services\OrderService;
use Livewire\Livewire;

test('guest is redirected to admin login when accessing baskets', function () {
    $this->get('/admin/baskets')->assertRedirect(route('admin.login'));
});

test('admin can create a wellness basket with products', function () {
    $admin = Admin::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango', 'price' => 120]);
    $mint = Product::factory()->create(['name' => 'Mint', 'price' => 40]);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Wellness Boost Basket')
        ->set('slug', 'wellness-boost-basket')
        ->set('type', Basket::TYPE_WELLNESS)
        ->set('price', 499)
        ->set('productIds', [$mango->id, $mint->id])
        ->call('save')
        ->assertRedirect(route('admin.baskets.index'));

    $basket = Basket::where('slug', 'wellness-boost-basket')->first();

    expect($basket)->not->toBeNull();
    expect($basket->type)->toBe(Basket::TYPE_WELLNESS);
    expect($basket->price)->toBe(499);
    expect($basket->products()->pluck('products.id')->all())->toEqualCanonicalizing([$mango->id, $mint->id]);
});

test('admin can pick a specific product unit for each basket product', function () {
    $admin = Admin::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango']);
    $bigUnit = $mango->units()->create(['unit' => '1 kg', 'price' => 200, 'sort_order' => 2]);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Unit Picked Basket')
        ->set('type', Basket::TYPE_WELLNESS)
        ->set('price', 499)
        ->set('productIds', [$mango->id])
        ->set('productUnitIds', [$mango->id => $bigUnit->id])
        ->call('save')
        ->assertRedirect(route('admin.baskets.index'));

    $basket = Basket::where('slug', 'unit-picked-basket')->first();

    expect($basket->products()->find($mango->id)->pivot->product_unit_id)->toBe($bigUnit->id);
});

test('admin can create a sabjify basket', function () {
    $admin = Admin::factory()->create();
    $tomato = Product::factory()->create(['name' => 'Tomato']);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Daily Sabjify Basket')
        ->set('type', Basket::TYPE_SABJIFY)
        ->set('price', 299)
        ->set('productIds', [$tomato->id])
        ->call('save')
        ->assertRedirect(route('admin.baskets.index'));

    $this->assertDatabaseHas('baskets', ['slug' => 'daily-sabjify-basket', 'type' => Basket::TYPE_SABJIFY]);
});

test('basket price auto-calculates from selected products and their units', function () {
    $admin = Admin::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango', 'unit' => '1 kg', 'price' => 150]);
    $mango->units()->create(['unit' => '2 kg', 'price' => 280, 'sort_order' => 1]);
    $mint = Product::factory()->create(['name' => 'Mint', 'price' => 40]);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Calculated Basket')
        ->set('type', Basket::TYPE_WELLNESS)
        ->set('productIds', [$mango->id, $mint->id])
        ->assertSet('price', 190)
        ->assertSet('priceManuallyEdited', false);

    $bigUnit = $mango->units()->where('unit', '2 kg')->first();

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Calculated Basket')
        ->set('type', Basket::TYPE_WELLNESS)
        ->set('productIds', [$mango->id, $mint->id])
        ->set('productUnitIds', [$mango->id => $bigUnit->id])
        ->assertSet('price', 320);
});

test('admin can manually override the calculated basket price', function () {
    $admin = Admin::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango', 'price' => 150]);
    $mint = Product::factory()->create(['name' => 'Mint', 'price' => 40]);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Manual Price Basket')
        ->set('type', Basket::TYPE_WELLNESS)
        ->set('price', 199)
        ->set('productIds', [$mango->id, $mint->id])
        ->assertSet('price', 199)
        ->assertSet('priceManuallyEdited', true);
});

test('admin can revert to the calculated basket price', function () {
    $admin = Admin::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango', 'price' => 150]);
    $mint = Product::factory()->create(['name' => 'Mint', 'price' => 40]);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Reverted Basket')
        ->set('type', Basket::TYPE_WELLNESS)
        ->set('price', 199)
        ->set('productIds', [$mango->id, $mint->id])
        ->call('applyCalculatedPrice')
        ->assertSet('price', 190)
        ->assertSet('priceManuallyEdited', false);
});

test('admin can update a basket', function () {
    $admin = Admin::factory()->create();
    $oldProduct = Product::factory()->create();
    $newProduct = Product::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Old Basket']);
    $basket->products()->attach($oldProduct);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class, ['basket' => $basket])
        ->set('name', 'New Basket')
        ->set('price', 599)
        ->set('productIds', [$newProduct->id])
        ->call('save')
        ->assertRedirect(route('admin.baskets.index'));

    expect($basket->fresh()->name)->toBe('New Basket');
    expect($basket->fresh()->price)->toBe(599);
    expect($basket->fresh()->products()->pluck('products.id')->all())->toEqualCanonicalizing([$newProduct->id]);
});

test('admin can toggle basket visibility', function () {
    $admin = Admin::factory()->create();
    $basket = Basket::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin, 'admin')
        ->test(Baskets::class)
        ->call('toggleActive', $basket)
        ->assertOk();

    expect($basket->fresh()->is_active)->toBeFalse();
});

test('admin can delete a basket', function () {
    $admin = Admin::factory()->create();
    $basket = Basket::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Baskets::class)
        ->call('delete', $basket)
        ->assertOk();

    $this->assertDatabaseMissing('baskets', ['id' => $basket->id]);
});

test('admin cannot delete a basket that has been ordered', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Ordered Basket']);

    CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 1]);

    app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Test User',
        'receiver_phone' => '9876543210',
        'address_line' => 'Test Address',
        'city' => 'Mumbai',
        'state' => 'MH',
        'pincode' => '400001',
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(Baskets::class)
        ->call('delete', $basket)
        ->assertOk();

    $this->assertDatabaseHas('baskets', ['id' => $basket->id]);
});

test('admin cannot delete a basket that is in a cart', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Cart Basket']);

    CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 1]);

    Livewire::actingAs($admin, 'admin')
        ->test(Baskets::class)
        ->call('delete', $basket)
        ->assertOk();

    $this->assertDatabaseHas('baskets', ['id' => $basket->id]);
});

test('basket form requires a price', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Free Basket')
        ->set('price', 0)
        ->set('productIds', [$product->id])
        ->call('save')
        ->assertHasErrors('price');

    $this->assertDatabaseCount('baskets', 0);
});

test('basket form requires at least one product', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Empty Basket')
        ->set('price', 299)
        ->set('productIds', [])
        ->call('save')
        ->assertHasErrors('productIds');

    $this->assertDatabaseCount('baskets', 0);
});

test('basket form rejects a non-existent type', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Bad Basket')
        ->set('type', 'mystery')
        ->set('price', 299)
        ->set('productIds', [$product->id])
        ->call('save')
        ->assertHasErrors('type');

    $this->assertDatabaseCount('baskets', 0);
});

test('home page shows active baskets', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'type' => Basket::TYPE_WELLNESS]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Wellness Baskets')
        ->assertSee('Wellness Boost')
        ->assertSeeHtml('overflow-x-auto py-2 snap-x snap-mandatory no-scrollbar');
});

test('home page basket card dispatches the hover preview with product names and units', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Spinach', 'unit' => 'bunch']);
    $unit = ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '500 g', 'price' => 4000]);
    $basket = Basket::factory()->create(['name' => 'Green Bundle']);
    $basket->products()->attach($product, ['product_unit_id' => $unit->id]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSeeHtml('basket-preview:open')
        ->assertSee('Green Bundle')
        ->assertSee('Spinach')
        ->assertSee('500 g');
});

test('home page renders the basket preview tooltip component', function () {
    $user = User::factory()->create();
    Basket::factory()->create(['name' => 'Wellness Boost', 'type' => Basket::TYPE_WELLNESS]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSeeHtml('basket-preview:open.window')
        ->assertSeeHtml('role="tooltip"')
        ->assertSeeHtml('View basket');
});

test('home page shows wellness and sabjify baskets under separate headings', function () {
    $user = User::factory()->create();
    Basket::factory()->create(['name' => 'Wellness Boost', 'type' => Basket::TYPE_WELLNESS]);
    Basket::factory()->create(['name' => 'Daily Sabjify', 'type' => Basket::TYPE_SABJIFY]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Wellness Baskets')
        ->assertSee('Wellness Boost')
        ->assertSee('Sabjify Baskets')
        ->assertSee('Daily Sabjify');
});

test('home page hides inactive baskets', function () {
    $user = User::factory()->create();
    Basket::factory()->create(['name' => 'Hidden Basket', 'is_active' => false]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('Hidden Basket');
});

test('baskets index page shows active baskets', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost']);
    $hidden = Basket::factory()->create(['name' => 'Hidden Basket', 'is_active' => false]);

    $this->actingAs($user)
        ->get(route('baskets.index'))
        ->assertOk()
        ->assertSee('Wellness Boost')
        ->assertDontSee('Hidden Basket');
});

test('baskets index shows wellness and sabjify baskets in separate sections', function () {
    $user = User::factory()->create();
    Basket::factory()->create(['name' => 'Wellness Boost', 'type' => Basket::TYPE_WELLNESS]);
    Basket::factory()->create(['name' => 'Daily Sabjify', 'type' => Basket::TYPE_SABJIFY]);

    $this->actingAs($user)
        ->get(route('baskets.index'))
        ->assertOk()
        ->assertSee('Wellness Baskets')
        ->assertSee('Wellness Boost')
        ->assertSee('Sabjify Baskets')
        ->assertSee('Daily Sabjify');
});

test('order detail page shows basket contents', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->available()->create(['name' => 'Mango', 'unit' => '1 pc', 'price' => 120]);
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);
    $basket->products()->attach($mango);

    CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 1]);

    $order = app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Test User',
        'receiver_phone' => '9876543210',
        'address_line' => 'Test Address',
        'city' => 'Mumbai',
        'state' => 'MH',
        'pincode' => '400001',
    ]);

    $this->actingAs($user)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Mango')
        ->assertSee('1 pc')
        ->assertSee('₹120');
});

test('admin order page shows basket contents', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();
    $mango = Product::factory()->available()->create(['name' => 'Mango', 'unit' => '1 pc', 'price' => 120]);
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);
    $basket->products()->attach($mango);

    CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 1]);

    $order = app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Test User',
        'receiver_phone' => '9876543210',
        'address_line' => 'Test Address',
        'city' => 'Mumbai',
        'state' => 'MH',
        'pincode' => '400001',
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('Mango')
        ->assertSee('1 pc')
        ->assertSee('₹120');
});

test('basket detail page shows name, price and inside products', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango', 'unit' => '1 pc', 'price' => 120]);
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);
    $basket->products()->attach($mango);

    Livewire::actingAs($user)
        ->test(BasketShow::class, ['basket' => $basket])
        ->assertOk()
        ->assertSee('Wellness Boost')
        ->assertSee('₹499')
        ->assertSee("What's inside", false)
        ->assertSee('Mango')
        ->assertSee('1 pc')
        ->assertSee('₹120');
});

test('basket detail shows the selected unit and its price', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango', 'unit' => '1 kg', 'price' => 150]);
    $bigUnit = $mango->units()->create(['unit' => '2 kg', 'price' => 280, 'sort_order' => 2]);
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);
    $basket->products()->attach($mango, ['product_unit_id' => $bigUnit->id]);

    Livewire::actingAs($user)
        ->test(BasketShow::class, ['basket' => $basket])
        ->assertOk()
        ->assertSee('Mango')
        ->assertSee('2 kg')
        ->assertSee('₹280');
});

test('basket detail default unit resolves to the first product unit when base columns differ', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango', 'unit' => '1 kg', 'price' => 150]);
    $mango->units()->create(['unit' => '500 g', 'price' => 90, 'sort_order' => 0]);
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);
    $basket->products()->attach($mango);

    Livewire::actingAs($user)
        ->test(BasketShow::class, ['basket' => $basket])
        ->assertOk()
        ->assertSee('Mango')
        ->assertSee('500 g')
        ->assertSee('₹90');
});

test('basket detail falls back to base columns when a product has no units', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango', 'unit' => '1 kg', 'price' => 150]);
    $mango->units()->delete();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);
    $basket->products()->attach($mango);

    Livewire::actingAs($user)
        ->test(BasketShow::class, ['basket' => $basket])
        ->assertOk()
        ->assertSee('Mango')
        ->assertSee('1 kg')
        ->assertSee('₹150');
});

test('inactive baskets cannot be viewed on the store', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['is_active' => false]);

    $this->actingAs($user)
        ->get(route('baskets.show', $basket))
        ->assertNotFound();
});

test('guest is redirected to login when adding a basket to cart', function () {
    $basket = Basket::factory()->create();

    Livewire::test(BasketShow::class, ['basket' => $basket])
        ->call('addToCart')
        ->assertRedirect(route('login'));
});

test('authenticated user can add a basket as a single cart line item', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);

    Livewire::actingAs($user)
        ->test(BasketShow::class, ['basket' => $basket])
        ->call('addToCart')
        ->assertOk()
        ->assertSet('inCart', true)
        ->assertSet('quantity', 1);

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'basket_id' => $basket->id,
        'product_id' => null,
        'quantity' => 1,
    ]);
});

test('adding the same basket twice increments its quantity', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost']);

    $item = CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(BasketShow::class, ['basket' => $basket])
        ->call('addToCart')
        ->assertOk();

    expect($item->fresh()->quantity)->toBe(2);
    $this->assertDatabaseCount('cart_items', 1);
});

test('basket detail increments the cart quantity from its counter', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost']);

    $item = CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(BasketShow::class, ['basket' => $basket])
        ->assertSet('inCart', true)
        ->assertSet('quantity', 2)
        ->call('increment')
        ->assertSet('quantity', 3)
        ->assertDispatched('cart-updated');

    expect($item->fresh()->quantity)->toBe(3);
});

test('basket detail decrements the cart quantity from its counter', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost']);

    $item = CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 3]);

    Livewire::actingAs($user)
        ->test(BasketShow::class, ['basket' => $basket])
        ->call('decrement')
        ->assertSet('quantity', 2)
        ->assertDispatched('cart-updated');

    expect($item->fresh()->quantity)->toBe(2);
});

test('basket detail counter removes the basket when quantity reaches zero', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost']);

    $item = CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(BasketShow::class, ['basket' => $basket])
        ->call('decrement')
        ->assertSet('inCart', false)
        ->assertSet('quantity', 1)
        ->assertDispatched('cart-updated');

    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});

test('cart page shows basket line items with correct subtotal', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 2]);
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->assertOk()
        ->assertSee('Wellness Boost')
        ->assertSet('subtotal', 1098);
});

test('cart groups baskets separately from products', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost']);
    $product = Product::factory()->available()->create(['name' => 'Apple']);

    CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 1]);
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->assertOk()
        ->assertSet('cartGroups', function (array $groups) use ($basket) {
            expect($groups)->toHaveCount(2);

            $basketGroup = collect($groups)->first(fn (array $group) => $group['basket'] !== null);

            expect($basketGroup['basket']->id)->toBe($basket->id);
            expect($basketGroup['items'])->toHaveCount(1);

            return true;
        });
});

test('cart increments a basket quantity without a stock cap', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create();
    $item = CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->call('increment', $item)
        ->assertOk();

    expect($item->fresh()->quantity)->toBe(3);
});

test('placing an order snapshots the basket as an order item without touching product stock', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->available()->create(['name' => 'Mango', 'price' => 100]);
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);

    CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 2]);
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $mango->id, 'quantity' => 1]);

    $order = app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Test User',
        'receiver_phone' => '9876543210',
        'address_line' => 'Test Address',
        'city' => 'Mumbai',
        'state' => 'MH',
        'pincode' => '400001',
    ]);

    expect($order->items)->toHaveCount(2);
    expect($order->subtotal)->toBe(1098);

    $basketItem = $order->items->firstWhere('product_name', 'Wellness Boost');

    expect($basketItem->basket_id)->toBe($basket->id);
    expect($basketItem->product_id)->toBeNull();
    expect($basketItem->price)->toBe(499);
    expect($basketItem->quantity)->toBe(2);
    expect($basketItem->total)->toBe(998);

    expect($user->fresh()->cartItems()->count())->toBe(0);
});

test('basket order items survive cancellation', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->available()->create(['name' => 'Mango']);
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);

    CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 1]);
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $mango->id, 'quantity' => 1]);

    $order = app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Test User',
        'receiver_phone' => '9876543210',
        'address_line' => 'Test Address',
        'city' => 'Mumbai',
        'state' => 'MH',
        'pincode' => '400001',
    ]);

    app(OrderService::class)->cancel($order, 'not needed');

    expect($order->fresh()->status)->toBe(Order::STATUS_CANCELLED);
});

test('guest is redirected to login when adding a basket from its card', function () {
    $basket = Basket::factory()->create();

    Livewire::test(BasketCard::class, ['basket' => $basket])
        ->call('addToCart')
        ->assertRedirect(route('login'));
});

test('authenticated user can add a basket to cart from its card', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost']);

    Livewire::actingAs($user)
        ->test(BasketCard::class, ['basket' => $basket])
        ->call('addToCart')
        ->assertOk()
        ->assertSet('inCart', true)
        ->assertDispatched('cart-updated');

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'basket_id' => $basket->id,
        'product_id' => null,
        'quantity' => 1,
    ]);
});

test('basket card shows quantity counter when already in cart', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost']);

    CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(BasketCard::class, ['basket' => $basket])
        ->assertSet('inCart', true)
        ->assertSet('quantity', 1)
        ->assertSee('Wellness Boost')
        ->assertSeeHtml('aria-label="Decrease quantity"')
        ->assertSeeHtml('aria-label="Increase quantity"');
});

test('basket card counter increments the cart quantity', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost']);

    $item = CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(BasketCard::class, ['basket' => $basket])
        ->call('increment')
        ->assertSet('quantity', 3)
        ->assertDispatched('cart-updated');

    expect($item->fresh()->quantity)->toBe(3);
});

test('basket card counter decrements the cart quantity', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost']);

    $item = CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 3]);

    Livewire::actingAs($user)
        ->test(BasketCard::class, ['basket' => $basket])
        ->call('decrement')
        ->assertSet('quantity', 2)
        ->assertDispatched('cart-updated');

    expect($item->fresh()->quantity)->toBe(2);
});

test('basket card counter removes the basket when quantity reaches zero', function () {
    $user = User::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Wellness Boost']);

    $item = CartItem::create(['user_id' => $user->id, 'basket_id' => $basket->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(BasketCard::class, ['basket' => $basket])
        ->call('decrement')
        ->assertSet('inCart', false)
        ->assertSet('quantity', 1)
        ->assertDispatched('cart-updated');

    $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
});

test('basket card overlay items resolve the selected pivot unit', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Spinach', 'unit' => 'bunch']);
    $unit = ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '500 g', 'price' => 4000]);
    $basket = Basket::factory()->create(['name' => 'Green Bundle']);
    $basket->products()->attach($product, ['product_unit_id' => $unit->id]);

    Livewire::actingAs($user)
        ->test(BasketCard::class, ['basket' => $basket])
        ->call('overlayItems')
        ->assertReturned([
            ['name' => 'Spinach', 'unit' => '500 g'],
        ]);
});
