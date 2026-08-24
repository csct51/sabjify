<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->boolean('in_stock')->default(true)->after('sort_order');
        });

        // Backfill from the product-level flag while it still exists.
        // Done in PHP to stay driver-agnostic (SQLite has no UPDATE ... JOIN).
        DB::table('products')
            ->select(['id', 'in_stock'])
            ->orderBy('id')
            ->chunkById(100, function ($products) {
                foreach ($products as $product) {
                    DB::table('product_units')
                        ->where('product_id', $product->id)
                        ->update(['in_stock' => (bool) $product->in_stock]);
                }
            });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('in_stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('in_stock')->default(true);
        });

        DB::table('product_units')
            ->select(['product_id', 'in_stock'])
            ->orderBy('product_id')
            ->chunkById(100, function ($units) {
                $byProduct = [];
                foreach ($units as $unit) {
                    $byProduct[$unit->product_id] = ($byProduct[$unit->product_id] ?? false) || (bool) $unit->in_stock;
                }

                foreach ($byProduct as $productId => $inStock) {
                    DB::table('products')
                        ->where('id', $productId)
                        ->update(['in_stock' => $inStock]);
                }
            });

        Schema::table('product_units', function (Blueprint $table) {
            $table->dropColumn('in_stock');
        });
    }
};
