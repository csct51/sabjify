<?php

use App\Livewire\Profile\Addresses;
use App\Models\Address;
use App\Models\DeliveryLocation;
use App\Models\User;
use Livewire\Livewire;

test('guest is redirected to login when visiting the saved addresses page', function () {
    $this->get(route('profile.addresses'))->assertRedirect(route('login'));
});

test('authenticated user can view their saved addresses', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->assertOk()
        ->assertSee('My Addresses');
});

test('address form offers to use the current location', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('openAddressForm')
        ->assertSee('Use my current location')
        ->assertSee('locate:request');
});

test('user can add a saved address', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('openAddressForm')
        ->set('label', 'Home')
        ->set('receiverName', 'Aarav Sharma')
        ->set('receiverPhone', '9876543210')
        ->set('addressLine', '12, MG Road')
        ->set('isDefault', true)
        ->call('saveAddress')
        ->assertHasNoErrors()
        ->assertSet('addressMode', 'list');

    $this->assertDatabaseHas('addresses', [
        'user_id' => $user->id,
        'label' => 'Home',
        'address_line' => '12, MG Road',
        'city' => 'Raipur',
        'state' => 'Chhattisgarh',
        'pincode' => '0',
        'is_default' => true,
    ]);
});

test('address requires a valid Indian phone', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('openAddressForm')
        ->set('label', 'Home')
        ->set('receiverName', 'Aarav Sharma')
        ->set('receiverPhone', '12345')
        ->set('addressLine', '12, MG Road')
        ->call('saveAddress')
        ->assertHasErrors(['receiverPhone']);
});

test('user can save an address with a pinned location', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('openAddressForm')
        ->set('label', 'Home')
        ->set('receiverName', 'Aarav Sharma')
        ->set('receiverPhone', '9876543210')
        ->set('addressLine', '12, MG Road')
        ->set('latitude', 19.076)
        ->set('longitude', 72.8777)
        ->call('saveAddress')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('addresses', [
        'user_id' => $user->id,
        'latitude' => 19.076,
        'longitude' => 72.8777,
    ]);
});

test('pinning a location records coordinates and checks deliverability', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('openAddressForm')
        ->call('reverseGeocode', 19.0596, 72.8295)
        ->assertSet('latitude', 19.0596)
        ->assertSet('longitude', 72.8295)
        ->assertSet('addressLine', '')
        ->assertReturned(true);
});

test('check deliverable reports whether a pin is inside an active delivery location', function () {
    DeliveryLocation::factory()->create([
        'latitude' => 19.076,
        'longitude' => 72.8777,
        'radius_km' => 5,
        'is_active' => true,
    ]);

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('checkDeliverable', 19.076, 72.8777)
        ->assertReturned(true)
        ->call('checkDeliverable', 28.6139, 77.2090)
        ->assertReturned(false);
});

test('pinning a location sets the address coordinates', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('openAddressForm')
        ->call('updateLocation', 21.1619, 79.0848)
        ->assertSet('latitude', 21.1619)
        ->assertSet('longitude', 79.0848);
});

test('user can edit an address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $originalCity = $address->city;

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('editAddress', $address)
        ->assertSet('editingAddressId', $address->id)
        ->set('addressLine', '88, Linking Road')
        ->call('saveAddress')
        ->assertHasNoErrors()
        ->assertSet('editingAddressId', null);

    expect($address->fresh())
        ->address_line->toBe('88, Linking Road')
        ->city->toBe($originalCity);
});

test('user can delete an address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('deleteAddress', $address)
        ->assertOk();

    expect(Address::find($address->id))->toBeNull();
});

test('user can set an address as default', function () {
    $user = User::factory()->create();
    $first = Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);
    $second = Address::factory()->create(['user_id' => $user->id, 'is_default' => false]);

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('setDefaultAddress', $second)
        ->assertOk();

    expect($first->fresh()->is_default)->toBeFalse();
    expect($second->fresh()->is_default)->toBeTrue();
});

test('deleting the default address promotes another address', function () {
    $user = User::factory()->create();
    $default = Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);
    $other = Address::factory()->create(['user_id' => $user->id, 'is_default' => false]);

    Livewire::actingAs($user)
        ->test(Addresses::class)
        ->call('deleteAddress', $default)
        ->assertOk();

    expect($other->fresh()->is_default)->toBeTrue();
});

test('user cannot manage another user addresses', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $owner->id]);

    Livewire::actingAs($other)
        ->test(Addresses::class)
        ->call('editAddress', $address)
        ->assertForbidden();

    Livewire::actingAs($other)
        ->test(Addresses::class)
        ->call('deleteAddress', $address)
        ->assertForbidden();

    expect(Address::find($address->id))->not->toBeNull();
});
