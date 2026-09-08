<?php

use App\Livewire\Admin\BasketForm;
use App\Models\Admin;
use App\Models\Basket;
use App\Models\Product;
use App\Models\Unit;
use Livewire\Livewire;

it('edit form reflects an inactive basket status', function () {
    $admin = Admin::factory()->create();
    $basket = Basket::factory()->create(['is_active' => false]);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class, ['basket' => $basket])
        ->assertSet('is_active', '0');
});

it('edit form can hide a basket via save', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg']);
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 0]);
    $basket = Basket::factory()->create(['is_active' => true]);
    $basket->products()->attach($product);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class, ['basket' => $basket])
        ->set('is_active', '0')
        ->call('save')
        ->assertRedirect(route('admin.baskets.index'));

    expect($basket->fresh()->is_active)->toBeFalse();
});

it('edit form can re-activate a hidden basket via save', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg']);
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 0]);
    $basket = Basket::factory()->create(['is_active' => false]);
    $basket->products()->attach($product);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class, ['basket' => $basket])
        ->set('is_active', '1')
        ->call('save')
        ->assertRedirect(route('admin.baskets.index'));

    expect($basket->fresh()->is_active)->toBeTrue();
});

it('renders the correct status option as selected for a hidden basket', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Hidden Basket', 'is_active' => false]);
    $basket->products()->attach($product);

    $html = Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class, ['basket' => $basket])
        ->html();

    expect($html)->toContain('<option value="0" selected>Hidden</option>');
});

it('renders the correct status option as selected for an active basket', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create();
    $basket = Basket::factory()->create(['name' => 'Active Basket', 'is_active' => true]);
    $basket->products()->attach($product);

    $html = Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class, ['basket' => $basket])
        ->html();

    expect($html)->toContain('<option value="1" selected>Active</option>');
});
