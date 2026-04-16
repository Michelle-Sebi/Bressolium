<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Game;
use App\Models\Material;
use App\Models\TileType;
use App\Models\Tile;
use App\Models\User;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 21 (Raw_Tareas)
// Title: DB Migration V5a: Tile Schema Correction
// ==========================================

test('tile_types tiene la columna base_type para identificar la familia del terreno', function () {
    expect(Schema::hasTable('tile_types'))->toBeTrue()
        ->and(Schema::hasColumn('tile_types', 'base_type'))->toBeTrue();
});

test('tiles tiene las columnas de trazabilidad de exploración', function () {
    expect(Schema::hasColumns('tiles', ['explored_by_player_id', 'explored_at']))->toBeTrue();
});

test('material_tile_type tiene las columnas de requisitos de tecnología e invento', function () {
    expect(Schema::hasColumns('material_tile_type', ['tech_required', 'invention_required']))->toBeTrue();
});

test('materials tiene columnas tier y group para clasificar recursos en el árbol', function () {
    expect(Schema::hasColumns('materials', ['tier', 'group']))->toBeTrue();
});

test('base_type acepta los cinco biomas y el tipo pueblo especial', function () {
    $biomas = ['bosque', 'cantera', 'rio', 'prado', 'veta', 'pueblo'];

    foreach ($biomas as $bioma) {
        $tileType = TileType::create([
            'name'      => "Casilla $bioma",
            'level'     => 1,
            'base_type' => $bioma,
        ]);
        $this->assertDatabaseHas('tile_types', ['id' => $tileType->id, 'base_type' => $bioma]);
    }
});

test('explored_by_player_id es nullable: una tile no explorada no requiere jugador', function () {
    $game     = Game::factory()->create();
    $tileType = TileType::create(['name' => 'Bosque Nv1', 'level' => 1, 'base_type' => 'bosque']);

    $tile = Tile::create([
        'game_id'               => $game->id,
        'tile_type_id'          => $tileType->id,
        'coord_x'               => 0,
        'coord_y'               => 0,
        'explored'              => false,
        'explored_by_player_id' => null,
        'explored_at'           => null,
    ]);

    expect($tile->explored_by_player_id)->toBeNull()
        ->and($tile->explored_at)->toBeNull();
});

test('explored_by_player_id acepta FK a users cuando la casilla es explorada', function () {
    $user     = User::factory()->create();
    $game     = Game::factory()->create();
    $tileType = TileType::create(['name' => 'Cantera Nv1', 'level' => 1, 'base_type' => 'cantera']);

    $tile = Tile::create([
        'game_id'               => $game->id,
        'tile_type_id'          => $tileType->id,
        'coord_x'               => 1,
        'coord_y'               => 1,
        'explored'              => true,
        'explored_by_player_id' => $user->id,
        'explored_at'           => now(),
    ]);

    expect($tile->explored_by_player_id)->toBe($user->id)
        ->and($tile->explored_at)->not->toBeNull();
});

test('material almacena tier y group según la capa del catálogo', function () {
    $base    = Material::create(['name' => 'Roble',   'tier' => 0, 'group' => 'Bosque']);
    $medio   = Material::create(['name' => 'Carbon',  'tier' => 1, 'group' => 'Bosque']);
    $avanzado = Material::create(['name' => 'Látex',  'tier' => 2, 'group' => 'Bosque']);

    $this->assertDatabaseHas('materials', ['name' => 'Roble',  'tier' => 0, 'group' => 'Bosque']);
    $this->assertDatabaseHas('materials', ['name' => 'Carbon', 'tier' => 1, 'group' => 'Bosque']);
    $this->assertDatabaseHas('materials', ['name' => 'Látex',  'tier' => 2, 'group' => 'Bosque']);
});

test('tech_required e invention_required en material_tile_type son nullable por defecto', function () {
    $tileType = TileType::create(['name' => 'Prado Nv1', 'level' => 1, 'base_type' => 'prado']);
    $material = Material::create(['name' => 'Lino', 'tier' => 0, 'group' => 'Prado']);

    $tileType->materials()->attach($material->id, [
        'quantity'           => 8,
        'tech_required'      => null,
        'invention_required' => null,
    ]);

    $pivot = $tileType->materials()->first()->pivot;
    expect($pivot->tech_required)->toBeNull()
        ->and($pivot->invention_required)->toBeNull();
});
