<?php

use App\Livewire\Auth\PhoneLogin;
use Livewire\Livewire;

it('renders the login page with logo sized to the form width', function () {
    Livewire::test(PhoneLogin::class)
        ->assertOk()
        ->assertDontSee(config('app.name'))
        ->assertDontSee('Fresh fruits & vegetables, delivered.')
        ->assertSee('Login')
        ->assertSee('Back to home')
        ->assertSee(route('home'));
});
