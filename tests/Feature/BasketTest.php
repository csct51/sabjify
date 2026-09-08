<?php

use App\Livewire\Admin\BasketForm;
use App\Livewire\BasketCard;
use App\Livewire\BasketShow;
use App\Models\Admin;
use App\Models\Basket;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Recipe;
use App\Models\Unit;
use App\Models\User;
use App\Services\OrderService;
use Livewire\Livewire;

test('basket discount percent is computed from mrp and price', function () {
    expect(Basket::factory()->make(['price' => 100, 'mrp' => null])->discountPercent())->toBe(0);
    expect(Basket::factory()->make(['price' => 100, 'mrp' => 80])->discountPercent())->toBe(0);
    expect(Basket::factory()->make(['price' => 100, 'mrp' => 125])->discountPercent())->toBe(20);
});

test('basket form pre-selects the first unit for newly checked products', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg']);
    $product->units()->delete();
    $first = ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '1 kg', 'sort_order' => 0]);
    ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '500 g', 'sort_order' => 1]);

    $component = Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('productIds', [$product->id])
        ->assertDontSee('Default unit');

    expect($component->get('productUnitIds')[$product->id])->toBe($first->id);
});

test('basket form maps legacy null pivot to the first unit on edit', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg']);
    $product->units()->delete();
    $first = ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '1 kg', 'sort_order' => 0]);
    $basket = Basket::factory()->create(['is_active' => true]);
    $basket->products()->attach($product->id, ['unit' => '1 kg', 'price' => 100, 'product_unit_id' => null]);

    $component = Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class, ['basket' => $basket]);

    expect($component->get('productUnitIds')[$product->id])->toBe($first->id);
});

test('basket form saves unit-less products via legacy fallback', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg', 'price' => 100]);
    $product->units()->delete();

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Legacy Basket')
        ->set('slug', 'legacy-basket')
        ->set('type', Basket::TYPE_SABJIFY)
        ->set('price', 100)
        ->set('productIds', [$product->id])
        ->call('save')
        ->assertHasNoErrors();

    $basket = Basket::where('slug', 'legacy-basket')->first();

    expect($basket->products()->first()->pivot->unit)->toBe('1 kg');
});

test('basket form leaves custom fields blank when pivot matches resolved unit', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg', 'price' => 100]);
    $product->units()->delete();
    $unit = ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '1 kg', 'price' => 100, 'sort_order' => 0]);
    $basket = Basket::factory()->create(['is_active' => true]);
    $basket->products()->attach($product->id, ['unit' => '1 kg', 'price' => 100, 'product_unit_id' => $unit->id]);

    $component = Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class, ['basket' => $basket]);

    expect($component->get('productCustomQtys')[$product->id])->toBeNull()
        ->and($component->get('productCustomPrices')[$product->id])->toBeNull();
});

test('basket form keeps genuinely custom values on edit and round-trip', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 pc', 'price' => 50, 'base_unit' => 'piece']);
    $product->units()->delete();
    ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '1 pc', 'price' => 50, 'sort_order' => 0]);
    $basket = Basket::factory()->create(['is_active' => true]);
    $basket->products()->attach($product->id, ['unit' => '3 piece', 'price' => 150, 'product_unit_id' => null]);

    $component = Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class, ['basket' => $basket]);

    expect($component->get('productCustomQtys')[$product->id])->toBe('3')
        ->and($component->get('productCustomPrices')[$product->id])->toBe(150);

    $component->set('name', 'Round Trip Basket')->call('save')->assertHasNoErrors();

    $fresh = $basket->fresh()->products()->first();

    expect($fresh->pivot->unit)->toBe('3 piece')
        ->and((int) $fresh->pivot->price)->toBe(150);
});

