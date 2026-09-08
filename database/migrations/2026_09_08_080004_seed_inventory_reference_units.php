<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Consolidated reference-unit seed (replaces the deleted
     * seed_purchase_units and seed_base_unit_rows migrations). Display
     * units ('1 kg', 'dozen', …) are intentionally NOT seeded here: tests
     * and UnitSeeder own those rows, and seeding them would collide with
     * test setup. REQUIRED as a migration — tests run RefreshDatabase
     * without seeders, so the suite only sees rows inserted here.
     * Idempotent: safe to re-run.
     */
    public function up(): void
    {
        $rows = [
            ['name' => 'kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'is_base' => false, 'purchase_unit' => null, 'integer_only' => false, 'sort_order' => 6],
            ['name' => 'piece', 'base_unit' => null, 'to_base_factor' => 1, 'is_base' => true, 'purchase_unit' => 'piece', 'integer_only' => true, 'sort_order' => 7],
            ['name' => 'g', 'base_unit' => null, 'to_base_factor' => 1, 'is_base' => true, 'purchase_unit' => 'kg', 'integer_only' => false, 'sort_order' => 8],
            ['name' => 'ml', 'base_unit' => null, 'to_base_factor' => 1, 'is_base' => true, 'purchase_unit' => 'litre', 'integer_only' => false, 'sort_order' => 9],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('units')->where('name', $row['name'])->exists();

            if (! $exists) {
                DB::table('units')->insert([
                    ...$row,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('units')->where('name', $row['name'])->update([
                    ...$row,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * Data seed: intentionally irreversible; forward-fix instead.
     */
    public function down(): void
    {
        //
    }
};
