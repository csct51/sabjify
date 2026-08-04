<?php

use App\Livewire\Profile;
use App\Models\Address;
use App\Models\User;
use Livewire\Livewire;

test('guest is redirected to login when visiting the profile page', function () {
    $this->get(route('profile'))->assertRedirect(route('login'));
});

test('authenticated user can view their profile', function () {
    $user = User::factory()->create(['name' => 'Aarav Sharma']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertOk()
        ->assertSet('name', 'Aarav Sharma');
});

test('user can update their name and email', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'New Name')
        ->set('email', 'new@example.com')
        ->call('saveProfile')
        ->assertHasNoErrors();

    expect($user->fresh())
        ->name->toBe('New Name')
        ->email->toBe('new@example.com');
});

test('user can clear their email', function () {
    $user = User::factory()->create(['email' => 'old@example.com']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'New Name')
        ->set('email', '')
        ->call('saveProfile')
        ->assertHasNoErrors();

    expect($user->fresh()->email)->toBeNull();
});

test('email must be unique among other users', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'New Name')
        ->set('email', 'taken@example.com')
        ->call('saveProfile')
        ->assertHasErrors('email');
});

test('user can keep their own email', function () {
    $user = User::factory()->create(['email' => 'me@example.com']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'New Name')
        ->set('email', 'me@example.com')
        ->call('saveProfile')
        ->assertHasNoErrors();
});

test('user can add a saved address', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->call('openAddressForm')
        ->set('label', 'Home')
        ->set('receiverName', 'Aarav Sharma')
        ->set('receiverPhone', '9876543210')
        ->set('addressLine', '12, MG Road')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '400001')
        ->set('isDefault', true)
        ->call('saveAddress')
        ->assertHasNoErrors()
        ->assertSet('addressMode', 'list');

    $this->assertDatabaseHas('addresses', [
        'user_id' => $user->id,
        'label' => 'Home',
        'address_line' => '12, MG Road',
        'is_default' => true,
    ]);
});

test('address requires a valid Indian phone and pincode', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->call('openAddressForm')
        ->set('label', 'Home')
        ->set('receiverName', 'Aarav Sharma')
        ->set('receiverPhone', '12345')
        ->set('addressLine', '12, MG Road')
        ->set('city', 'Mumbai')
        ->set('state', 'Maharashtra')
        ->set('pincode', '123')
        ->call('saveAddress')
        ->assertHasErrors(['receiverPhone', 'pincode']);
});

test('user can edit an address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->call('editAddress', $address)
        ->assertSet('editingAddressId', $address->id)
        ->set('addressLine', '88, Linking Road')
        ->set('city', 'Pune')
        ->call('saveAddress')
        ->assertHasNoErrors()
        ->assertSet('editingAddressId', null);

    expect($address->fresh())
        ->address_line->toBe('88, Linking Road')
        ->city->toBe('Pune');
});

test('user can delete an address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->call('deleteAddress', $address)
        ->assertOk();

    expect(Address::find($address->id))->toBeNull();
});

test('user can set an address as default', function () {
    $user = User::factory()->create();
    $first = Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);
    $second = Address::factory()->create(['user_id' => $user->id, 'is_default' => false]);

    Livewire::actingAs($user)
        ->test(Profile::class)
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
        ->test(Profile::class)
        ->call('deleteAddress', $default)
        ->assertOk();

    expect($other->fresh()->is_default)->toBeTrue();
});

test('user cannot manage another user addresses', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $owner->id]);

    Livewire::actingAs($other)
        ->test(Profile::class)
        ->call('editAddress', $address)
        ->assertForbidden();

    Livewire::actingAs($other)
        ->test(Profile::class)
        ->call('deleteAddress', $address)
        ->assertForbidden();

    expect(Address::find($address->id))->not->toBeNull();
});
