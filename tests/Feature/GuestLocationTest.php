<?php

use App\Livewire\AddAddressPrompt;
use App\Livewire\GuestAddressClaimPrompt;
use App\Models\DeliveryLocation;
use App\Models\User;
use Livewire\Livewire;

test('guest can open address prompt and see guest form', function () {
    Livewire::test(AddAddressPrompt::class)
        ->call('openFromHeader')
        ->assertSet('show', true)
        ->assertSet('view', 'guest')
        ->assertSee('Set delivery location');
});

test('guest can save location and it is stored in session', function () {
    DeliveryLocation::factory()->create([
        'latitude' => 21.2514,
        'longitude' => 81.6296,
        'radius_km' => 10,
        'is_active' => true,
    ]);

    Livewire::test(AddAddressPrompt::class)
        ->call('openFromHeader')
        ->set('addressLine', '12 Guest Street')
        ->set('latitude', 21.2514)
        ->set('longitude', 81.6296)
        ->call('saveGuestLocation')
        ->assertHasNoErrors()
        ->assertSet('show', false);

    $guest = session('guest_delivery_location');

    expect($guest)->not->toBeNull()
        ->and($guest['address_line'])->toBe('12 Guest Street')
        ->and($guest['label'])->toBe('12 Guest Street')
        ->and($guest['address_label'])->toBe('Home')
        ->and($guest['receiver_name'])->toBeNull()
        ->and($guest['available'])->toBeTrue();
});

test('guest cannot save location when outside radius', function () {
    DeliveryLocation::factory()->create([
        'latitude' => 21.2514,
        'longitude' => 81.6296,
        'radius_km' => 5,
        'is_active' => true,
    ]);

    Livewire::test(AddAddressPrompt::class)
        ->call('openFromHeader')
        ->set('addressLine', 'Far Away')
        ->set('latitude', 28.6139)
        ->set('longitude', 77.2090)
        ->call('saveGuestLocation')
        ->assertHasErrors('delivery');

    expect(session('guest_delivery_location'))->toBeNull();
});

test('guest claim prompt shows after login when session has guest address', function () {
    $user = User::factory()->create();

    session()->put('guest_delivery_location', [
        'label' => '12 Guest Street',
        'address_label' => 'Home',
        'receiver_name' => 'Guest User',
        'receiver_phone' => '9876501234',
        'address_line' => '12 Guest Street',
        'landmark' => null,
        'city' => 'Raipur',
        'state' => 'Chhattisgarh',
        'pincode' => 0,
        'latitude' => 21.2514,
        'longitude' => 81.6296,
        'lat' => 21.2514,
        'lng' => 81.6296,
        'available' => true,
    ]);

    Livewire::actingAs($user)
        ->test(GuestAddressClaimPrompt::class)
        ->assertSet('show', true)
        ->assertSee('Save your recent location?');
});

test('guest can claim address and it is saved to database', function () {
    $user = User::factory()->create();

    session()->put('guest_delivery_location', [
        'label' => '12 Guest Street',
        'address_label' => 'Home',
        'receiver_name' => 'Guest User',
        'receiver_phone' => '9876501234',
        'address_line' => '12 Guest Street',
        'landmark' => null,
        'city' => 'Raipur',
        'state' => 'Chhattisgarh',
        'pincode' => 0,
        'latitude' => 21.2514,
        'longitude' => 81.6296,
        'lat' => 21.2514,
        'lng' => 81.6296,
        'available' => true,
    ]);

    Livewire::actingAs($user)
        ->test(GuestAddressClaimPrompt::class)
        ->call('claim')
        ->assertSet('show', false);

    expect($user->addresses()->count())->toBe(1)
        ->and($user->addresses()->first()->address_line)->toBe('12 Guest Street');

    expect(session('guest_delivery_location'))->toBeNull();
});

test('guest claim prompt can be dismissed', function () {
    $user = User::factory()->create();

    session()->put('guest_delivery_location', [
        'address_line' => '12 Guest Street',
        'latitude' => 21.2514,
        'longitude' => 81.6296,
        'available' => true,
    ]);

    Livewire::actingAs($user)
        ->test(GuestAddressClaimPrompt::class)
        ->call('dismiss')
        ->assertSet('show', false);

    expect(session('guest_delivery_location'))->toBeNull();
});
