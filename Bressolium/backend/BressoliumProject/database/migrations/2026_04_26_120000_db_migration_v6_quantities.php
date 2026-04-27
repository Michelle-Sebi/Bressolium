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
        Schema::table('invention_prerequisites', function (Blueprint $table) {
            $table->integer('quantity')->default(1);
        });

        Schema::table('technology_prerequisites', function (Blueprint $table) {
            $table->integer('quantity')->default(1);
        });

        Schema::table('game_invention', function (Blueprint $table) {
            $table->integer('quantity')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_invention', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });

        Schema::table('technology_prerequisites', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });

        Schema::table('invention_prerequisites', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
