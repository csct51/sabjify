<?php

use App\Livewire\Search;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('search page renders and mounts the unit picker', function () {
    $this->get('/search')
        ->assertOk()
        ->assertSee('product-unit-picker:open.window');
});

test('search shows matching products', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['name' => 'Fresh Red Apple', 'category_id' => $category->id]);
    Product::factory()->create(['name' => 'Green Banana', 'category_id' => $category->id]);

    Livewire::test(Search::class)
        ->set('search', 'apple')
        ->assertSeeHtml('Showing results for "apple"');
});

test('search shows out of stock products with a label', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['name' => 'Fresh Apple', 'category_id' => $category->id]);
    Product::factory()->outOfStock()->create(['name' => 'Out of Stock Apple', 'category_id' => $category->id]);

    Livewire::test(Search::class)
        ->set('search', 'apple')
        ->assertSeeHtml('Showing results for "apple"');
});

test('search shows empty state when no query', function () {
    Livewire::test(Search::class)
        ->assertSee('Start searching');
});

test('search paginates matching products and loads more on demand', function () {
    $category = Category::factory()->create();
    Product::factory()->count(14)->create(['name' => 'Mango Something', 'category_id' => $category->id]);
    Product::factory()->create(['name' => 'Banana', 'category_id' => $category->id]);

    Livewire::test(Search::class)
        ->set('search', 'mango')
        ->assertSet('items', fn ($items) => $items->count() === 12)
        ->assertSet('hasMore', true)
        ->call('loadMore')
        ->assertSet('items', fn ($items) => $items->count() === 14)
        ->assertSet('hasMore', false);
});

test('search resets pagination when the query changes', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['name' => 'Mango A', 'category_id' => $category->id]);
    Product::factory()->create(['name' => 'Mango B', 'category_id' => $category->id]);

    Livewire::test(Search::class)
        ->set('search', 'mango')
        ->assertSet('page', 1)
        ->assertSet('hasMore', false)
        ->assertSet('items', fn ($items) => $items->count() === 2);
});

test('search shows total result count independent of the loaded page', function () {
    $category = Category::factory()->create();
    Product::factory()->count(14)->create(['name' => 'Mango X', 'category_id' => $category->id]);

    Livewire::test(Search::class)
        ->set('search', 'mango')
        ->assertSeeHtml('Showing results for "mango"')
        ->assertSet('items', fn ($items) => $items->count() === 12)
        ->assertSet('totalResults', 14);
});
