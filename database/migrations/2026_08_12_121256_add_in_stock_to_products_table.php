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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('in_stock')->default(true)->after('stock');
        });

        DB::table('products')->where('stock', '>', 0)->update(['in_stock' => true]);
        DB::table('products')->where('stock', '<=', 0)->update(['in_stock' => false]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock')->default(0)->after('in_stock');
        });

        DB::table('products')->where('in_stock', false)->update(['stock' => 0]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('in_stock');
        });
    }
};
