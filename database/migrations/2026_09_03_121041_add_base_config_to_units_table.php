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
        Schema::table('units', function (Blueprint $table) {
            $table->boolean('is_base')->default(false)->after('base_unit');
            $table->string('purchase_unit')->nullable()->after('is_base');
            $table->boolean('integer_only')->default(false)->after('purchase_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['is_base', 'purchase_unit', 'integer_only']);
        });
    }
};
