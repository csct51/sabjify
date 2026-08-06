<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $settings = Setting::pluck('value', 'key')->filter()->all();

        if (isset($settings['store_name'])) {
            config(['app.name' => $settings['store_name']]);
        }

        if (isset($settings['delivery_fee'])) {
            config(['mart.delivery_fee' => (int) $settings['delivery_fee']]);
        }

        if (isset($settings['free_delivery_threshold'])) {
            config(['mart.free_delivery_threshold' => (int) $settings['free_delivery_threshold']]);
        }

        if (isset($settings['minimum_order_amount'])) {
            config(['mart.minimum_order_amount' => (int) $settings['minimum_order_amount']]);
        }

        if (isset($settings['placeholder_image'])) {
            config(['mart.placeholder_image' => $settings['placeholder_image']]);
        }

        if (isset($settings['enabled_payment_methods'])) {
            $enabled = array_filter(array_map('trim', explode(',', $settings['enabled_payment_methods'])));
            config(['mart.enabled_payment_methods' => array_values($enabled)]);
        }
    }
}
