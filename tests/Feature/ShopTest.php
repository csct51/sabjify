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

test('shop page filters products by search query', function () {
    Product::factory()->create(['name' => 'Apple']);
    Product::factory()->create(['name' => 'Banana']);

    Livewire::test(Shop::class)
        ->assertSee('2 items available')
        ->set('search', 'Apple')
        ->assertSee('1 items available');
});

test('desktop filter controls use live binding', function () {
    Livewire::test(Shop::class)
        ->assertSee('wire:model.live')
        ->assertSee('debounce.300ms');
});

test('categories index page shows all active categories', function () {
    Category::factory()->create(['name' => 'Fruits']);
    $hidden = Category::factory()->create(['name' => 'Hidden Category', 'is_active' => false]);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Fruits')
        ->assertDontSee('Hidden Category');
});

test('shop paginates products and loads more on demand', function () {
    Category::factory()->create();
    Product::factory()->count(14)->create();

    Livewire::test(Shop::class)
        ->assertSet('items', fn ($items) => $items->count() === 12)
        ->assertSet('hasMore', true)
        ->call('loadMore')
        ->assertSet('items', fn ($items) => $items->count() === 14)
        ->assertSet('hasMore', false)
        ->assertSet('page', 2);
});

test('shop resets pagination when a filter is applied', function () {
    $category = Category::factory()->create(['name' => 'Fruits']);
    Product::factory()->count(14)->create();
    Product::factory()->create(['name' => 'Apple', 'category_id' => $category->id]);

    Livewire::test(Shop::class)
        ->set('category', $category->slug)
        ->assertSet('page', 1)
        ->assertSet('items', fn ($items) => $items->count() === 1)
        ->assertSet('hasMore', false);
});

test('shop has no load more button and shows an end of list line', function () {
    Category::factory()->create();
    Product::factory()->count(14)->create();

    Livewire::test(Shop::class)
        ->assertDontSee('Load more')
        ->assertSee('data-infinite-sentinel', false)
        ->call('loadMore')
        ->assertDontSee('Load more')
        ->assertSee('Showing all 14 items');
});

test('shop grid never hides behind the reveal system', function () {
    Category::factory()->create();
    Product::factory()->count(3)->create();

    Livewire::test(Shop::class)->assertDontSee('data-reveal');
});

test('shop repeated loads stay unique', function () {
    Category::factory()->create();
    Product::factory()->count(30)->create();

    Livewire::test(Shop::class)
        ->call('loadMore')
        ->call('loadMore')
        ->assertSet('items', fn ($items) => $items->count() === 30
            && $items->pluck('id')->unique()->count() === 30)
        ->assertSet('hasMore', false);
});
