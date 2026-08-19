<?php

use App\Livewire\StoreHeader;
use App\Models\Address;
use App\Models\User;
use Livewire\Livewire;

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
        ->assertSee('12, MG Road')
        ->assertSee('Mumbai');
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
        ->assertSee('12, MG Road')
        ->assertSee('Mumbai');
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
        ->assertSee('12, MG Road')
        ->assertSee('Mumbai');
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
