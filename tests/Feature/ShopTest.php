<?php

use App\Livewire\Shop;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('shop page renders the filter button and modal toggle', function () {
    Category::factory()->create(['name' => 'Fruits']);

    Livewire::test(Shop::class)
        ->assertSee('Filters')
        ->assertDontSeeHtml('role="dialog"')
        ->call('$set', 'showFilters', true)
        ->assertSeeHtml('role="dialog"')
        ->assertSeeHtml('aria-modal="true"')
        ->assertSee('Fruits');
});

test('shop page filters products by pill category selection', function () {
    $fruits = Category::factory()->create(['name' => 'Fruits']);
    $veg = Category::factory()->create(['name' => 'Vegetables']);

    Product::factory()->create(['name' => 'Apple', 'category_id' => $fruits->id]);
    Product::factory()->create(['name' => 'Carrot', 'category_id' => $veg->id]);

    Livewire::test(Shop::class)
        ->set('category', $fruits->slug)
        ->assertSet('category', $fruits->slug)
        ->assertSee('Fruits')
        ->assertSee('1 items available');
});

test('shop page clears filters from the modal', function () {
    $fruits = Category::factory()->create(['name' => 'Fruits']);
    $veg = Category::factory()->create(['name' => 'Vegetables']);

    Product::factory()->create(['name' => 'Apple', 'category_id' => $fruits->id]);
    Product::factory()->create(['name' => 'Carrot', 'category_id' => $veg->id]);

    Livewire::test(Shop::class)
        ->set('category', $fruits->slug)
        ->call('clearFilters')
        ->assertSet('category', null)
        ->assertSee('All Products')
        ->assertSee('2 items available');
});

test('categories index page shows all active categories', function () {
    Category::factory()->create(['name' => 'Fruits']);
    $hidden = Category::factory()->create(['name' => 'Hidden Category', 'is_active' => false]);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Fruits')
        ->assertDontSee('Hidden Category');
});
