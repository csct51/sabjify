<?php

use App\Livewire\Profile;
use App\Models\Address;
use App\Models\User;
use Livewire\Livewire;

test('guest is redirected to login when visiting the profile page', function () {
    $this->get(route('profile'))->assertRedirect(route('login'));
});

test('authenticated user can view their profile hub', function () {
    $user = User::factory()->create(['name' => 'Aarav Sharma']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertOk()
        ->assertSee('Aarav Sharma')
        ->assertSee('Account Details')
        ->assertSee('Saved Addresses')
        ->assertSee('My Orders')
        ->assertSee('Logout');
});

test('profile hub shows order and address counts', function () {
    $user = User::factory()->create();
    Address::factory()->count(2)->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertSet('ordersCount', 0)
        ->assertSet('addressesCount', 2);
});

test('user can log out from the profile page', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->call('logout')
        ->assertRedirect(route('login'));

    expect(auth('web')->check())->toBeFalse();
});
