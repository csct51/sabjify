<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Data-only migration (no schema change). Base rows carry their own
     * purchase/entry unit + integer rule so new bases work admin-only.
     * Idempotent: safe to re-run.
     */
    public function up(): void
    {
        $maxSort = (int) (DB::table('units')->max('sort_order') ?? 0);

        $rows = [
            ['name' => 'g', 'base_unit' => null, 'to_base_factor' => 1, 'is_base' => true, 'purchase_unit' => 'kg', 'integer_only' => false],
            ['name' => 'piece', 'base_unit' => null, 'to_base_factor' => 1, 'is_base' => true, 'purchase_unit' => 'piece', 'integer_only' => true],
            ['name' => 'ml', 'base_unit' => null, 'to_base_factor' => 1, 'is_base' => true, 'purchase_unit' => 'litre', 'integer_only' => false],
        ];

        foreach ($rows as $row) {
            $existing = DB::table('units')->where('name', $row['name'])->first();

            if (! $existing) {
                $maxSort++;

                DB::table('units')->insert([
                    ...$row,
                    'sort_order' => $maxSort,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('units')->where('name', $row['name'])->update([
                    'base_unit' => null,
                    'to_base_factor' => 1,
                    'is_base' => true,
                    'purchase_unit' => $row['purchase_unit'],
                    'integer_only' => $row['integer_only'],
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
