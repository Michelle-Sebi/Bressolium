<?php

use App\Models\Game;
use App\Models\Material;
use App\Models\Tile;
use App\Models\TileType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 8 (Raw_Tareas)
// Title: Individual Actions API (Explore / Upgrade)
// ==========================================

// Prepara el escenario completo para un upgrade válido:
// - dos TileTypes encadenados (level 1 → 2, mismo base_type)
// - material con coste definido en material_tile_type del nivel destino
// - stock suficiente en game_material
// - casilla marcada como explorada y con el tipo de nivel 1
function makeUpgradeScenario(Game $game, Tile $tile, int $stock = 15): array
{
    $currentType = TileType::create(['name' => 'Bosque Nv1', 'level' => 1, 'base_type' => 'bosque']);
    $nextType = TileType::create(['name' => 'Bosque Nv2', 'level' => 2, 'base_type' => 'bosque']);
    $material = Material::create(['name' => 'Roble', 'tier' => 0, 'group' => 'Bosque']);

    $currentType->upgradeCosts()->attach($material->id, ['quantity' => 10]);
    $game->materials()->attach($material->id, ['quantity' => $stock]);
    $tile->update(['tile_type_id' => $currentType->id, 'explored' => true]);

    return compact('currentType', 'nextType', 'material');
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->game = Game::factory()->create();
    $this->user->games()->attach($this->game->id);

    $this->round = $this->game->rounds()->create([
        'number' => 1,
        'start_date' => now(),
    ]);
    $this->round->users()->attach($this->user->id, ['actions_spent' => 0]);

    $tileType = TileType::factory()->create(['level' => 1, 'base_type' => 'bosque']);
    $this->tile = Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $tileType->id,
        'coord_x' => 1,
        'coord_y' => 1,
        'explored' => false,
    ]);

    // Casilla de inicio del usuario: explorada y adyacente al tile de pruebas (coord 0,1 → adyacente a 1,1)
    Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $tileType->id,
        'coord_x' => 0,
        'coord_y' => 1,
        'explored' => true,
        'explored_by_player_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);
});

// ─── Autenticación ────────────────────────────────────────────────────────────

test('POST /api/tiles/{id}/explore devuelve 401 sin sesión activa', function () {
    $game = Game::factory()->create();
    $tileType = TileType::factory()->create();
    $tile = Tile::factory()->create(['game_id' => $game->id, 'tile_type_id' => $tileType->id]);

    $this->app['auth']->forgetGuards();

    $this->postJson("/api/v1/tiles/{$tile->id}/explore")
        ->assertUnauthorized();
});

test('POST /api/tiles/{id}/upgrade devuelve 401 sin sesión activa', function () {
    $game = Game::factory()->create();
    $tileType = TileType::factory()->create();
    $tile = Tile::factory()->create(['game_id' => $game->id, 'tile_type_id' => $tileType->id]);

    $this->app['auth']->forgetGuards();

    $this->postJson("/api/v1/tiles/{$tile->id}/upgrade")
        ->assertUnauthorized();
});

// ─── Autorización ─────────────────────────────────────────────────────────────

test('explorar devuelve 403 si el usuario no pertenece a la partida del tile', function () {
    $otherGame = Game::factory()->create();
    $tileType = TileType::factory()->create();
    $otherTile = Tile::factory()->create([
        'game_id' => $otherGame->id,
        'tile_type_id' => $tileType->id,
        'explored' => false,
    ]);

    $this->postJson("/api/v1/tiles/{$otherTile->id}/explore")
        ->assertForbidden();
});

test('upgrade devuelve 403 si el usuario no pertenece a la partida del tile', function () {
    $otherGame = Game::factory()->create();
    $tileType = TileType::factory()->create();
    $otherTile = Tile::factory()->create([
        'game_id' => $otherGame->id,
        'tile_type_id' => $tileType->id,
        'explored' => true,
    ]);

    $this->postJson("/api/v1/tiles/{$otherTile->id}/upgrade")
        ->assertForbidden();
});

// ─── Acción: Explorar ─────────────────────────────────────────────────────────

test('explorar gasta 1 acción y revela la casilla', function () {
    $this->postJson("/api/v1/tiles/{$this->tile->id}/explore")
        ->assertStatus(200);

    $actionsSpent = $this->round->users()
        ->where('user_id', $this->user->id)
        ->first()->pivot->actions_spent;

    $this->tile->refresh();

    expect($actionsSpent)->toBe(1)
        ->and($this->tile->explored)->toBeTrue();
});

