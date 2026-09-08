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
        Schema::create('info_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('position')->unique();
            $table->string('icon', 50);
            $table->string('title', 60);
            $table->string('subtitle', 80)->nullable();
            $table->timestamps();
        });

        // The JSON settings-key path is retired; the table owns the cards now.
        DB::table('settings')->where('key', 'info_cards')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_cards');
    }
};
