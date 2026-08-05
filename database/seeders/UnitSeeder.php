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
        $units = ['kg', '500 g', '250 g', '125 g', '1 pc', '2 pcs', 'dozen', 'bunch'];

        foreach ($units as $sortOrder => $name) {
            Unit::updateOrCreate(['name' => $name], ['sort_order' => $sortOrder]);
        }
    }
}
