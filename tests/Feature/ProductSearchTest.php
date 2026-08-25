<?php

use App\Livewire\Admin\ProductForm;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('alternate names cast normalizes comma and space separated input', function () {
    $product = Product::factory()->create([
        'name' => 'Tomato',
        'alternate_names' => 'tamatar, टमाटर, tamatar',
    ]);

    expect($product->alternate_names)->toBeArray()
        ->and($product->alternate_names)->toContain('tamatar')
        ->and($product->alternate_names)->toContain('टमाटर')
        ->and(count($product->alternate_names))->toBe(2);

    $spaceSeparated = Product::factory()->create([
        'name' => 'Potato',
        'alternate_names' => 'aloo batata',
    ]);

    expect($spaceSeparated->alternate_names)->toBe(['aloo batata']);

    $multiWord = Product::factory()->create([
        'name' => 'Tomato',
        'alternate_names' => 'rock salt, tamatar',
    ]);

    expect($multiWord->alternate_names)->toContain('rock salt')
        ->and($multiWord->alternate_names)->toContain('tamatar');
});

test('shop search matches product by alternate name', function () {
    $product = Product::factory()->available()
        ->withAlternateNames(['tamatar', 'टमाटर'])
        ->create(['name' => 'Tomato']);

    $this->get('/shop?q=tamatar')->assertOk()->assertSee($product->name);
    $this->get('/shop?q=टमाटर')->assertOk()->assertSee($product->name);
    $this->get('/shop?q=zzznotarealterm')->assertOk()->assertDontSee($product->name);
});

test('store search matches product by alternate name', function () {
    $product = Product::factory()->available()
        ->withAlternateNames(['tamatar', 'टमाटर'])
        ->create(['name' => 'Tomato']);

    $this->get('/search?q=tamatar')->assertOk()->assertSee($product->name);
    $this->get('/search?q=zzznotarealterm')->assertOk()->assertDontSee($product->name);
});

test('admin product form saves alternate names', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(ProductForm::class)
        ->set('categoryId', $category->id)
        ->set('name', 'Tomato')
        ->set('slug', 'tomato-alt')
        ->set('description', 'Fresh red tomato')
        ->set('alternateNames', 'tamatar, टमाटर')
        ->set('unitRows', [['unit' => '1 kg', 'price' => '50', 'mrp' => null, 'in_stock' => true]])
        ->set('sort_order', 0)
        ->call('save')
        ->assertHasNoErrors();

    $saved = Product::where('slug', 'tomato-alt')->first();

    expect($saved)->not->toBeNull()
        ->and($saved->alternate_names)->toContain('tamatar')
        ->and($saved->alternate_names)->toContain('टमाटर');
});

test('search shows suggestions and refines on click', function () {
    $product = Product::factory()->available()
        ->withAlternateNames(['tamatar'])
        ->create(['name' => 'Tomato']);

    $test = Livewire::test(Search::class)->set('search', 'tamatar');

    expect($test->instance()->suggestions->contains('name', 'Tomato'))->toBeTrue();

    $test->call('selectSuggestion', $product->id)
        ->assertSet('search', 'Tomato')
        ->assertSet('showSuggestions', false)
        ->assertDontSee('data-suggestions');

    $this->get('/search?q=tamatar')
        ->assertOk()
        ->assertSee('data-suggestions')
        ->assertSee($product->name);
});

test('search returns results for a misspelled keyword', function () {
    $product = Product::factory()->available()->create(['name' => 'Tomato']);

    $this->get('/search?q=tomatoe')
        ->assertOk()
        ->assertSee($product->name);
});

test('shop search returns results for a misspelled keyword via alternate name', function () {
    $product = Product::factory()->available()
        ->withAlternateNames(['tamatar'])
        ->create(['name' => 'Tomato Local']);

    $this->get('/shop?q=tamato')
        ->assertOk()
        ->assertSee($product->name);
});

test('shop browse still lists products with an empty search', function () {
    Product::factory()->available()->count(3)->create();

    $this->get('/shop')
        ->assertOk();
});
