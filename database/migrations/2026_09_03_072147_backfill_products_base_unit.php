<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Data-only migration (no schema change). Derives each product's base unit
     * with the same logic Product::baseUnit() used before the column existed.
     * Self-contained on the DB facade (no app model dependency) and
     * driver-agnostic for the sqlite test suite.
     */
    public function up(): void
    {
        DB::table('products')->whereNull('base_unit')->orderBy('id')->chunkById(200, function ($products) {
            foreach ($products as $product) {
                $firstUnit = DB::table('product_units')
                    ->where('product_id', $product->id)
                    ->orderBy('sort_order')
                    ->orderBy('price')
                    ->value('unit');

                DB::table('products')->where('id', $product->id)->update([
                    'base_unit' => $this->resolveBase($firstUnit ?? $product->unit),
                ]);
            }
        });
    }

    private function resolveBase(?string $name): string
    {
        if ($name) {
            $base = DB::table('units')->where('name', $name)->value('base_unit');

            if ($base === 'g' || $base === 'piece') {
                return $base;
            }

            $lower = strtolower($name);

            if (str_contains($lower, 'kg') || str_contains($lower, 'g')) {
                return 'g';
            }
        }

        return 'g';
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
