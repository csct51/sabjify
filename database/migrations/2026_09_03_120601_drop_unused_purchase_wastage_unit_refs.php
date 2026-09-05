<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Guarded: prod never ran the removed create-table lines (no-op there),
        // local/staging DBs that did get cleaned up here.
        if (Schema::hasColumn('purchase_items', 'product_unit_id')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_unit_id');
            });
        }

        if (Schema::hasColumn('wastage_items', 'product_unit_id')) {
            Schema::table('wastage_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_unit_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('purchase_items', 'product_unit_id')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->foreignId('product_unit_id')->nullable()->after('product_id')->constrained('product_units')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('wastage_items', 'product_unit_id')) {
            Schema::table('wastage_items', function (Blueprint $table) {
                $table->foreignId('product_unit_id')->nullable()->after('product_id')->constrained('product_units')->nullOnDelete();
            });
        }
    }
};
