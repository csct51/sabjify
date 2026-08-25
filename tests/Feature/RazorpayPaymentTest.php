<?php

use App\Livewire\Checkout;
use App\Livewire\Orders\Show;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

test('online checkout opens payment without placing the order first', function () {
    Http::fake([
        'api.razorpay.com/*' => Http::response([
            'id' => 'order_rzp_123',
            'amount' => 14000,
            'currency' => 'INR',
        ]),
    ]);

    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('paymentMethod', 'online')
        ->call('placeOrder')
        ->assertDispatched(
            'razorpay-open',
            key_id: config('razorpay.key_id'),
            order_id: 'order_rzp_123',
            amount: 14000,
        );

    expect(Order::count())->toBe(0)
        ->and($user->cartItems()->count())->toBe(1)
        ->and(session('pending_payment_order_rzp_123'))->not->toBeNull();
});

test('order page offers pay now for an unpaid online order', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'payment_method' => 'online',
        'payment_status' => 'pending',
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['order' => $order])
        ->assertSee('Pay Now')
        ->assertSee('payment is pending');
});

test('paid online orders do not offer pay now', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'online',
        'payment_status' => 'paid',
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['order' => $order])
        ->assertDontSee('Pay Now');
});

test('payOnline creates a razorpay order and dispatches checkout', function () {
    Http::fake([
        'api.razorpay.com/*' => Http::response([
            'id' => 'order_rzp_123',
            'amount' => 14000,
            'currency' => 'INR',
        ]),
    ]);

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'online',
        'payment_status' => 'pending',
        'total' => 140,
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['order' => $order])
        ->call('payOnline')
        ->assertDispatched(
            'razorpay-open',
            key_id: config('razorpay.key_id'),
            order_id: 'order_rzp_123',
            amount: 14000,
            name: config('razorpay.name'),
            description: config('razorpay.description').' '.$order->order_number,
            theme_color: config('razorpay.theme_color'),
        );

    expect($order->fresh()->payment_reference)->toBe('order_rzp_123');
});

test('verify route marks order paid when the signature is valid', function () {
    config(['razorpay.key_secret' => 'secret']);

    Http::fake([
        'api.razorpay.com/v1/payments/pay_rzp_456' => Http::response([
            'id' => 'pay_rzp_456',
            'status' => 'captured',
            'method' => 'upi',
            'vpa' => 'test@upi',
            'amount' => 14000,
            'fee' => 140,
        ]),
    ]);

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'payment_method' => 'online',
        'payment_status' => 'pending',
        'payment_reference' => 'order_rzp_123',
        'total' => 140,
    ]);

    $paymentId = 'pay_rzp_456';
    $signature = hash_hmac('sha256', 'order_rzp_123|'.$paymentId, 'secret');

    $this->actingAs($user)
        ->postJson(route('orders.payment.verify', $order), [
            'razorpay_order_id' => 'order_rzp_123',
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect($order->fresh()->payment_status)->toBe('paid')
        ->and($order->fresh()->payment_id)->toBe($paymentId)
        ->and($order->fresh()->payment_details)->toBe([
            'id' => 'pay_rzp_456',
            'status' => 'captured',
            'method' => 'upi',
            'vpa' => 'test@upi',
            'amount' => 14000,
            'fee' => 140,
        ]);
});

test('order is still marked paid when payment details cannot be fetched', function () {
    config(['razorpay.key_secret' => 'secret']);

    Http::fake([
        'api.razorpay.com/v1/payments/*' => Http::response([], 500),
    ]);

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'payment_method' => 'online',
        'payment_status' => 'pending',
        'payment_reference' => 'order_rzp_123',
    ]);

    $paymentId = 'pay_rzp_456';
    $signature = hash_hmac('sha256', 'order_rzp_123|'.$paymentId, 'secret');

    $this->actingAs($user)
        ->postJson(route('orders.payment.verify', $order), [
            'razorpay_order_id' => 'order_rzp_123',
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect($order->fresh()->payment_status)->toBe('paid')
        ->and($order->fresh()->payment_details)->toBeNull();
});

