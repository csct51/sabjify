<?php

use App\Livewire\Admin\BasketForm;
use App\Livewire\Admin\Baskets;
use App\Livewire\BasketShow;
use App\Livewire\Cart;
use App\Models\Admin;
use App\Models\Basket;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Livewire\Livewire;

test('guest is redirected to admin login when accessing baskets', function () {
    $this->get('/admin/baskets')->assertRedirect(route('admin.login'));
});

test('admin can create a wellness basket with products, units and prices', function () {
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
        ->set('units', [$mango->id => '2 pcs', $mint->id => '1 bunch'])
        ->set('prices', [$mango->id => 120, $mint->id => 40])
        ->call('save')
        ->assertRedirect(route('admin.baskets.index'));

    $basket = Basket::where('slug', 'wellness-boost-basket')->first();

    expect($basket)->not->toBeNull();
    expect($basket->type)->toBe(Basket::TYPE_WELLNESS);
    expect($basket->price)->toBe(499);

    $pivot = $basket->products()->find($mango->id)->pivot;

    expect($pivot->unit)->toBe('2 pcs');
    expect($pivot->price)->toBe(120);

    $mintPivot = $basket->products()->find($mint->id)->pivot;

    expect($mintPivot->unit)->toBe('1 bunch');
    expect($mintPivot->price)->toBe(40);
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

test('admin can update a basket', function () {
    $admin = Admin::factory()->create();
    $oldProduct = Product::factory()->create();
    $newProduct = Product::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Old Basket']);
    $basket->products()->attach($oldProduct, ['unit' => '1 pcs', 'price' => 50]);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class, ['basket' => $basket])
        ->set('name', 'New Basket')
        ->set('price', 599)
        ->set('productIds', [$newProduct->id])
        ->set('units', [$newProduct->id => '2 kg'])
        ->set('prices', [$newProduct->id => 90])
        ->call('save')
        ->assertRedirect(route('admin.baskets.index'));

    expect($basket->fresh()->name)->toBe('New Basket');
    expect($basket->fresh()->price)->toBe(599);
    expect($basket->fresh()->products()->pluck('products.id')->all())->toEqualCanonicalizing([$newProduct->id]);

    $pivot = $basket->fresh()->products()->find($newProduct->id)->pivot;

    expect($pivot->unit)->toBe('2 kg');
    expect($pivot->price)->toBe(90);
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
        ->assertSee('Wellness Boost');
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
    $mango = Product::factory()->available()->create(['name' => 'Mango', 'stock' => 10, 'price' => 100]);
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);
    $basket->products()->attach($mango, ['unit' => '2 pcs', 'price' => 120]);

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
        ->assertSee('2 pcs')
        ->assertSee('₹120');
});

test('admin order page shows basket contents', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();
    $mango = Product::factory()->available()->create(['name' => 'Mango', 'stock' => 10, 'price' => 100]);
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);
    $basket->products()->attach($mango, ['unit' => '2 pcs', 'price' => 120]);

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
        ->assertSee('2 pcs')
        ->assertSee('₹120');
});

test('basket detail page shows name, price and inside products', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->create(['name' => 'Mango']);
    $basket = Basket::factory()->create(['name' => 'Wellness Boost', 'price' => 499]);
    $basket->products()->attach($mango, ['unit' => '2 pcs', 'price' => 120]);

    Livewire::actingAs($user)
        ->test(BasketShow::class, ['basket' => $basket])
        ->assertOk()
        ->assertSee('Wellness Boost')
        ->assertSee('₹499')
        ->assertSee("What's inside", false)
        ->assertSee('Mango')
        ->assertSee('2 pcs')
        ->assertSee('₹120');
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
        ->assertSet('cartMessage', '"Wellness Boost" added to your cart.');

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
    $mango = Product::factory()->available()->create(['name' => 'Mango', 'stock' => 10, 'price' => 100]);
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

    expect($mango->fresh()->stock)->toBe(9);
    expect($user->fresh()->cartItems()->count())->toBe(0);
});

test('basket order items survive cancellation', function () {
    $user = User::factory()->create();
    $mango = Product::factory()->available()->create(['name' => 'Mango', 'stock' => 5]);
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
    expect($mango->fresh()->stock)->toBe(5);
});
