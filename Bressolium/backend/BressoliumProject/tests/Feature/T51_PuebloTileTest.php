<?php

use App\DTOs\ExploreActionDTO;
use App\DTOs\UpgradeActionDTO;
use App\Exceptions\PuebloTileActionException;
use App\Models\Game;
use App\Models\Tile;
use App\Models\TileType;
use App\Models\User;
use App\Services\ActionService;
use App\Services\BoardGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================================
// T51 — Pueblo Tile: Center Placement + Tech Tree Access
// ============================================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->game = Game::factory()->create();
    $this->round = $this->game->rounds()->create(['number' => 1]);
    $this->game->users()->attach($this->user->id, ['is_afk' => false]);
    $this->round->users()->attach($this->user->id, ['actions_spent' => 0]);
});

// ---
// DoD: BoardGeneratorService garantiza que (7, 7) siempre es base_type=pueblo
// ---

test('generate coloca siempre una casilla de base_type pueblo en la posición central (7, 7)', function () {
    TileType::factory()->create(['base_type' => 'bosque', 'level' => 1]);
    TileType::factory()->create(['base_type' => 'pueblo', 'level' => 1]);

    app(BoardGeneratorService::class)->generate($this->game->id);

    $centralTile = Tile::where('game_id', $this->game->id)
        ->where('coord_x', 7)
        ->where('coord_y', 7)
        ->with('type')
        ->first();

    expect($centralTile)->not->toBeNull();
    expect($centralTile->type->base_type)->toBe('pueblo');
});

test('generate no coloca casillas de tipo pueblo fuera de la posición central (7, 7)', function () {
    TileType::factory()->create(['base_type' => 'bosque', 'level' => 1]);
    TileType::factory()->create(['base_type' => 'pueblo', 'level' => 1]);

    app(BoardGeneratorService::class)->generate($this->game->id);

    $puebloFueraDeCentro = Tile::where('game_id', $this->game->id)
        ->where(fn ($q) => $q->where('coord_x', '!=', 7)->orWhere('coord_y', '!=', 7))
        ->whereHas('type', fn ($q) => $q->where('base_type', 'pueblo'))
        ->count();

    expect($puebloFueraDeCentro)->toBe(0);
});

test('generate crea exactamente 225 casillas (tablero 15x15 completo)', function () {
    TileType::factory()->create(['base_type' => 'bosque', 'level' => 1]);
    TileType::factory()->create(['base_type' => 'pueblo', 'level' => 1]);

    app(BoardGeneratorService::class)->generate($this->game->id);

    $totalTiles = Tile::where('game_id', $this->game->id)->count();
    expect($totalTiles)->toBe(225);
});

// ---
// DoD: La casilla pueblo no puede explorarse mediante acciones individuales
// ---

test('explorar una casilla de tipo pueblo lanza PuebloTileActionException', function () {
    $puebloType = TileType::factory()->create(['base_type' => 'pueblo', 'level' => 1]);
    $tile = Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $puebloType->id,
        'explored' => false,
        'coord_x' => 7,
        'coord_y' => 7,
    ]);

    expect(fn () => app(ActionService::class)->explore(
        new ExploreActionDTO(tileId: $tile->id, userId: $this->user->id)
    ))->toThrow(PuebloTileActionException::class);
});

test('intentar explorar la casilla pueblo no la marca como explorada', function () {
    $puebloType = TileType::factory()->create(['base_type' => 'pueblo', 'level' => 1]);
    $tile = Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $puebloType->id,
        'explored' => false,
    ]);

    try {
        app(ActionService::class)->explore(
            new ExploreActionDTO(tileId: $tile->id, userId: $this->user->id)
        );
    } catch (PuebloTileActionException) {
    }

    expect($tile->fresh()->explored)->toBeFalse();
});

test('intentar explorar la casilla pueblo no consume acciones del jugador', function () {
    $puebloType = TileType::factory()->create(['base_type' => 'pueblo', 'level' => 1]);
    $tile = Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $puebloType->id,
        'explored' => false,
    ]);

    try {
        app(ActionService::class)->explore(
            new ExploreActionDTO(tileId: $tile->id, userId: $this->user->id)
        );
    } catch (PuebloTileActionException) {
    }

    $pivot = $this->round->users()->where('user_id', $this->user->id)->first();
    expect((int) $pivot->pivot->actions_spent)->toBe(0);
});

// ---
// DoD: La casilla pueblo no puede mejorarse mediante acciones individuales
// ---

test('evolucionar una casilla de tipo pueblo lanza PuebloTileActionException', function () {
    $puebloType = TileType::factory()->create(['base_type' => 'pueblo', 'level' => 1]);
    $tile = Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $puebloType->id,
        'explored' => true,
        'coord_x' => 7,
        'coord_y' => 7,
    ]);

    expect(fn () => app(ActionService::class)->upgrade(
        new UpgradeActionDTO(tileId: $tile->id, userId: $this->user->id)
    ))->toThrow(PuebloTileActionException::class);
});

test('intentar evolucionar la casilla pueblo no consume acciones del jugador', function () {
    $puebloType = TileType::factory()->create(['base_type' => 'pueblo', 'level' => 1]);
    $tile = Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $puebloType->id,
        'explored' => true,
    ]);

    try {
        app(ActionService::class)->upgrade(
            new UpgradeActionDTO(tileId: $tile->id, userId: $this->user->id)
        );
    } catch (PuebloTileActionException) {
    }

    $pivot = $this->round->users()->where('user_id', $this->user->id)->first();
    expect((int) $pivot->pivot->actions_spent)->toBe(0);
});
