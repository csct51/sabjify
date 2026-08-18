<?php

use App\Livewire\Admin\DeliveryLocationForm;
use App\Livewire\Admin\DeliveryLocations;
use App\Models\Admin;
use App\Models\DeliveryLocation;
use Livewire\Livewire;

test('guest is redirected to admin login when accessing delivery locations', function () {
    $this->get('/admin/delivery-locations')->assertRedirect(route('admin.login'));
});

test('admin can view the delivery locations index', function () {
    $admin = Admin::factory()->create();
    DeliveryLocation::factory()->create(['name' => 'Mumbai Central']);

    $this->actingAs($admin, 'admin')
        ->get('/admin/delivery-locations')
        ->assertOk()
        ->assertSee('Mumbai Central');
});

test('admin can create a delivery location', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(DeliveryLocationForm::class)
        ->set('name', 'Mumbai Central')
        ->set('latitude', 19.076)
        ->set('longitude', 72.8777)
        ->set('radiusKm', 8)
        ->call('save')
        ->assertRedirect(route('admin.delivery-locations.index'));

    $location = DeliveryLocation::where('name', 'Mumbai Central')->first();

    expect($location)->not->toBeNull();
    expect($location->latitude)->toBe(19.076);
    expect($location->longitude)->toBe(72.8777);
    expect($location->radius_km)->toBe(8.0);
    expect($location->is_active)->toBeTrue();
});

test('admin can edit a delivery location', function () {
    $admin = Admin::factory()->create();
    $location = DeliveryLocation::factory()->create(['name' => 'Mumbai Central', 'radius_km' => 8]);

    Livewire::actingAs($admin, 'admin')
        ->test(DeliveryLocationForm::class, ['deliveryLocation' => $location])
        ->set('name', 'Andheri West')
        ->set('radiusKm', 12)
        ->call('save')
        ->assertRedirect(route('admin.delivery-locations.index'));

    expect($location->fresh()->name)->toBe('Andheri West');
    expect($location->fresh()->radius_km)->toBe(12.0);
});

test('admin form shows latitude and longitude in readonly inputs', function () {
    $admin = Admin::factory()->create();
    $location = DeliveryLocation::factory()->create(['name' => 'Mumbai Central', 'latitude' => 19.076, 'longitude' => 72.8777]);

    Livewire::actingAs($admin, 'admin')
        ->test(DeliveryLocationForm::class, ['deliveryLocation' => $location])
        ->assertSeeHtml('id="latitude" type="text" value="19.076" readonly')
        ->assertSeeHtml('id="longitude" type="text" value="72.8777" readonly')
        ->assertSet('latitude', 19.076)
        ->assertSet('longitude', 72.8777);
});

test('admin form updates coordinates from the map', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(DeliveryLocationForm::class)
        ->call('updateLocation', 21.1619, 79.0848, 8)
        ->assertSet('latitude', 21.1619)
        ->assertSet('longitude', 79.0848)
        ->assertSet('radiusKm', 8.0);
});

test('admin form updates the radius from the map', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(DeliveryLocationForm::class)
        ->call('updateRadius', 12)
        ->assertSet('radiusKm', 12.0);
});

test('admin can toggle a delivery location active state', function () {
    $admin = Admin::factory()->create();
    $location = DeliveryLocation::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin, 'admin')
        ->test(DeliveryLocations::class)
        ->call('toggleActive', $location->id);

    expect($location->fresh()->is_active)->toBeFalse();
});

test('admin can delete a delivery location', function () {
    $admin = Admin::factory()->create();
    $location = DeliveryLocation::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(DeliveryLocations::class)
        ->call('delete', $location->id);

    expect(DeliveryLocation::find($location->id))->toBeNull();
});

test('delivery location requires valid coordinates and radius', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(DeliveryLocationForm::class)
        ->set('name', '')
        ->set('latitude', 95)
        ->set('longitude', 200)
        ->set('radiusKm', 0)
        ->call('save')
        ->assertHasErrors([
            'name' => 'required',
            'latitude' => 'between',
            'longitude' => 'between',
            'radiusKm' => 'gt',
        ]);
});