test('verify route rejects an invalid signature', function () {
    config(['razorpay.key_secret' => 'secret']);

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'payment_method' => 'online',
        'payment_status' => 'pending',
        'payment_reference' => 'order_rzp_123',
    ]);

    $this->actingAs($user)
        ->postJson(route('orders.payment.verify', $order), [
            'razorpay_order_id' => 'order_rzp_123',
            'razorpay_payment_id' => 'pay_rzp_456',
            'razorpay_signature' => 'not-a-valid-signature',
        ])
        ->assertStatus(422);

    expect($order->fresh()->payment_status)->toBe('pending');
});

test('verify route rejects a mismatched razorpay order id', function () {
    config(['razorpay.key_secret' => 'secret']);

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'payment_method' => 'online',
        'payment_status' => 'pending',
        'payment_reference' => 'order_rzp_123',
    ]);

    $paymentId = 'pay_rzp_456';
    $signature = hash_hmac('sha256', 'order_rzp_999|'.$paymentId, 'secret');

    $this->actingAs($user)
        ->postJson(route('orders.payment.verify', $order), [
            'razorpay_order_id' => 'order_rzp_999',
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ])
        ->assertStatus(422);

    expect($order->fresh()->payment_status)->toBe('pending');
});

test('user cannot verify another users payment', function () {
    config(['razorpay.key_secret' => 'secret']);

    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $owner->id,
        'payment_method' => 'online',
        'payment_status' => 'pending',
        'payment_reference' => 'order_rzp_123',
    ]);

    $paymentId = 'pay_rzp_456';
    $signature = hash_hmac('sha256', 'order_rzp_123|'.$paymentId, 'secret');

    $this->actingAs($other)
        ->postJson(route('orders.payment.verify', $order), [
            'razorpay_order_id' => 'order_rzp_123',
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ])
        ->assertForbidden();

    expect($order->fresh()->payment_status)->toBe('pending');
});

