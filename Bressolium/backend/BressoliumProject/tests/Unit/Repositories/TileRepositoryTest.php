<?php

// ==========================================
// TEST FOR: TASK 29 — Tests Unitarios de Backend
// Repository: Eloquent\TileRepository
// ==========================================

use App\Models\Game;
use App\Models\Material;
use App\Models\Round;
use App\Models\Tile;
use App\Models\TileType;
use App\Models\User;
use App\Repositories\Eloquent\TileRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// ─── find ─────────────────────────────────────────────────────────────────────

test('TileRepository::find devuelve el Tile con ese id', function () {
    $game     = Game::factory()->create();
    $tileType = TileType::factory()->create(['level' => 1, 'base_type' => 'bosque']);
    $tile     = Tile::factory()->create(['game_id' => $game->id, 'tile_type_id' => $tileType->id]);

    $repo   = new TileRepository();
    $result = $repo->find($tile->id);

    expect($result)->toBeInstanceOf(Tile::class)
        ->and($result->id)->toBe($tile->id);
});

test('TileRepository::find devuelve null si no existe', function () {
    $repo = new TileRepository();
    expect($repo->find('00000000-0000-0000-0000-000000000000'))->toBeNull();
});

// ─── isUserInGame ─────────────────────────────────────────────────────────────

test('TileRepository::isUserInGame devuelve true si el usuario pertenece a la partida', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $game->users()->attach($user->id);

    $repo = new TileRepository();
    expect($repo->isUserInGame($user->id, $game->id))->toBeTrue();
});

test('TileRepository::isUserInGame devuelve false si el usuario NO pertenece a la partida', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    $repo = new TileRepository();
    expect($repo->isUserInGame($user->id, $game->id))->toBeFalse();
});

// ─── getCurrentRound ──────────────────────────────────────────────────────────

test('TileRepository::getCurrentRound devuelve la ronda abierta (sin ended_at)', function () {
    $game  = Game::factory()->create();
    $round = $game->rounds()->create(['number' => 1, 'start_date' => now()]);

    $repo   = new TileRepository();
    $result = $repo->getCurrentRound($game->id);

    expect($result)->toBeInstanceOf(Round::class)
        ->and($result->id)->toBe($round->id);
});

test('TileRepository::getCurrentRound devuelve null si todas las rondas están cerradas', function () {
    $game = Game::factory()->create();
    $game->rounds()->create(['number' => 1, 'start_date' => now(), 'ended_at' => now()]);

    $repo = new TileRepository();
    expect($repo->getCurrentRound($game->id))->toBeNull();
});

// ─── getActionsSpent ──────────────────────────────────────────────────────────

test('TileRepository::getActionsSpent devuelve el valor del pivot round_user', function () {
    $user  = User::factory()->create();
    $game  = Game::factory()->create();
    $round = $game->rounds()->create(['number' => 1, 'start_date' => now()]);
    $round->users()->attach($user->id, ['actions_spent' => 3]);

    $repo = new TileRepository();
    expect($repo->getActionsSpent($round, $user->id))->toBe(3);
});

test('TileRepository::getActionsSpent devuelve 0 si el usuario no está en la ronda', function () {
    $user  = User::factory()->create();
    $game  = Game::factory()->create();
    $round = $game->rounds()->create(['number' => 1, 'start_date' => now()]);

    $repo = new TileRepository();
    expect($repo->getActionsSpent($round, $user->id))->toBe(0);
});

// ─── markExplored ─────────────────────────────────────────────────────────────

test('TileRepository::markExplored marca la casilla como explorada', function () {
    $user     = User::factory()->create();
    $game     = Game::factory()->create();
    $tileType = TileType::factory()->create(['level' => 1, 'base_type' => 'bosque']);
    $tile     = Tile::factory()->create([
        'game_id'      => $game->id,
        'tile_type_id' => $tileType->id,
        'explored'     => false,
    ]);

    $repo = new TileRepository();
    $repo->markExplored($tile, $user->id);

    $fresh = $tile->fresh();
    expect((bool) $fresh->explored)->toBeTrue()
        ->and($fresh->explored_by_player_id)->toBe($user->id)
        ->and($fresh->explored_at)->not->toBeNull();
});

// ─── findNextTileType ─────────────────────────────────────────────────────────

test('TileRepository::findNextTileType devuelve el TileType del siguiente nivel', function () {
    $lvl1 = TileType::create(['name' => 'Bosque 1', 'level' => 1, 'base_type' => 'bosque']);
    $lvl2 = TileType::create(['name' => 'Bosque 2', 'level' => 2, 'base_type' => 'bosque']);

    $game = Game::factory()->create();
    $tile = Tile::factory()->create(['game_id' => $game->id, 'tile_type_id' => $lvl1->id]);

    $repo   = new TileRepository();
    $result = $repo->findNextTileType($tile);

    expect($result)->toBeInstanceOf(TileType::class)
        ->and($result->id)->toBe($lvl2->id);
});

test('TileRepository::findNextTileType devuelve null si no hay siguiente nivel', function () {
    $lvl2 = TileType::create(['name' => 'Bosque Max', 'level' => 2, 'base_type' => 'bosque']);
    $game = Game::factory()->create();
    $tile = Tile::factory()->create(['game_id' => $game->id, 'tile_type_id' => $lvl2->id]);

    $repo = new TileRepository();
    expect($repo->findNextTileType($tile))->toBeNull();
});

// ─── hasSufficientMaterials ───────────────────────────────────────────────────

test('TileRepository::hasSufficientMaterials devuelve true si el stock es suficiente', function () {
    $game     = Game::factory()->create();
    $material = Material::create(['name' => 'Roble', 'tier' => 0, 'group' => 'Bosque']);
    $game->materials()->attach($material->id, ['quantity' => 10]);

    $lvl2    = TileType::create(['name' => 'Bosque 2', 'level' => 2, 'base_type' => 'bosque']);
    $lvl2->materials()->attach($material->id, ['quantity' => 5]);
    $costs   = $lvl2->materials;

    $repo = new TileRepository();
    expect($repo->hasSufficientMaterials($game, $costs))->toBeTrue();
});

test('TileRepository::hasSufficientMaterials devuelve false si el stock es insuficiente', function () {
    $game     = Game::factory()->create();
    $material = Material::create(['name' => 'Roble', 'tier' => 0, 'group' => 'Bosque']);
    $game->materials()->attach($material->id, ['quantity' => 3]);

    $lvl2  = TileType::create(['name' => 'Bosque 2', 'level' => 2, 'base_type' => 'bosque']);
    $lvl2->materials()->attach($material->id, ['quantity' => 5]);
    $costs = $lvl2->materials;

    $repo = new TileRepository();
    expect($repo->hasSufficientMaterials($game, $costs))->toBeFalse();
});

// ─── deductMaterials ──────────────────────────────────────────────────────────

test('TileRepository::deductMaterials reduce la cantidad de materiales del juego', function () {
    $game     = Game::factory()->create();
    $material = Material::create(['name' => 'Roble', 'tier' => 0, 'group' => 'Bosque']);
    $game->materials()->attach($material->id, ['quantity' => 10]);

    $lvl2  = TileType::create(['name' => 'Bosque 2', 'level' => 2, 'base_type' => 'bosque']);
    $lvl2->materials()->attach($material->id, ['quantity' => 3]);
    $costs = $lvl2->materials;

    $repo = new TileRepository();
    $repo->deductMaterials($game, $costs);

    $remaining = $game->materials()->where('material_id', $material->id)->first()->pivot->quantity;
    expect($remaining)->toBe(7);
});
