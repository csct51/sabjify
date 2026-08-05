<?php

use App\Livewire\Admin\Settings;
use App\Models\Admin;
use App\Models\Setting;
use Livewire\Livewire;

test('guest is redirected to admin login when accessing settings', function () {
    $this->get('/admin/settings')->assertRedirect(route('admin.login'));
});

test('admin can view the settings page', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->get('/admin/settings')->assertOk()->assertSee('Platform Settings');
});

test('admin can update platform settings', function () {
    $admin = Admin::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(Settings::class)
        ->set('storeName', 'GreenMart')
        ->set('deliveryFee', 50)
        ->set('freeDeliveryThreshold', 600)
        ->set('enabledPaymentMethods', ['cod'])
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::get('store_name'))->toBe('GreenMart')
        ->and(Setting::get('delivery_fee'))->toBe('50')
        ->and(Setting::get('free_delivery_threshold'))->toBe('600')
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
