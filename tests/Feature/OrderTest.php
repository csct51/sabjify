<?php

use App\Livewire\Admin\OrderShow;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\Orders\Show;
use App\Models\Admin;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('checkout redirects to cart when cart is empty', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->assertRedirect(route('cart'));
});

test('checkout defaults to a disabled payment method fallback', function () {
    config(['mart.enabled_payment_methods' => ['online']]);

    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->assertSet('paymentMethod', 'online');
});

test('checkout rejects payment methods that are disabled', function () {
    config(['mart.enabled_payment_methods' => ['online']]);

    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertHasErrors('paymentMethod');
});

test('checkout renders with items in cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->assertOk()
        ->assertSet('subtotal', 200);
});

test('placing an order creates order, items and clears cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertRedirect();

    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->user_id)->toBe($user->id)
        ->and($order->subtotal)->toBe(200)
        ->and($order->total)->toBe(240)
        ->and($order->delivery_fee)->toBe(40)
        ->and($order->status)->toBe('pending')
        ->and($order->payment_method)->toBe('cod')
        ->and($order->items->count())->toBe(1)
        ->and($order->items->first()->product_name)->toBe($product->name)
        ->and($user->cartItems()->count())->toBe(0);
});

test('placing an order flashes a success message', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertRedirect();

    $order = Order::first();

    expect(session('success'))->toBe('Order placed successfully. Order no: '.$order->order_number);
});

test('free delivery above threshold', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 600]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->assertSet('deliveryFee', 0);
});

test('checkout is blocked when cart is below the minimum order amount', function () {
    config(['mart.minimum_order_amount' => 200]);

    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertHasErrors('minimum');

    expect(Order::count())->toBe(0)
        ->and($user->cartItems()->count())->toBe(1);
});

test('checkout proceeds when cart meets the minimum order amount', function () {
    config(['mart.minimum_order_amount' => 200]);

    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertRedirect();

    expect(Order::count())->toBe(1);
});

test('cart disables checkout when below the minimum order amount', function () {
    config(['mart.minimum_order_amount' => 200]);

    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->assertSet('belowMinimum', true)
        ->assertSee('Minimum order')
        ->assertSeeHtml('disabled');
});

test('order can be cancelled', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertRedirect();

    $order = Order::first();

    expect(app(OrderService::class)->cancel($order, 'Found a better price elsewhere', 'customer'))->toBeTrue()
        ->and($order->fresh()->status)->toBe('cancelled')
        ->and($order->fresh()->cancelled_reason)->toBe('Found a better price elsewhere')
        ->and($order->fresh()->cancelled_by)->toBe('customer');
});

test('customer can cancel order with a reason', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertRedirect();

    $order = Order::first();

    Livewire::actingAs($user)
        ->test(Show::class, ['order' => $order])
        ->set('cancelReason', 'Wrong address entered')
        ->call('cancelOrder');

    expect($order->fresh()->status)->toBe('cancelled')
        ->and($order->fresh()->cancelled_reason)->toBe('Wrong address entered')
        ->and($order->fresh()->cancelled_by)->toBe('customer');
});

test('customer cannot cancel a confirmed order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'confirmed']);

    Livewire::actingAs($user)
        ->test(Show::class, ['order' => $order])
        ->assertDontSee('Cancel Order')
        ->set('cancelReason', 'Changed my mind')
        ->call('cancelOrder');

    expect($order->fresh()->status)->toBe('confirmed')
        ->and($order->fresh()->cancelled_reason)->toBeNull();
});

test('admin can cancel order on behalf of the platform', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

    Livewire::actingAs(Admin::factory()->create(), 'admin')
        ->test(OrderShow::class, ['order' => $order])
        ->set('cancelReason', 'Stock unavailable')
        ->call('cancelOrder');

    expect($order->fresh()->status)->toBe('cancelled')
        ->and($order->fresh()->cancelled_reason)->toBe('Stock unavailable')
        ->and($order->fresh()->cancelled_by)->toBe('platform');
});

