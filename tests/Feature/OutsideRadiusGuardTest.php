<?php

use App\Livewire\AddAddressPrompt;
use App\Livewire\Checkout;
use App\Livewire\Profile\Addresses;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\DeliveryLocation;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

test('profile cannot save address when outside delivery radius', function () {
    DeliveryLocation::factory()->create([
        'latitude' => 21.2514,
        'longitude' => 81.6296,
        'radius_km' => 5,
        'is_active' => true,
    ]);

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->set('label', 'Home')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('latitude', 28.6139)
        ->set('longitude', 77.2090)
        ->call('saveAddress')
        ->assertHasErrors('delivery');

    expect(Address::count())->toBe(0);
});

test('profile cannot save address when no pin and zone exists', function () {
    DeliveryLocation::factory()->create([
        'latitude' => 21.2514,
        'longitude' => 81.6296,
        'radius_km' => 5,
        'is_active' => true,
    ]);

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->set('label', 'Home')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('latitude', null)
        ->set('longitude', null)
        ->call('saveAddress')
        ->assertHasErrors('delivery');

    expect(Address::count())->toBe(0);
});

test('profile cannot update address to outside radius', function () {
    DeliveryLocation::factory()->create([
        'latitude' => 21.2514,
        'longitude' => 81.6296,
        'radius_km' => 5,
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $address = Address::factory()->create([
        'user_id' => $user->id,
        'latitude' => 21.2514,
        'longitude' => 81.6296,
    ]);

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('editAddress', $address->id)
        ->set('latitude', 28.6139)
        ->set('longitude', 77.2090)
        ->call('saveAddress')
        ->assertHasErrors('delivery');

    expect($address->fresh()->latitude)->toBe(21.2514);
});

test('add address prompt cannot save when outside radius', function () {
    DeliveryLocation::factory()->create([
        'latitude' => 21.2514,
        'longitude' => 81.6296,
        'radius_km' => 5,
        'is_active' => true,
    ]);

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->set('label', 'Home')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('latitude', 28.6139)
        ->set('longitude', 77.2090)
        ->call('saveAddress')
        ->assertHasErrors('delivery');

    expect(Address::count())->toBe(0);
});

test('checkout nextFromAddress does not persist when outside radius', function () {
    DeliveryLocation::factory()->create([
        'latitude' => 21.2514,
        'longitude' => 81.6296,
        'radius_km' => 5,
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $product = Product::factory()->available()->create(['price' => 100]);
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->set('addressMode', 'new')
        ->set('label', 'Home')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('latitude', 28.6139)
        ->set('longitude', 77.2090)
        ->set('saveAddress', true)
        ->call('nextFromAddress')
        ->assertHasErrors('delivery')
        ->assertSet('step', 1);

    expect(Address::count())->toBe(0);
});

test('saving outside is allowed when no delivery zones configured', function () {
    // No zones -> unrestricted
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->set('label', 'Home')
        ->set('receiverName', 'Rahul Sharma')
        ->set('receiverPhone', '9876501234')
        ->set('addressLine', '12 Main Street')
        ->set('latitude', 28.6139)
        ->set('longitude', 77.2090)
        ->call('saveAddress')
        ->assertHasNoErrors();

    expect(Address::count())->toBe(1);
});
