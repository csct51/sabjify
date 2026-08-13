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
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_unit_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
        });

        DB::table('cart_items')->select('id', 'product_id')->whereNotNull('product_id')->orderBy('id')->chunkById(100, function ($items) {
            foreach ($items as $item) {
                $defaultUnitId = DB::table('product_units')
                    ->where('product_id', $item->product_id)
                    ->orderBy('sort_order')
                    ->value('id');

                DB::table('cart_items')->where('id', $item->id)->update(['product_unit_id' => $defaultUnitId]);
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'product_id']);
            $table->unique(['user_id', 'product_id', 'product_unit_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'product_id', 'product_unit_id']);
            $table->unique(['user_id', 'product_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_unit_id');
        });
    }
};
