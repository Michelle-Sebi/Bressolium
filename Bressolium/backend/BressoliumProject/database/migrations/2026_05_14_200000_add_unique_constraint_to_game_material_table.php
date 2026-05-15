<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Consolidate duplicate rows: update the min-id keeper with the summed quantity
        DB::statement("
            UPDATE game_material keeper
            INNER JOIN (
                SELECT MIN(id) AS min_id, game_id, material_id, SUM(quantity) AS total
                FROM game_material
                GROUP BY game_id, material_id
            ) agg ON keeper.id = agg.min_id
            SET keeper.quantity = agg.total
        ");

        // Delete all non-keeper rows (everything except min-id per game+material)
        DB::statement("
            DELETE gm FROM game_material gm
            INNER JOIN (
                SELECT MIN(id) AS min_id, game_id, material_id
                FROM game_material
                GROUP BY game_id, material_id
            ) keepers
              ON  gm.game_id    = keepers.game_id
              AND gm.material_id = keepers.material_id
            WHERE gm.id != keepers.min_id
        ");

        Schema::table('game_material', function (Blueprint $table) {
            $table->unique(['game_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::table('game_material', function (Blueprint $table) {
            $table->dropUnique(['game_id', 'material_id']);
        });
    }
};
