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
            ['name' => '1 kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'is_base' => false, 'purchase_unit' => null, 'integer_only' => false, 'sort_order' => 0],
            ['name' => '500 g', 'base_unit' => 'g', 'to_base_factor' => 500, 'is_base' => false, 'purchase_unit' => null, 'integer_only' => false, 'sort_order' => 1],
            ['name' => '250 g', 'base_unit' => 'g', 'to_base_factor' => 250, 'is_base' => false, 'purchase_unit' => null, 'integer_only' => false, 'sort_order' => 2],
            ['name' => '1 pc', 'base_unit' => 'piece', 'to_base_factor' => 1, 'is_base' => false, 'purchase_unit' => null, 'integer_only' => false, 'sort_order' => 3],
            ['name' => 'dozen', 'base_unit' => 'piece', 'to_base_factor' => 12, 'is_base' => false, 'purchase_unit' => null, 'integer_only' => false, 'sort_order' => 4],
            ['name' => 'bunch', 'base_unit' => 'piece', 'to_base_factor' => 1, 'is_base' => false, 'purchase_unit' => null, 'integer_only' => false, 'sort_order' => 5],
            ['name' => 'kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'is_base' => false, 'purchase_unit' => null, 'integer_only' => false, 'sort_order' => 6],
            ['name' => 'piece', 'base_unit' => null, 'to_base_factor' => 1, 'is_base' => true, 'purchase_unit' => 'piece', 'integer_only' => true, 'sort_order' => 7],
            ['name' => 'g', 'base_unit' => null, 'to_base_factor' => 1, 'is_base' => true, 'purchase_unit' => 'kg', 'integer_only' => false, 'sort_order' => 8],
            ['name' => 'ml', 'base_unit' => null, 'to_base_factor' => 1, 'is_base' => true, 'purchase_unit' => 'litre', 'integer_only' => false, 'sort_order' => 9],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(['name' => $unit['name']], $unit);
        }
    }
}
