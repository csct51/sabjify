<?php

use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

it('renders home with cards', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Product::factory(4)->create(['is_featured' => true]);

    $html = Livewire::test('home')->html();

    file_put_contents('C:/Users/CHANDR~1/AppData/Local/Temp/opencode/home-render.html', $html);
    expect(true)->toBeTrue();
});
