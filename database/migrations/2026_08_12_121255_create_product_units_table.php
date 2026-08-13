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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('unit');
            $table->unsignedInteger('price');
            $table->unsignedInteger('mrp')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'unit']);
            $table->index(['product_id', 'sort_order']);
        });

        DB::table('products')->select('id', 'unit', 'price', 'mrp')->orderBy('id')->chunkById(100, function ($products) {
            foreach ($products as $product) {
                DB::table('product_units')->insert([
                    'product_id' => $product->id,
                    'unit' => $product->unit,
                    'price' => $product->price,
                    'mrp' => $product->mrp,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
