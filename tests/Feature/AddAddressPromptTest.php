<?php

use App\Livewire\AddAddressPrompt;
use App\Models\Address;
use App\Models\User;
use Livewire\Livewire;

test('prompt is not shown automatically after login', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->assertSet('show', false)
        ->assertDontSee('Select a delivery address');
});

test('prompt opens the select screen from the header', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id, 'address_line' => '12, MG Road']);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->dispatch('open-address-prompt')
        ->assertSet('show', true)
        ->assertSet('view', 'select')
        ->assertSet('dismissable', true)
        ->assertSee('Select a delivery address')
        ->assertSee('12, MG Road')
        ->assertSee('Confirm Address')
        ->assertSee('Add New Address');
});

test('prompt is shown from the header even when the user has a default address', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->dispatch('open-address-prompt')
        ->assertSee('Select a delivery address');
});

test('prompt closes after an address is selected', function () {
    $user = User::factory()->create();
    $other = Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);
    $selected = Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->dispatch('open-address-prompt')
        ->call('selectAddress', $selected->id)
        ->assertSet('selectedAddressId', $selected->id)
        ->call('confirmSelection')
        ->assertHasNoErrors()
        ->assertSet('show', false)
        ->assertDispatched('address-updated')
        ->assertDispatched('toast');

    $this->assertDatabaseHas('addresses', ['id' => $selected->id, 'is_default' => true]);
    $this->assertDatabaseHas('addresses', ['id' => $other->id, 'is_default' => false]);
});

test('prompt prefills the receiver details when opened from the header', function () {
    $user = User::factory()->create(['name' => 'Aarav Sharma', 'phone' => '9876543210']);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->dispatch('open-address-prompt')
        ->assertSet('receiverName', 'Aarav Sharma')
        ->assertSet('receiverPhone', '9876543210');
});

test('confirming without a selection shows an error', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->dispatch('open-address-prompt')
        ->set('selectedAddressId', null)
        ->call('confirmSelection')
        ->assertHasErrors('selection')
        ->assertSet('show', true);
});

test('user can open the add address form from the prompt', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->dispatch('open-address-prompt')
        ->call('openAddForm')
        ->assertSet('view', 'form')
        ->assertSee('Add your delivery address')
        ->call('backToSelect')
        ->assertSet('view', 'select');
});

test('add address form offers to use the current location', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->dispatch('open-address-prompt')
        ->call('openAddForm')
        ->assertSee('Use my current location')
        ->assertSee('locate:request');
});

test('user can add their first address from the prompt', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->dispatch('open-address-prompt')
        ->call('openAddForm')
        ->set('receiverName', 'Aarav Sharma')
        ->set('receiverPhone', '9876543210')
        ->set('addressLine', '12, MG Road')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('latitude', 19.076)
        ->set('longitude', 72.8777)
        ->call('saveAddress')
        ->assertHasNoErrors()
        ->assertSet('show', false)
        ->assertDispatched('address-updated')
        ->assertDispatched('toast');

    $this->assertDatabaseHas('addresses', [
        'user_id' => $user->id,
        'label' => 'Home',
        'receiver_name' => 'Aarav Sharma',
        'is_default' => true,
        'latitude' => 19.076,
        'longitude' => 72.8777,
    ]);
});

test('prompt validates the address fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->dispatch('open-address-prompt')
        ->call('openAddForm')
        ->set('receiverName', '')
        ->set('receiverPhone', '12345')
        ->set('pincode', '123')
        ->call('saveAddress')
        ->assertHasErrors(['receiverName', 'receiverPhone', 'addressLine', 'city', 'state', 'pincode'])
        ->assertSet('show', true);
});

test('prompt opened from the header can be dismissed', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->dispatch('open-address-prompt')
        ->assertSet('show', true)
        ->assertSet('dismissable', true)
        ->assertSeeHtml('aria-label="Close"')
        ->call('dismiss')
        ->assertSet('show', false)
        ->assertSet('dismissable', false);
});

test('home page does not force the address prompt for a logged-in user', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertDontSee('Select a delivery address');
});

test('guests can browse the storefront without being redirected to login', function () {
    $this->get('/')->assertOk();
    $this->get('/shop')->assertOk();
    $this->get('/categories')->assertOk();
});
