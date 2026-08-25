<?php

use App\Livewire\StoreHeader;
use App\Models\Address;
use App\Models\DeliveryLocation;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('detects a guest location and shows delivery available when no zones are configured', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => [
                'road' => 'MG Road',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postcode' => '400001',
            ],
        ]),
    ]);

    Livewire::test(StoreHeader::class)
        ->call('detectLocation', 19.076, 72.8777)
        ->assertSet('hasLocation', true)
        ->assertSet('deliveryAvailable', true)
        ->assertSee('MG Road')
        ->assertSee('Delivery available');
});

it('shows not delivering here for a guest outside every active delivery zone', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => ['city' => 'Mumbai', 'state' => 'Maharashtra', 'postcode' => '400001'],
        ]),
    ]);

    DeliveryLocation::factory()->create([
        'name' => 'Far Zone',
        'latitude' => 28.6139,
        'longitude' => 77.209,
        'radius_km' => 1,
        'is_active' => true,
    ]);

    Livewire::test(StoreHeader::class)
        ->call('detectLocation', 19.076, 72.8777)
        ->assertSet('deliveryAvailable', false)
        ->assertSee('Not delivering here');
});

it('shows delivery available for a logged in user whose default address is inside a zone', function () {
    $user = User::factory()->create();
    Address::factory()->withLocation(19.076, 72.8777)->create([
        'user_id' => $user->id,
        'is_default' => true,
        'address_line' => '12, MG Road',
        'city' => 'Mumbai',
    ]);

    DeliveryLocation::factory()->create([
        'name' => 'Mumbai',
        'latitude' => 19.076,
        'longitude' => 72.8777,
        'radius_km' => 10,
        'is_active' => true,
    ]);

    Livewire::actingAs($user)
        ->test(StoreHeader::class)
        ->assertSet('hasLocation', true)
        ->assertSet('deliveryAvailable', true)
        ->assertSee('12, MG Road')
        ->assertSee('Delivery available');
});

it('prompts guests to use their location when none has been detected yet', function () {
    Livewire::test(StoreHeader::class)
        ->assertSet('needsDetection', true)
        ->assertSee('Use my location');
});

test('home page renders the default address inside the header', function () {
    $user = User::factory()->create();
    Address::factory()->create([
        'user_id' => $user->id,
        'is_default' => true,
        'address_line' => '12, MG Road',
        'city' => 'Mumbai',
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('12, MG Road');
});

test('header does not show an address for guests', function () {
    Livewire::test(StoreHeader::class)
        ->assertDontSee('MG Road');
});

test('header does not show an address when the user has no default address', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(StoreHeader::class)
        ->assertDontSee('MG Road');
});

test('header shows the default delivery address', function () {
    $user = User::factory()->create();
    Address::factory()->create([
        'user_id' => $user->id,
        'is_default' => true,
        'address_line' => '12, MG Road',
        'city' => 'Mumbai',
    ]);

    Livewire::actingAs($user)
        ->test(StoreHeader::class)
        ->assertSee('12, MG Road');
});

test('header refreshes when the address-updated event fires', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(StoreHeader::class)
        ->assertDontSee('MG Road');

    Address::factory()->create([
        'user_id' => $user->id,
        'is_default' => true,
        'address_line' => '12, MG Road',
        'city' => 'Mumbai',
    ]);

    $component
        ->call('refreshAddress')
        ->assertSee('12, MG Road');
});

test('clicking the address chip opens the address prompt', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);

    Livewire::actingAs($user)
        ->test(StoreHeader::class)
        ->call('openAddressPrompt')
        ->assertDispatched('open-address-prompt');
});

test('header ignores addresses that are not the default', function () {
    $user = User::factory()->create();
    Address::factory()->create([
        'user_id' => $user->id,
        'is_default' => false,
        'address_line' => '45, Linking Road',
    ]);

    Livewire::actingAs($user)
        ->test(StoreHeader::class)
        ->assertDontSee('Linking Road');
});
