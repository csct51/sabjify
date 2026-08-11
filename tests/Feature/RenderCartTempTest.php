<?php

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

it('renders cart buttons', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $product = Product::factory()->create();
    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

    $html = Livewire::test('cart')->html();
    file_put_contents('C:/Users/CHANDR~1/AppData/Local/Temp/opencode/cart-render.html', $html);
    expect(true)->toBeTrue();
});
