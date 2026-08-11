<?php

use App\Livewire\Profile\Account;
use App\Models\User;
use Livewire\Livewire;

test('guest is redirected to login when visiting the account details page', function () {
    $this->get(route('profile.account'))->assertRedirect(route('login'));
});

test('authenticated user can view their account details', function () {
    $user = User::factory()->create(['name' => 'Aarav Sharma']);

    Livewire::actingAs($user)
        ->test(Account::class)
        ->assertOk()
        ->assertSet('name', 'Aarav Sharma');
});

test('user can update their name and email', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Account::class)
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
        ->test(Account::class)
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
        ->test(Account::class)
        ->set('name', 'New Name')
        ->set('email', 'taken@example.com')
        ->call('saveProfile')
        ->assertHasErrors('email');
});

test('user can keep their own email', function () {
    $user = User::factory()->create(['email' => 'me@example.com']);

    Livewire::actingAs($user)
        ->test(Account::class)
        ->set('name', 'New Name')
        ->set('email', 'me@example.com')
        ->call('saveProfile')
        ->assertHasNoErrors();
});