test('explorar registra explored_by_player_id y explored_at en la casilla', function () {
    $this->postJson("/api/v1/tiles/{$this->tile->id}/explore")
        ->assertStatus(200);

    $this->tile->refresh();

    expect($this->tile->explored_by_player_id)->toBe($this->user->id)
        ->and($this->tile->explored_at)->not->toBeNull();
});

test('explorar devuelve respuesta con estructura {success, data, error}', function () {
    $this->postJson("/api/v1/tiles/{$this->tile->id}/explore")
        ->assertStatus(200)
        ->assertJsonStructure(['success', 'data', 'error']);
});

test('no se puede explorar si ya se gastaron 2 acciones', function () {
    $this->round->users()->updateExistingPivot($this->user->id, ['actions_spent' => 2]);

    $this->postJson("/api/v1/tiles/{$this->tile->id}/explore")
        ->assertForbidden();
});

test('no se puede explorar una casilla que ya está explorada', function () {
    $this->tile->update(['explored' => true]);

    $this->postJson("/api/v1/tiles/{$this->tile->id}/explore")
        ->assertStatus(422);
});

test('no se puede explorar una casilla no adyacente al territorio del jugador', function () {
    $tileType = TileType::factory()->create(['level' => 1, 'base_type' => 'bosque']);
    $farTile = Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $tileType->id,
        'coord_x' => 5,
        'coord_y' => 5,
        'explored' => false,
    ]);

    $this->postJson("/api/v1/tiles/{$farTile->id}/explore")
        ->assertStatus(422)
        ->assertJsonFragment(['error' => 'Solo puedes explorar casillas adyacentes a tu territorio.']);
});

// ─── Acción: Upgrade ──────────────────────────────────────────────────────────

test('upgrade cambia tile_type al siguiente nivel y descuenta los materiales del equipo', function () {
    ['nextType' => $nextType, 'material' => $material] = makeUpgradeScenario(
        $this->game, $this->tile
    );

    $this->postJson("/api/v1/tiles/{$this->tile->id}/upgrade")
        ->assertStatus(200);

    $this->tile->refresh();
    expect($this->tile->tile_type_id)->toBe($nextType->id);

    $stock = $this->game->materials()->where('material_id', $material->id)->first()->pivot->quantity;
    expect($stock)->toBe(5); // 15 - 10
});

test('upgrade gasta 1 acción diaria', function () {
    makeUpgradeScenario($this->game, $this->tile);

    $this->postJson("/api/v1/tiles/{$this->tile->id}/upgrade")
        ->assertStatus(200);

    $actionsSpent = $this->round->users()
        ->where('user_id', $this->user->id)
        ->first()->pivot->actions_spent;

    expect($actionsSpent)->toBe(1);
});

test('upgrade devuelve respuesta con estructura {success, data, error}', function () {
    makeUpgradeScenario($this->game, $this->tile);

    $this->postJson("/api/v1/tiles/{$this->tile->id}/upgrade")
        ->assertStatus(200)
        ->assertJsonStructure(['success', 'data', 'error']);
});

test('no se puede hacer upgrade si ya se gastaron 2 acciones', function () {
    makeUpgradeScenario($this->game, $this->tile);
    $this->round->users()->updateExistingPivot($this->user->id, ['actions_spent' => 2]);

    $this->postJson("/api/v1/tiles/{$this->tile->id}/upgrade")
        ->assertForbidden();
});

test('no se puede hacer upgrade de una casilla no explorada', function () {
    makeUpgradeScenario($this->game, $this->tile);
    $this->tile->update(['explored' => false]);

    $this->postJson("/api/v1/tiles/{$this->tile->id}/upgrade")
        ->assertStatus(422);
});

test('upgrade falla con 400 si el equipo no tiene materiales suficientes', function () {
    makeUpgradeScenario($this->game, $this->tile, stock: 5); // coste es 10, solo hay 5

    $this->postJson("/api/v1/tiles/{$this->tile->id}/upgrade")
        ->assertStatus(400)
        ->assertJsonFragment(['error' => 'Materiales insuficientes para realizar esta acción.']);
});
