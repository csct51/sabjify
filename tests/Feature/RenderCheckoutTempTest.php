<?php

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

it('renders checkout button', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $product = Product::factory()->create();
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

    $html = Livewire::test('checkout')->html();

    $i = strpos($html, 'placeOrder');
    $snippet = $i !== false ? substr($html, max(0, $i - 200), 900) : 'NOT FOUND';

    file_put_contents('C:/Users/CHANDR~1/AppData/Local/Temp/opencode/checkout-btn.txt', $snippet);
    expect(true)->toBeTrue();
});