test('basket sale decrements constituent stocks exactly', function () {
    $user = User::factory()->create();
    $apple = Product::factory()->create(['unit' => '1 kg', 'base_unit' => 'g', 'price' => 100]);
    $apple->units()->delete();
    $appleUnit = ProductUnit::factory()->create(['product_id' => $apple->id, 'unit' => '1 kg', 'price' => 100, 'sort_order' => 0]);
    $apple->update(['current_stock' => 5000]);
    $coco = Product::factory()->create(['unit' => '1 pc', 'base_unit' => 'piece', 'price' => 50]);
    $coco->units()->delete();
    $cocoUnit = ProductUnit::factory()->create(['product_id' => $coco->id, 'unit' => '1 pc', 'price' => 50, 'sort_order' => 0]);
    $coco->update(['current_stock' => 10]);

    $basket = Basket::factory()->create(['is_active' => true, 'price' => 140]);
    $basket->products()->attach($apple->id, ['unit' => '1 kg', 'price' => 100, 'product_unit_id' => $appleUnit->id]);
    $basket->products()->attach($coco->id, ['unit' => '1 pc', 'price' => 50, 'product_unit_id' => $cocoUnit->id]);

    expect($basket->basketsSellable())->toBe(5);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => null, 'basket_id' => $basket->id, 'quantity' => 2]);

    $order = app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Rahul Sharma',
        'receiver_phone' => '9876501234',
        'address_line' => '12 Main Street',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '123456',
    ]);

    expect((float) $apple->fresh()->current_stock)->toBe(3000.0)
        ->and((float) $coco->fresh()->current_stock)->toBe(8.0);

    app(OrderService::class)->cancel($order, 'test', 'customer');

    expect((float) $apple->fresh()->current_stock)->toBe(5000.0)
        ->and((float) $coco->fresh()->current_stock)->toBe(10.0);
});

test('basket sale is blocked when a constituent is short', function () {
    $user = User::factory()->create();
    $apple = Product::factory()->create(['unit' => '1 kg', 'base_unit' => 'g', 'price' => 100]);
    $apple->units()->delete();
    $appleUnit = ProductUnit::factory()->create(['product_id' => $apple->id, 'unit' => '1 kg', 'price' => 100, 'sort_order' => 0]);
    $apple->update(['current_stock' => 500]);

    $basket = Basket::factory()->create(['is_active' => true, 'price' => 140]);
    $basket->products()->attach($apple->id, ['unit' => '1 kg', 'price' => 100, 'product_unit_id' => $appleUnit->id]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => null, 'basket_id' => $basket->id, 'quantity' => 1]);

    expect(fn () => app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Rahul Sharma',
        'receiver_phone' => '9876501234',
        'address_line' => '12 Main Street',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '123456',
    ]))->toThrow(RuntimeException::class, 'Not enough stock for basket');

    Livewire::actingAs($user)
        ->test(BasketShow::class, ['basket' => $basket])
        ->call('addToCart');

    expect($user->cartItems()->count())->toBe(1);
});

test('basket custom numeric qty deducts exact base', function () {
    $user = User::factory()->create();
    $apple = Product::factory()->create(['unit' => '1 kg', 'base_unit' => 'g', 'price' => 100]);
    $apple->update(['current_stock' => 1000]);

    $basket = Basket::factory()->create(['is_active' => true, 'price' => 60]);
    $basket->products()->attach($apple->id, ['unit' => '0.5 kg', 'price' => 60, 'product_unit_id' => null]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => null, 'basket_id' => $basket->id, 'quantity' => 1]);

    app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Rahul Sharma',
        'receiver_phone' => '9876501234',
        'address_line' => '12 Main Street',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '123456',
    ]);

    expect((float) $apple->fresh()->current_stock)->toBe(500.0);
});

test('basket show and card show out of stock when constituents run out', function () {
    $apple = Product::factory()->create(['unit' => '1 kg', 'base_unit' => 'g', 'price' => 100]);
    $apple->units()->delete();
    $appleUnit = ProductUnit::factory()->create(['product_id' => $apple->id, 'unit' => '1 kg', 'price' => 100, 'sort_order' => 0]);
    $apple->update(['current_stock' => 0]);

    $basket = Basket::factory()->create(['is_active' => true, 'price' => 140]);
    $basket->products()->attach($apple->id, ['unit' => '1 kg', 'price' => 100, 'product_unit_id' => $appleUnit->id]);

    Livewire::test(BasketShow::class, ['basket' => $basket])
        ->assertSee('Out of Stock')
        ->assertDontSee('Only 0 baskets');

    Livewire::test(BasketCard::class, ['basket' => $basket])
        ->assertSee('Out of Stock')
        ->assertDontSee('Only 0 baskets');
});

test('admin basket form allows zero custom price for free items', function () {
    $admin = Admin::factory()->create();
    Unit::create(['name' => '1 pc', 'base_unit' => 'piece', 'to_base_factor' => 1, 'sort_order' => 0]);
    $product = Product::factory()->create(['unit' => '1 pc', 'base_unit' => 'piece']);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Freebie Basket')
        ->set('slug', 'freebie-basket')
        ->set('type', Basket::TYPE_SABJIFY)
        ->set('price', 199)
        ->set('productIds', [$product->id])
        ->set('productCustomPrices.'.$product->id, 0)
        ->call('save')
        ->assertHasNoErrors();

    $pivot = Basket::where('slug', 'freebie-basket')->first()->products()->withPivot('price')->first()->pivot;

    expect((int) $pivot->price)->toBe(0);
});

