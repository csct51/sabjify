<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = ['1 kg', '500 g', '250 g', '1 pc', 'dozen', 'bunch'];

        foreach ($units as $sortOrder => $name) {
            Unit::updateOrCreate(['name' => $name], ['sort_order' => $sortOrder]);
        }
    }
}
