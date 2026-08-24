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

test('admin basket form stores custom unit and price for a product', function () {
    $product = Product::factory()->create();

    Livewire::test(BasketForm::class)
        ->set('name', 'Custom Basket')
        ->set('slug', 'custom-basket')
        ->set('type', Basket::TYPE_SABJIFY)
        ->set('price', 199)
        ->set('productIds', [$product->id])
        ->set('productCustomUnits.'.$product->id, '2 pcs')
        ->set('productCustomPrices.'.$product->id, 199)
        ->call('save')
        ->assertHasNoErrors();

    $basket = Basket::where('slug', 'custom-basket')->first();
    $pivot = $basket->products()->withPivot('unit', 'price')->first()->pivot;

    expect($pivot->unit)->toBe('2 pcs')
        ->and($pivot->price)->toBe(199);
});

test('custom unit and price are displayed on the basket show page', function () {
    $product = Product::factory()->create();
    $basket = Basket::factory()->create([
        'name' => 'Custom Display',
        'slug' => 'custom-display',
        'is_active' => true,
    ]);
    $basket->products()->attach($product->id, [
        'unit' => '2 pcs',
        'price' => 199,
        'product_unit_id' => null,
    ]);

    Livewire::test(BasketShow::class, ['basket' => $basket])
        ->assertSee('2 pcs')
        ->assertSee(Number::currency(199, 'INR'));
});

test('custom price feeds the calculated basket total', function () {
    $product = Product::factory()->create(['price' => 100]);

    $component = Livewire::test(BasketForm::class)
        ->set('name', 'Calc Basket')
        ->set('slug', 'calc-basket')
        ->set('type', Basket::TYPE_SABJIFY)
        ->set('productIds', [$product->id])
        ->set('productCustomPrices.'.$product->id, 199);

    expect($component->calculatedPrice)->toBe(199);
});

test('admin form pre-populates custom unit and price when editing', function () {
    $product = Product::factory()->create();
    $basket = Basket::factory()->create([
        'name' => 'Edit Basket',
        'slug' => 'edit-basket',
        'type' => Basket::TYPE_SABJIFY,
    ]);
    $basket->products()->attach($product->id, [
        'unit' => '3 pcs',
        'price' => 150,
        'product_unit_id' => null,
    ]);

    $component = Livewire::test(BasketForm::class, ['basket' => $basket]);

    expect($component->get('productCustomUnits')[$product->id])->toBe('3 pcs')
        ->and($component->get('productCustomPrices')[$product->id])->toBe(150);
});
