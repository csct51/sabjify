<?php

use App\Models\User;
use Livewire\Livewire;

it('renders home with product cards', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $html = Livewire::test('home')->html();

    file_put_contents('C:/Users/CHANDR~1/AppData/Local/Temp/opencode/home-render.html', $html);

    expect($html)
        ->toContain('Shop by Category')
        ->toContain('font-heading')
        ->toContain('font-extrabold');
});

it('renders home with the six info cards', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $html = Livewire::test('home')->html();

    expect($html)
        ->toContain('On-time delivery')
        ->toContain('Freshly packed')
        ->toContain('Secure payment')
        ->toContain('Easy order tracking')
        ->toContain('Weekly basket')
        ->toContain('Health basket')
        ->not->toContain('Easy Returns')
        ->not->toContain('Best Prices')
        ->not->toContain('24x7 Support');
});