test('basket show page labels zero-priced constituents as free', function () {
    $product = Product::factory()->create(['unit' => '1 pc']);
    $basket = Basket::factory()->create([
        'name' => 'Free Display',
        'slug' => 'free-display',
        'is_active' => true,
    ]);
    $basket->products()->attach($product->id, [
        'unit' => '1 pc',
        'price' => 0,
        'product_unit_id' => null,
    ]);

    Livewire::test(BasketShow::class, ['basket' => $basket])
        ->assertSee('FREE', false)
        ->assertDontSee('₹0.00');
});

test('sub-one unit names display in base sub-units', function () {
    expect(Unit::displayUnitFor('0.75 kg'))->toBe('750 g')
        ->and(Unit::displayUnitFor('0.5 litre'))->toBe('500 ml')
        ->and(Unit::displayUnitFor('1 kg'))->toBe('1 kg')
        ->and(Unit::displayUnitFor('1.5 kg'))->toBe('1.5 kg')
        ->and(Unit::displayUnitFor('2 kg'))->toBe('2 kg')
        ->and(Unit::displayUnitFor('1 pc'))->toBe('1 pc')
        ->and(Unit::displayUnitFor('2 pcs'))->toBe('2 pcs')
        ->and(Unit::displayUnitFor('Free text'))->toBe('Free text');
});

test('basket show page renders sub-one custom unit in grams', function () {
    $product = Product::factory()->create(['unit' => '1 kg', 'base_unit' => 'g']);
    $basket = Basket::factory()->create([
        'name' => 'Gram Display',
        'slug' => 'gram-display',
        'is_active' => true,
    ]);
    $basket->products()->attach($product->id, [
        'unit' => '0.75 kg',
        'price' => 80,
        'product_unit_id' => null,
    ]);

    Livewire::test(BasketShow::class, ['basket' => $basket])
        ->assertSee('750 g')
        ->assertDontSee('0.75 kg');
});

test('admin basket form attaches and detaches recipes', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg', 'price' => 100]);
    $product->units()->delete();
    ProductUnit::factory()->create(['product_id' => $product->id, 'unit' => '1 kg', 'price' => 100, 'sort_order' => 0]);
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 0]);
    $first = Recipe::factory()->create(['title' => 'Linked Soup', 'is_active' => true]);
    $second = Recipe::factory()->create(['title' => 'Second Salad', 'is_active' => true]);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Recipe Basket')
        ->set('slug', 'recipe-basket')
        ->set('type', Basket::TYPE_SABJIFY)
        ->set('price', 299)
        ->set('productIds', [$product->id])
        ->set('recipeIds', [$first->id, $second->id])
        ->call('save')
        ->assertHasNoErrors();

    $basket = Basket::where('slug', 'recipe-basket')->first();

    expect($basket->recipes()->pluck('recipes.id')->all())->toEqualCanonicalizing([$first->id, $second->id]);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class, ['basket' => $basket])
        ->assertSet('recipeIds', [$first->id, $second->id])
        ->call('removeRecipe', $second->id)
        ->set('name', 'Recipe Basket')
        ->call('save')
        ->assertHasNoErrors();

    expect($basket->fresh()->recipes()->pluck('recipes.id')->all())->toBe([$first->id]);
});

test('basket show page lists active recipes and hides inactive ones', function () {
    $active = Recipe::factory()->create(['title' => 'Active Soup', 'is_active' => true]);
    $hidden = Recipe::factory()->create(['title' => 'Hidden Stew', 'is_active' => false]);
    $basket = Basket::factory()->create(['is_active' => true]);
    $basket->recipes()->attach([$active->id, $hidden->id]);

    Livewire::test(BasketShow::class, ['basket' => $basket])
        ->assertSee('What you can make')
        ->assertSee('Active Soup')
        ->assertDontSee('Hidden Stew');
});

test('basket show page shows the six info cards', function () {
    $basket = Basket::factory()->create(['is_active' => true]);

    Livewire::test(BasketShow::class, ['basket' => $basket])
        ->assertSee('On-time delivery')
        ->assertSee('Freshly packed')
        ->assertSee('Secure payment')
        ->assertSee('Easy order tracking')
        ->assertSee('Weekly basket')
        ->assertSee('Health basket')
        ->assertDontSee('Easy returns')
        ->assertDontSee('Quality check');
});