test('customer sees payment details for a paid online order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'online',
        'payment_status' => 'paid',
        'payment_id' => 'pay_rzp_456',
        'payment_details' => [
            'method' => 'upi',
            'status' => 'captured',
            'amount' => 14000,
            'vpa' => 'test@upi',
        ],
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['order' => $order])
        ->assertSee('Payment Details')
        ->assertSee('UPI')
        ->assertSee('test@upi')
        ->assertSee('pay_rzp_456');
});

test('customer sees empty payment details for a legacy online order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'online',
        'payment_status' => 'paid',
        'payment_reference' => 'legacy_order_ref',
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['order' => $order])
        ->assertSee('Payment Details')
        ->assertSee('Method')
        ->assertSee('UPI')
        ->assertSee('—');
});

test('admin sees empty payment details for a legacy online order', function () {
    $order = Order::factory()->create([
        'payment_method' => 'online',
        'payment_status' => 'paid',
        'payment_reference' => 'legacy_order_ref',
    ]);

    Livewire::actingAs(Admin::factory()->create(), 'admin')
        ->test(OrderShow::class, ['order' => $order])
        ->assertSee('Payment')
        ->assertSee('Method')
        ->assertSee('UPI')
        ->assertSee('—');
});

test('customer can download the invoice for their order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'subtotal' => 200,
        'delivery_fee' => 40,
        'total' => 240,
    ]);

    $this->actingAs($user)
        ->get(route('orders.invoice', $order))
        ->assertOk()
        ->assertDownload('invoice-'.$order->order_number.'.pdf');
});

test('customer cannot download another users invoice', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->get(route('orders.invoice', $order))
        ->assertForbidden();
});

test('guest cannot download an invoice', function () {
    $order = Order::factory()->create();

    $this->get(route('orders.invoice', $order))
        ->assertRedirect(route('login'));
});

test('admin can download an invoice for any order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.orders.invoice', $order))
        ->assertOk()
        ->assertDownload('invoice-'.$order->order_number.'.pdf');
});

test('admin cannot access customer invoice route without admin session', function () {
    $order = Order::factory()->create();

    $this->get(route('orders.invoice', $order))
        ->assertRedirect(route('login'));
});

test('invoice embeds the store logo as a data uri', function () {
    Storage::fake('public');

    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

    Storage::disk('public')->put('logos/logo.png', $png);

    Setting::updateOrCreate(['key' => 'logo_type'], ['value' => 'image']);
    Setting::updateOrCreate(['key' => 'logo_value'], ['value' => 'logos/logo.png']);

    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $html = view('invoices.order', ['order' => $order])->render();

    expect(Setting::logoDataUri())->toStartWith('data:image/png;base64,')
        ->and($html)->toContain('data:image/png;base64,');
});

test('invoice renders without a logo when none is configured', function () {
    Setting::whereIn('key', ['logo_type', 'logo_value'])->delete();

    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('orders.invoice', $order))
        ->assertOk()
        ->assertDownload('invoice-'.$order->order_number.'.pdf');
});

test('admin sees payment details for a paid online order', function () {
    $order = Order::factory()->create([
        'payment_method' => 'online',
        'payment_status' => 'paid',
        'payment_id' => 'pay_rzp_456',
        'payment_details' => [
            'method' => 'upi',
            'status' => 'captured',
            'amount' => 14000,
            'vpa' => 'test@upi',
        ],
    ]);

    Livewire::actingAs(Admin::factory()->create(), 'admin')
        ->test(OrderShow::class, ['order' => $order])
        ->assertSee('Payment')
        ->assertSee('UPI')
        ->assertSee('test@upi')
        ->assertSee('pay_rzp_456');
});

test('customer must provide a reason to cancel', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

    Livewire::actingAs($user)
        ->test(Show::class, ['order' => $order])
        ->set('showCancelForm', true)
        ->call('cancelOrder')
        ->assertHasErrors(['cancelReason' => 'required'])
        ->assertSee('Please tell us why you are cancelling this order.')
        ->assertNotSet('order.status', 'cancelled');
});

test('delivered orders cannot be cancelled', function () {
    $user = User::factory()->create();
    $order = Order::factory()->delivered()->create(['user_id' => $user->id]);

    expect(app(OrderService::class)->cancel($order))->toBeFalse()
        ->and($order->fresh()->status)->toBe('delivered');
});

