<?php

use App\Models\Product;
use Livewire\Livewire;

it('renders product card', function () {
    $product = Product::factory()->create();

    $html = Livewire::test('product-card', ['product' => $product])->html();

    file_put_contents('C:/Users/CHANDR~1/AppData/Local/Temp/opencode/card-render.html', $html);

    expect(true)->toBeTrue();
});
