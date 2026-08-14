<?php

use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Units;
use App\Models\Admin;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('guest is redirected to admin login when accessing settings', function () {
    $this->get('/admin/settings')->assertRedirect(route('admin.login'));
});

test('admin can view the settings page', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->get('/admin/settings')->assertOk()->assertSee('Platform Settings');
});

test('admin can add and remove a unit', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Units::class)
        ->set('newUnit', '750 ml')
        ->call('addUnit')
        ->assertSee('750 ml');

    expect(Unit::query()->where('name', '750 ml')->exists())->toBeTrue();

    $unit = Unit::query()->where('name', '750 ml')->firstOrFail();

    Livewire::actingAs($admin, 'admin')
        ->test(Units::class)
        ->call('removeUnit', $unit->id);

    expect(Unit::query()->where('name', '750 ml')->exists())->toBeFalse();
});

test('admin cannot add a duplicate unit', function () {
    $admin = Admin::factory()->create();
    Unit::factory()->create(['name' => '1 kg']);

    Livewire::actingAs($admin, 'admin')
        ->test(Units::class)
        ->set('newUnit', '1 kg')
        ->call('addUnit')
        ->assertHasErrors('newUnit');

    expect(Unit::query()->where('name', '1 kg')->count())->toBe(1);
});

test('admin cannot remove a unit used by products', function () {
    $admin = Admin::factory()->create();
    $unit = Unit::factory()->create(['name' => 'dozen']);
    Product::factory()->create(['unit' => 'dozen']);

    Livewire::actingAs($admin, 'admin')
        ->test(Units::class)
        ->call('removeUnit', $unit->id)
        ->assertHasErrors('remove');

    expect(Unit::query()->where('name', 'dozen')->exists())->toBeTrue();
});

test('admin can update platform settings', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Settings::class)
        ->set('storeName', 'GreenMart')
        ->set('deliveryFee', 50)
        ->set('freeDeliveryThreshold', 600)
        ->set('minimumOrderAmount', 100)
        ->set('enabledPaymentMethods', ['cod'])
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::get('store_name'))->toBe('GreenMart')
        ->and(Setting::get('delivery_fee'))->toBe('50')
        ->and(Setting::get('free_delivery_threshold'))->toBe('600')
        ->and(Setting::get('minimum_order_amount'))->toBe('100')
        ->and(Setting::get('enabled_payment_methods'))->toBe('cod');
});

test('settings require at least one payment method', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Settings::class)
        ->set('enabledPaymentMethods', [])
        ->call('save')
        ->assertHasErrors(['enabledPaymentMethods' => 'required'])
        ->assertSee('At least one payment method is required.');
});

test('last remaining payment method checkbox is disabled', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Settings::class)
        ->set('enabledPaymentMethods', ['cod'])
        ->call('save')
        ->assertSee('disabled')
        ->assertSee('At least one payment method is required');
});

test('settings validate delivery fee as a positive integer', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Settings::class)
        ->set('deliveryFee', -5)
        ->call('save')
        ->assertHasErrors(['deliveryFee' => 'min']);
});

test('admin can set a logo url', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Settings::class)
        ->set('logoType', 'url')
        ->set('logoUrl', 'https://example.com/logo.png')
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::get('logo_type'))->toBe('url')
        ->and(Setting::get('logo_value'))->toBe('https://example.com/logo.png')
        ->and(Setting::logoUrl())->toBe('https://example.com/logo.png');
});

test('admin can upload a logo image', function () {
    Storage::fake('public');
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Settings::class)
        ->set('logoType', 'image')
        ->set('logoImage', UploadedFile::fake()->image('logo.png'))
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::get('logo_type'))->toBe('image');

    Storage::disk('public')->assertExists(Setting::get('logo_value'));

    expect(Setting::logoUrl())->toBe('/storage/'.Setting::get('logo_value'));
});

test('logo url must be a valid url', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Settings::class)
        ->set('logoType', 'url')
        ->set('logoUrl', 'not-a-url')
        ->call('save')
        ->assertHasErrors(['logoUrl' => 'url']);
});
