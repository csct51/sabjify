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
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['ingredients', 'steps']);
        });

        Schema::create('recipe_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['recipe_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_product');

        Schema::table('recipes', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('slug')->constrained()->cascadeOnDelete();
            $table->text('ingredients')->nullable()->after('image');
            $table->text('steps')->nullable()->after('ingredients');
        });
    }
};
