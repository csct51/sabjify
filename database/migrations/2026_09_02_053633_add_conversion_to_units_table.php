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
        Schema::table('units', function (Blueprint $table) {
            $table->string('base_unit')->nullable()->after('name');
            $table->decimal('to_base_factor', 10, 4)->default(1)->after('base_unit');
        });

        $map = [
            '1 kg' => ['base_unit' => 'g', 'to_base_factor' => 1000],
            '500 g' => ['base_unit' => 'g', 'to_base_factor' => 500],
            '250 g' => ['base_unit' => 'g', 'to_base_factor' => 250],
            '1 pc' => ['base_unit' => 'piece', 'to_base_factor' => 1],
            'piece' => ['base_unit' => 'piece', 'to_base_factor' => 1],
            'kg' => ['base_unit' => 'g', 'to_base_factor' => 1000],
            'dozen' => ['base_unit' => 'piece', 'to_base_factor' => 12],
            'bunch' => ['base_unit' => 'piece', 'to_base_factor' => 1],
        ];

        foreach ($map as $name => $data) {
            DB::table('units')->where('name', $name)->update($data);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['base_unit', 'to_base_factor']);
        });
    }
};
