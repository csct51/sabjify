<?php

use App\Livewire\Admin\BasketForm;
use App\Livewire\BasketCard;
use App\Livewire\BasketShow;
use App\Models\Basket;
use App\Models\Product;
use Livewire\Livewire;

test('basket discount percent is computed from mrp and price', function () {
    expect(Basket::factory()->make(['price' => 100, 'mrp' => null])->discountPercent())->toBe(0);
    expect(Basket::factory()->make(['price' => 100, 'mrp' => 80])->discountPercent())->toBe(0);
    expect(Basket::factory()->make(['price' => 100, 'mrp' => 125])->discountPercent())->toBe(20);
});

test('admin basket form persists mrp', function () {
    $product = Product::factory()->create();

    Livewire::test(BasketForm::class)
        ->set('name', 'MRP Test Basket')
        ->set('slug', 'mrp-test-basket')
        ->set('type', Basket::TYPE_SABJIFY)
        ->set('price', 299)
        ->set('mrp', 399)
        ->set('productIds', [$product->id])
        ->call('save')
        ->assertHasNoErrors();

    $basket = Basket::where('slug', 'mrp-test-basket')->first();

    expect($basket)->not->toBeNull()
        ->and($basket->mrp)->toBe(399)
        ->and($basket->price)->toBe(299);
});

test('empty mrp is stored as null through the admin form', function () {
    $product = Product::factory()->create();

    Livewire::test(BasketForm::class)
        ->set('name', 'No Mrp Basket')
        ->set('slug', 'no-mrp-basket')
        ->set('type', Basket::TYPE_SABJIFY)
        ->set('price', 299)
        ->set('mrp', '')
        ->set('productIds', [$product->id])
        ->call('save')
        ->assertHasNoErrors();

    expect(Basket::where('slug', 'no-mrp-basket')->first()->mrp)->toBeNull();
});

test('basket show page displays mrp when discounted', function () {
    $basket = Basket::factory()->create([
        'name' => 'Discounted Basket',
        'slug' => 'discounted-basket',
        'price' => 299,
        'mrp' => 399,
        'is_active' => true,
    ]);

    Livewire::test(BasketShow::class, ['basket' => $basket])
        ->assertSee(Number::currency(399, 'INR'));
});

test('basket card shows the discount badge and mrp', function () {
    $basket = Basket::factory()->create([
        'name' => 'Discounted Card',
        'slug' => 'discounted-card',
        'price' => 299,
        'mrp' => 399,
        'is_active' => true,
    ]);

    Livewire::test(BasketCard::class, ['basket' => $basket])
        ->assertSee('25% OFF')
        ->assertSee(Number::currency(399, 'INR'));
});

test('basket show page hides the discount when there is no mrp', function () {
    $basket = Basket::factory()->create([
        'price' => 299,
        'mrp' => null,
        'is_active' => true,
    ]);

    Livewire::test(BasketShow::class, ['basket' => $basket])
        ->assertDontSee('% OFF');
});
