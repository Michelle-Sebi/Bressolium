<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tile_types: añadir base_type para identificar la familia del terreno
        Schema::table('tile_types', function (Blueprint $table) {
            $table->string('base_type')->nullable();
        });

        // tiles: añadir trazabilidad de exploración
        Schema::table('tiles', function (Blueprint $table) {
            $table->uuid('explored_by_player_id')->nullable();
            $table->foreign('explored_by_player_id')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->timestamp('explored_at')->nullable();
        });

        // material_tile_type: añadir requisitos de desbloqueo (tech e invento)
        Schema::table('material_tile_type', function (Blueprint $table) {
            $table->uuid('tech_required')->nullable();
            $table->foreign('tech_required')
                ->references('id')->on('technologies')
                ->nullOnDelete();
            $table->uuid('invention_required')->nullable();
            $table->foreign('invention_required')
                ->references('id')->on('inventions')
                ->nullOnDelete();
        });

        // materials: añadir tier y group para clasificación en el catálogo
        Schema::table('materials', function (Blueprint $table) {
            $table->integer('tier')->nullable();
            $table->string('group')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['tier', 'group']);
        });

        Schema::table('material_tile_type', function (Blueprint $table) {
            $table->dropForeign(['invention_required']);
            $table->dropForeign(['tech_required']);
            $table->dropColumn(['tech_required', 'invention_required']);
        });

        Schema::table('tiles', function (Blueprint $table) {
            $table->dropForeign(['explored_by_player_id']);
            $table->dropColumn(['explored_by_player_id', 'explored_at']);
        });

        Schema::table('tile_types', function (Blueprint $table) {
            $table->dropColumn('base_type');
        });
    }
};
