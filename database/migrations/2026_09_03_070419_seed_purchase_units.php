<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Data-only migration (no schema change). Purchase/wastage rows store the
     * purchase unit name ('kg'/'piece') and convert via units.to_base_factor,
     * but UnitSeeder never created these rows — so kg purchases were stored
     * unconverted (e.g. 5.5 kg became 5.5 g). Insert them idempotently.
     */
    public function up(): void
    {
        $rows = [
            ['name' => 'kg', 'base_unit' => 'g', 'to_base_factor' => 1000, 'sort_order' => 6],
            ['name' => 'piece', 'base_unit' => 'piece', 'to_base_factor' => 1, 'sort_order' => 7],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('units')->where('name', $row['name'])->exists();

            if (! $exists) {
                DB::table('units')->insert([
                    ...$row,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * Safe: purchase/wastage items reference units by name string (no FK), so
     * removing these rows only restores the pre-fix unconverted behaviour.
     */
    public function down(): void
    {
        DB::table('units')->whereIn('name', ['kg', 'piece'])->delete();
    }
};
