<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'store_name' => config('app.name'),
            'delivery_fee' => (string) config('mart.delivery_fee'),
            'free_delivery_threshold' => (string) config('mart.free_delivery_threshold'),
            'placeholder_image' => config('mart.placeholder_image'),
            'enabled_payment_methods' => implode(',', config('mart.enabled_payment_methods')),
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