test('verified checkout creates a paid order and clears the cart', function () {
    config(['razorpay.key_secret' => 'secret']);

    Http::fake([
        'api.razorpay.com/v1/payments/pay_rzp_456' => Http::response([
            'id' => 'pay_rzp_456',
            'status' => 'captured',
            'method' => 'upi',
            'vpa' => 'test@upi',
            'amount' => 24000,
        ]),
    ]);

    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

    $paymentId = 'pay_rzp_456';
    $signature = hash_hmac('sha256', 'order_rzp_123|'.$paymentId, 'secret');

    session(['pending_payment_order_rzp_123' => [
        'user_id' => $user->id,
        'address' => [
            'receiver_name' => 'Rahul Sharma',
            'receiver_phone' => '9876501234',
            'address_line' => '12 Main Street',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'label' => 'Home',
            'notes' => null,
            'latitude' => 19.0760,
            'longitude' => 72.8777,
        ],
    ]]);

    $this->actingAs($user)
        ->postJson(route('checkout.payment.verify'), [
            'razorpay_order_id' => 'order_rzp_123',
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->payment_method)->toBe('online')
        ->and($order->payment_status)->toBe('paid')
        ->and($order->payment_reference)->toBe('order_rzp_123')
        ->and($order->payment_id)->toBe($paymentId)
        ->and($order->payment_details)->toBe([
            'id' => 'pay_rzp_456',
            'status' => 'captured',
            'method' => 'upi',
            'vpa' => 'test@upi',
            'amount' => 24000,
        ])
        ->and($order->total)->toBe(240)
        ->and($user->cartItems()->count())->toBe(0);
});

test('verified checkout with an invalid signature creates no order', function () {
    config(['razorpay.key_secret' => 'secret']);

    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    session(['pending_payment_order_rzp_123' => [
        'user_id' => $user->id,
        'address' => [
            'receiver_name' => 'Rahul Sharma',
            'receiver_phone' => '9876501234',
            'address_line' => '12 Main Street',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
        ],
    ]]);

    $this->actingAs($user)
        ->postJson(route('checkout.payment.verify'), [
            'razorpay_order_id' => 'order_rzp_123',
            'razorpay_payment_id' => 'pay_rzp_456',
            'razorpay_signature' => 'not-a-valid-signature',
        ])
        ->assertStatus(422);

    expect(Order::count())->toBe(0)
        ->and($user->cartItems()->count())->toBe(1);
});

test('verified checkout is idempotent', function () {
    config(['razorpay.key_secret' => 'secret']);

    Http::fake([
        'api.razorpay.com/v1/payments/pay_rzp_456' => Http::response([
            'id' => 'pay_rzp_456',
            'status' => 'captured',
            'method' => 'card',
            'amount' => 14000,
        ]),
    ]);

    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    $paymentId = 'pay_rzp_456';
    $signature = hash_hmac('sha256', 'order_rzp_123|'.$paymentId, 'secret');

    session(['pending_payment_order_rzp_123' => [
        'user_id' => $user->id,
        'address' => [
            'receiver_name' => 'Rahul Sharma',
            'receiver_phone' => '9876501234',
            'address_line' => '12 Main Street',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'latitude' => 19.0760,
            'longitude' => 72.8777,
        ],
    ]]);

    $payload = [
        'razorpay_order_id' => 'order_rzp_123',
        'razorpay_payment_id' => $paymentId,
        'razorpay_signature' => $signature,
    ];

    $this->actingAs($user)->postJson(route('checkout.payment.verify'), $payload)->assertOk();
    $this->actingAs($user)->postJson(route('checkout.payment.verify'), $payload)->assertOk();

    expect(Order::count())->toBe(1);
});

test('verified checkout rejects a mismatched captured amount', function () {
    config(['razorpay.key_secret' => 'secret']);

    Http::fake([
        'api.razorpay.com/v1/payments/pay_rzp_456' => Http::response([
            'id' => 'pay_rzp_456',
            'status' => 'captured',
            'method' => 'upi',
            'amount' => 999999,
        ]),
    ]);

    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    $paymentId = 'pay_rzp_456';
    $signature = hash_hmac('sha256', 'order_rzp_123|'.$paymentId, 'secret');

    session(['pending_payment_order_rzp_123' => [
        'user_id' => $user->id,
        'address' => [
            'receiver_name' => 'Rahul Sharma',
            'receiver_phone' => '9876501234',
            'address_line' => '12 Main Street',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'latitude' => 19.0760,
            'longitude' => 72.8777,
        ],
    ]]);

    $this->actingAs($user)
        ->postJson(route('checkout.payment.verify'), [
            'razorpay_order_id' => 'order_rzp_123',
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ])
        ->assertStatus(422);

    expect(Order::count())->toBe(0);
});

test('user cannot verify a checkout initiated by another user', function () {
    config(['razorpay.key_secret' => 'secret']);

    $owner = User::factory()->create();
    $other = User::factory()->create();

    session(['pending_payment_order_rzp_123' => [
        'user_id' => $owner->id,
        'address' => [
            'receiver_name' => 'Rahul Sharma',
            'receiver_phone' => '9876501234',
            'address_line' => '12 Main Street',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
        ],
    ]]);

    $signature = hash_hmac('sha256', 'order_rzp_123|pay_rzp_456', 'secret');

    $this->actingAs($other)
        ->postJson(route('checkout.payment.verify'), [
            'razorpay_order_id' => 'order_rzp_123',
            'razorpay_payment_id' => 'pay_rzp_456',
            'razorpay_signature' => $signature,
        ])
        ->assertForbidden();

    expect(Order::count())->toBe(0);
});
