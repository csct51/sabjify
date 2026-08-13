<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recipe_product', function (Blueprint $table) {
            $table->foreignId('product_unit_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
        });

        DB::table('recipe_product')->select('id', 'product_id')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $defaultUnitId = DB::table('product_units')
                    ->where('product_id', $row->product_id)
                    ->orderBy('sort_order')
                    ->value('id');

                DB::table('recipe_product')->where('id', $row->id)->update(['product_unit_id' => $defaultUnitId]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_product', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_unit_id');
        });
    }
};
