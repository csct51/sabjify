<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Data-only migration (no schema change). Seeds sensible per-base
     * defaults for rows predating the column; admins can override per
     * product. Nulls only — never overwrites admin-set values.
     */
    public function up(): void
    {
        $defaults = ['g' => 1000, 'piece' => 10, 'ml' => 1000];

        foreach ($defaults as $base => $threshold) {
            DB::table('products')
                ->whereNull('low_stock')
                ->where('base_unit', $base)
                ->update(['low_stock' => $threshold]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * Backfill is intentionally irreversible; forward-fix instead.
     */
    public function down(): void
    {
        //
    }
};
