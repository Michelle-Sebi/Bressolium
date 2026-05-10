<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 6 (Raw_Tareas)
// Title: Tile Migrations and Base Dictionary
// ==========================================

test('la tabla tiles gestiona la cuadricula XY y el estado de exploracion', function () {
    expect(Schema::hasTable('tiles'))->toBeTrue()
        ->and(Schema::hasColumns('tiles', ['id', 'game_id', 'tile_type_id', 'assigned_player', 'coord_x', 'coord_y', 'explored']))->toBeTrue()
        ->and(Schema::hasColumn('tiles', 'level'))->toBeFalse();
});

test('la tabla tile_types define el nombre y el nivel del bioma', function () {
    expect(Schema::hasTable('tile_types'))->toBeTrue()
        ->and(Schema::hasColumns('tile_types', ['id', 'name', 'level']))->toBeTrue();
});

test('la tabla material_tile_type define la produccion de recursos por tipo', function () {
    expect(Schema::hasTable('material_tile_type'))->toBeTrue()
        ->and(Schema::hasColumns('material_tile_type', ['tile_type_id', 'material_id', 'quantity']))->toBeTrue();
});

test('el seeder de tipos genera los biomas base con niveles', function () {
    Artisan::call('db:seed', ['--class' => 'TileTypesSeeder']);

    $this->assertDatabaseHas('tile_types', ['name' => 'Forest', 'level' => 1]);
    $this->assertDatabaseHas('tile_types', ['name' => 'Quarry', 'level' => 1]);
});

test('el seeder de produccion inserta cantidades en material_tile_type', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);
    Artisan::call('db:seed', ['--class' => 'TileLevelResourcesSeeder']);

    $count = DB::table('material_tile_type')
        ->where('quantity', '>', 0)
        ->count();

    expect($count)->toBeGreaterThan(0);
});
