<?php

use App\Livewire\AddAddressPrompt;
use App\Livewire\Auth\PhoneLogin;
use App\Models\Address;
use App\Models\OtpCode;
use App\Models\User;
use Livewire\Livewire;

test('prompt asks a user with no saved addresses to select one', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->assertSet('show', true)
        ->assertSet('view', 'select')
        ->assertSee('Select a delivery address')
        ->assertSee('No saved addresses yet')
        ->assertSee('Add New Address');
});

test('prompt lists saved addresses for a user without a default address', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id, 'address_line' => '12, MG Road']);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->assertSet('show', true)
        ->assertSee('12, MG Road')
        ->assertSee('Confirm Address')
        ->assertSee('Add New Address');
});

test('prompt is shown after login even when the user has a default address', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->assertSet('show', true)
        ->assertSee('Select a delivery address');
});

test('prompt is not shown again once completed in the same session', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id]);

    $selected = $user->addresses()->latest()->first();

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->call('confirmSelection')
        ->assertSet('show', false);

    expect(session()->get('address_prompt_completed'))->toBeTrue();

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->assertSet('show', false);
});

test('prompt prefills the receiver details from the user profile', function () {
    $user = User::factory()->create(['name' => 'Aarav Sharma', 'phone' => '9876543210']);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->assertSet('receiverName', 'Aarav Sharma')
        ->assertSet('receiverPhone', '9876543210');
});

test('user can select an existing address and confirm it', function () {
    $user = User::factory()->create();
    $other = Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);
    $selected = Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
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

test('confirming without a selection shows an error', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
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
        ->call('openAddForm')
        ->assertSet('view', 'form')
        ->assertSee('Add your delivery address')
        ->call('backToSelect')
        ->assertSet('view', 'select');
});

test('user can add their first address from the prompt', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
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
        ->set('receiverName', '')
        ->set('receiverPhone', '12345')
        ->set('pincode', '123')
        ->call('saveAddress')
        ->assertHasErrors(['receiverName', 'receiverPhone', 'addressLine', 'city', 'state', 'pincode'])
        ->assertSet('show', true);
});

test('logging in again clears the completion flag so the prompt shows again', function () {
    $user = User::factory()->create(['phone' => '9876543210']);
    Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);

    session()->put('address_prompt_completed', true);

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->call('sendOtp');

    $code = OtpCode::where('phone', '9876543210')->latest()->first()->code;

    Livewire::test(PhoneLogin::class)
        ->set('phone', '9876543210')
        ->set('otp', $code)
        ->call('verifyOtp')
        ->assertRedirect(route('home'));

    expect(session()->get('address_prompt_completed'))->toBeNull();

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->assertSet('show', true);
});

test('prompt opens from the header even after it was completed', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);
    session()->put('address_prompt_completed', true);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->assertSet('show', false)
        ->dispatch('open-address-prompt')
        ->assertSet('show', true)
        ->assertSet('view', 'select')
        ->assertSet('dismissable', true);
});

test('prompt opened from the header can be dismissed', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->dispatch('open-address-prompt')
        ->assertSet('show', true)
        ->call('dismiss')
        ->assertSet('show', false)
        ->assertSet('dismissable', false);
});

test('auto-shown prompt is not dismissable', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AddAddressPrompt::class)
        ->assertSet('show', true)
        ->assertSet('dismissable', false)
        ->assertDontSee('aria-label="Close"');
});

test('home page shows the prompt for a user with no saved addresses', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('Select a delivery address');
});

test('home page shows the prompt for a user with a default address after login', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('Select a delivery address');
});

test('home page does not show the prompt once completed in the session', function () {
    $user = User::factory()->create();
    Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);

    session()->put('address_prompt_completed', true);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertDontSee('Select a delivery address');
});
