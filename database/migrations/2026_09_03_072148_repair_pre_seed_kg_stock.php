<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Data-only migration (no schema change). Rows stored before the 'kg' unit
     * was seeded never got the x1000 conversion, fingerprint: unit = 'kg' AND
     * base_qty == qty (a correctly converted kg row always has
     * base_qty = qty * 1000 != qty, since qty >= 0.001). Recompute them, then
     * rebuild current_stock of affected products from corrected items.
     */
    public function up(): void
    {
        $fixedPurchases = DB::table('purchase_items')
            ->where('unit', 'kg')
            ->whereColumn('base_qty', 'qty')
            ->update(['base_qty' => DB::raw('ROUND(qty * 1000, 3)')]);

        $fixedWastages = DB::table('wastage_items')
            ->where('unit', 'kg')
            ->whereColumn('base_qty', 'qty')
            ->update(['base_qty' => DB::raw('ROUND(qty * 1000, 3)')]);

        $productIds = DB::table('purchase_items')->where('unit', 'kg')->pluck('product_id')
            ->merge(DB::table('wastage_items')->where('unit', 'kg')->pluck('product_id'))
            ->unique()
            ->values();

        foreach ($productIds as $productId) {
            $purchased = (float) DB::table('purchase_items')->where('product_id', $productId)->sum('base_qty');
            $wasted = (float) DB::table('wastage_items')->where('product_id', $productId)->sum('base_qty');

            DB::table('products')->where('id', $productId)->update([
                'current_stock' => round($purchased - $wasted, 3),
            ]);
        }

        logger()->info('repair_pre_seed_kg_stock: fixed '.($fixedPurchases + $fixedWastages).' item rows, rebuilt '.count($productIds).' products.');
    }

    /**
     * Reverse the migrations.
     *
     * Repair is intentionally irreversible; forward-fix instead.
     */
    public function down(): void
    {
        //
    }
};
