<?php

use App\Models\Recipe;
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

it('renders home recipes as a full-width autoscroll carousel on mobile and static grid on desktop', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Recipe::factory()->count(3)->create(['is_active' => true]);

    $html = Livewire::test('home')->html();

    // Mobile carousel (lg:hidden)
    expect($html)
        ->toContain('data-auto-carousel')
        ->toContain('data-carousel-track')
        ->toContain('data-carousel-prev')
        ->toContain('data-carousel-next')
        ->toContain('data-carousel-dots')
        ->toContain('data-carousel-dot="0"')
        ->toContain('data-carousel-dot="3"')
        ->toContain('Go to view all')
        ->toContain('basis-full')
        ->toContain('lg:hidden');

    // Desktop horizontal scroll rail (hidden lg:block)
    expect($html)
        ->toContain('hidden lg:block')
        ->toContain('overflow-x-auto')
        ->toContain('snap-x')
        ->toContain('w-64 sm:w-72');
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