test('admin basket form persists mrp', function () {
    $product = Product::factory()->create(['unit' => '1 kg']);
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 0]);

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
    $product = Product::factory()->create(['unit' => '1 kg']);
    Unit::create(['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 0]);

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

test('admin basket form stores custom qty and price for a product', function () {
    $product = Product::factory()->create(['unit' => '1 kg', 'base_unit' => 'g']);

    Livewire::test(BasketForm::class)
        ->set('name', 'Custom Basket')
        ->set('slug', 'custom-basket')
        ->set('type', Basket::TYPE_SABJIFY)
        ->set('price', 199)
        ->set('productIds', [$product->id])
        ->set('productCustomQtys.'.$product->id, '0.5')
        ->set('productCustomPrices.'.$product->id, 199)
        ->call('save')
        ->assertHasNoErrors();

    $basket = Basket::where('slug', 'custom-basket')->first();
    $pivot = $basket->products()->withPivot('unit', 'price')->first()->pivot;

    expect($pivot->unit)->toBe('0.5 kg')
        ->and($pivot->price)->toBe(199);
});

test('decimal custom qty deducts exact base on sale', function () {
    $user = User::factory()->create();
    $apple = Product::factory()->create(['unit' => '1 kg', 'base_unit' => 'g', 'price' => 100]);
    $apple->update(['current_stock' => 1000]);

    $basket = Basket::factory()->create(['is_active' => true, 'price' => 60]);
    $basket->products()->attach($apple->id, ['unit' => '0.5 kg', 'price' => 60, 'product_unit_id' => null]);

    CartItem::factory()->create(['user_id' => $user->id, 'product_id' => null, 'basket_id' => $basket->id, 'quantity' => 1]);

    app(OrderService::class)->createFromCart($user, [
        'payment_method' => 'cod',
        'receiver_name' => 'Rahul Sharma',
        'receiver_phone' => '9876501234',
        'address_line' => '12 Main Street',
        'city' => 'City',
        'state' => 'State',
        'pincode' => '123456',
    ]);

    expect((float) $apple->fresh()->current_stock)->toBe(500.0);
});

test('whole custom qty stays in purchase units', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg', 'base_unit' => 'g']);

    Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class)
        ->set('name', 'Whole Basket')
        ->set('slug', 'whole-basket')
        ->set('type', Basket::TYPE_SABJIFY)
        ->set('price', 199)
        ->set('productIds', [$product->id])
        ->set('productCustomQtys.'.$product->id, '2')
        ->call('save')
        ->assertHasNoErrors();

    $pivot = Basket::where('slug', 'whole-basket')->first()->products()->withPivot('unit')->first()->pivot;

    expect($pivot->unit)->toBe('2 kg');
});

test('basket form parses composed purchase unit back to custom qty', function () {
    $admin = Admin::factory()->create();
    $product = Product::factory()->create(['unit' => '1 kg', 'base_unit' => 'g', 'price' => 100]);
    $basket = Basket::factory()->create(['is_active' => true]);
    $basket->products()->attach($product->id, ['unit' => '0.5 kg', 'price' => 60, 'product_unit_id' => null]);

    $component = Livewire::actingAs($admin, 'admin')
        ->test(BasketForm::class, ['basket' => $basket]);

    expect($component->get('productCustomQtys')[$product->id])->toBe('0.5');
});

test('admin basket form rejects fractional custom qty for piece products', function () {
    $product = Product::factory()->create(['unit' => '1 pc', 'base_unit' => 'piece']);

    Livewire::test(BasketForm::class)
        ->set('name', 'Fraction Basket')
        ->set('slug', 'fraction-basket')
        ->set('type', Basket::TYPE_SABJIFY)
        ->set('price', 199)
        ->set('productIds', [$product->id])
        ->set('productCustomQtys.'.$product->id, '1.5')
        ->call('save')
        ->assertHasErrors('productCustomQtys.'.$product->id);

    expect(Basket::where('slug', 'fraction-basket')->exists())->toBeFalse();
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

test('admin form pre-populates custom qty and price when editing', function () {
    $product = Product::factory()->create(['unit' => '1 pc', 'base_unit' => 'piece']);
    $basket = Basket::factory()->create([
        'name' => 'Edit Basket',
        'slug' => 'edit-basket',
        'type' => Basket::TYPE_SABJIFY,
    ]);
    $basket->products()->attach($product->id, [
        'unit' => '3 piece',
        'price' => 150,
        'product_unit_id' => null,
    ]);

    $component = Livewire::test(BasketForm::class, ['basket' => $basket]);

    expect($component->get('productCustomQtys')[$product->id])->toBe('3')
        ->and($component->get('productCustomPrices')[$product->id])->toBe(150);
});
