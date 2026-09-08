<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Consolidated inventory columns for products (replaces the deleted
     * add_current_stock / decimal-alter / add_base_unit / add_low_stock
     * migrations). Stock is stored in base units (g/piece/ml).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('current_stock', 12, 3)->default(0)->after('sort_order');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('base_unit')->nullable()->after('unit');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('low_stock', 12, 3)->nullable()->after('current_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['low_stock', 'base_unit', 'current_stock']);
        });
    }
};
