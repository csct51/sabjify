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
        $units = [
            ['name' => '1 kg', 'sort_order' => 0],
            ['name' => '500 g', 'sort_order' => 1],
            ['name' => '250 g', 'sort_order' => 2],
            ['name' => '1 pc', 'sort_order' => 3],
            ['name' => 'dozen', 'sort_order' => 4],
            ['name' => 'bunch', 'sort_order' => 5],
            ['name' => 'kg', 'sort_order' => 6, 'base_unit' => 'g', 'to_base_factor' => 1000],
            ['name' => 'piece', 'sort_order' => 7, 'base_unit' => null, 'to_base_factor' => 1, 'is_base' => true, 'purchase_unit' => 'piece', 'integer_only' => true],
            ['name' => 'g', 'sort_order' => 8, 'base_unit' => null, 'to_base_factor' => 1, 'is_base' => true, 'purchase_unit' => 'kg', 'integer_only' => false],
            ['name' => 'ml', 'sort_order' => 9, 'base_unit' => null, 'to_base_factor' => 1, 'is_base' => true, 'purchase_unit' => 'litre', 'integer_only' => false],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(['name' => $unit['name']], $unit);
        }
    }
}
