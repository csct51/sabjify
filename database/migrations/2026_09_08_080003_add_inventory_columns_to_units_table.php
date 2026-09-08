<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Consolidated unit configuration columns (replaces the deleted
     * add_conversion / add_base_config migrations). Base units are
     * DB-driven via is_base; see seed_inventory_reference_units.
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('base_unit')->nullable()->after('name');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->decimal('to_base_factor', 10, 4)->default(1)->after('base_unit');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->boolean('is_base')->default(false)->after('to_base_factor');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->string('purchase_unit')->nullable()->after('is_base');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->boolean('integer_only')->default(false)->after('purchase_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['integer_only', 'purchase_unit', 'is_base', 'to_base_factor', 'base_unit']);
        });
    }
};