test('user only sees their own orders', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Order::factory()->create(['user_id' => $user->id]);
    Order::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user)->get('/orders')->assertOk()->assertSee(Order::where('user_id', $user->id)->first()->order_number);
});

test('user cannot view another users order', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)->get('/orders/'.$order->id)->assertForbidden();
});

test('checkout is blocked when a product in the cart is out of stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'in_stock' => false]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertHasErrors('stock');

    expect(Order::count())->toBe(0)
        ->and($user->cartItems()->count())->toBe(1);
});

test('checkout is blocked when any product in the cart is out of stock', function () {
    $user = User::factory()->create();
    $inStock = Product::factory()->available()->create(['price' => 100]);
    $outOfStock = Product::factory()->create(['price' => 50, 'in_stock' => false]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $inStock->id, 'quantity' => 1]);
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $outOfStock->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertHasErrors('stock');

    expect(Order::count())->toBe(0);
});

test('checkout is allowed when all products are in stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertRedirect();

    expect(Order::count())->toBe(1);
});

test('cart disables checkout when a product is out of stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'in_stock' => false]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Cart::class)
        ->assertSet('outOfStockItems', function ($items) use ($product) {
            expect($items)->toHaveCount(1)
                ->and($items->first()->product_id)->toBe($product->id);

            return true;
        })
        ->assertSee('Some items in your cart are out of stock');
});

test('order service rejects a cart with an out of stock product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'in_stock' => false]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    expect(fn () => app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Rahul',
        'receiver_phone' => '9876501234',
        'address_line' => '12 Main Street',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'pincode' => '400001',
    ]))->toThrow(RuntimeException::class, 'Some items are out of stock');

    expect(Order::count())->toBe(0)
        ->and($user->cartItems()->count())->toBe(1);
});

test('order service allows in stock products', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    $order = app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Rahul',
        'receiver_phone' => '9876501234',
        'address_line' => '12 Main Street',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'pincode' => '400001',
    ]);

    expect(Order::count())->toBe(1);
});

test('order creation requires a non-empty cart', function () {
    $user = User::factory()->create();

    expect(fn () => app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Rahul',
        'receiver_phone' => '9876501234',
        'address_line' => '12 Main Street',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'pincode' => '400001',
    ]))->toThrow(RuntimeException::class);
});

test('order numbers are sequential and zero padded', function () {
    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    for ($i = 0; $i < 3; $i++) {
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        Livewire::actingAs($user)
            ->test(Checkout::class)
            ->set('addressMode', 'new')
            ->set('receiverName', 'Rahul Sharma')
            ->set('receiverPhone', '9876501234')
            ->set('addressLine', '12 Main Street')
            ->set('city', 'Mumbai')
            ->set('state', 'Maharashtra')
            ->set('pincode', '400001')
            ->set('paymentMethod', 'cod')
            ->call('placeOrder')
            ->assertRedirect();
    }

    expect(Order::orderBy('id')->pluck('order_number')->all())
        ->toBe(['ORD-001', 'ORD-002', 'ORD-003']);
});

test('order numbers continue after existing orders', function () {
    $user = User::factory()->create();
    Order::factory()->create(['user_id' => $user->id]);
    Order::factory()->create(['user_id' => $user->id]);

    $maxBefore = Order::query()
        ->pluck('order_number')
        ->map(fn (string $number): ?int => ctype_digit(substr($number, 4)) ? (int) substr($number, 4) : null)
        ->filter()
        ->max() ?? 0;

    $product = Product::factory()->available()->create(['price' => 100]);
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertRedirect();

    expect(Order::orderBy('id')->get()->last()->order_number)->toBe('ORD-'.str_pad((string) ($maxBefore + 1), 3, '0', STR_PAD_LEFT));
});

test('legacy random order numbers do not break sequential generation', function () {
    $user = User::factory()->create();
    Order::factory()->create(['user_id' => $user->id, 'order_number' => 'ORD-'.Str::upper(Str::random(8))]);

    $product = Product::factory()->available()->create(['price' => 100]);
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertRedirect();

    expect(Order::orderBy('id')->get()->last()->order_number)->toBe('ORD-001');
});
