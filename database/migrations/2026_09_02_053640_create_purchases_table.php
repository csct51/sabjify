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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supplier_name')->nullable();
            $table->date('purchase_date');
            $table->text('remark')->nullable();
            $table->unsignedInteger('total_amount')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->index('purchase_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
