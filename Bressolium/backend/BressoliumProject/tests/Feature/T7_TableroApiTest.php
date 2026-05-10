<?php

use App\Models\Game;
use App\Models\Tile;
use App\Models\TileType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 7 (Raw_Tareas)
// Title: Board Generator and API Controller
// ==========================================

// Puebla los 6 tipos de casilla base que necesita el generador.
function seedTileTypes(): void
{
    foreach (['bosque', 'cantera', 'rio', 'prado', 'mina', 'pueblo'] as $base) {
        TileType::create(['name' => ucfirst($base), 'level' => 1, 'base_type' => $base]);
    }
}

// ─── Autenticación ────────────────────────────────────────────────────────────

test('GET /api/board/{id} devuelve 401 sin sesión activa', function () {
    $game = Game::factory()->create();

    $this->getJson("/api/v1/board/{$game->id}")
        ->assertUnauthorized();
});

// ─── Endpoint GET /api/board/{id} ─────────────────────────────────────────────

test('GET /api/board/{id} devuelve 403 si el usuario no pertenece a la partida', function () {
    $user = User::factory()->create();
    $otherGame = Game::factory()->create();
    Tile::factory()->count(5)->create(['game_id' => $otherGame->id]);

    $this->actingAs($user)
        ->getJson("/api/v1/board/{$otherGame->id}")
        ->assertForbidden();
});

test('GET /api/board/{id} devuelve 200 con estructura {success, data[*], error} para miembro', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $user->games()->attach($game->id);
    Tile::factory()->count(10)->create(['game_id' => $game->id]);

    $this->actingAs($user)
        ->getJson("/api/v1/board/{$game->id}")
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'coord_x', 'coord_y', 'tile_type_id', 'explored'],
            ],
            'error',
        ]);
});

test('GET /api/board/{id} devuelve solo las casillas de la partida solicitada', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $other = Game::factory()->create();
    $user->games()->attach($game->id);
    $user->games()->attach($other->id);
    Tile::factory()->count(10)->create(['game_id' => $game->id]);
    Tile::factory()->count(5)->create(['game_id' => $other->id]);

    $response = $this->actingAs($user)->getJson("/api/v1/board/{$game->id}");

    expect(count($response->json('data')))->toBe(10);
});

// ─── Generación automática del tablero ───────────────────────────────────────

test('crear equipo genera automáticamente exactamente 225 casillas (15×15)', function () {
    seedTileTypes();
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/v1/game/create', ['team_name' => 'Equipo Alpha']);
    $response->assertStatus(200);

    $gameId = $response->json('data.id');
    expect(Tile::where('game_id', $gameId)->count())->toBe(225);
});

test('el tablero cubre todas las coordenadas 0-14 × 0-14 sin duplicados', function () {
    seedTileTypes();
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/v1/game/create', ['team_name' => 'Equipo Beta']);
    $gameId = $response->json('data.id');

    $coords = Tile::where('game_id', $gameId)
        ->get(['coord_x', 'coord_y'])
        ->map(fn ($t) => "{$t->coord_x},{$t->coord_y}")
        ->unique();

    expect($coords)->toHaveCount(225);

    for ($x = 0; $x <= 14; $x++) {
        for ($y = 0; $y <= 14; $y++) {
            expect($coords->contains("{$x},{$y}"))->toBeTrue(
                "Falta la casilla ({$x},{$y}) en el tablero generado"
            );
        }
    }
});

test('todas las casillas generadas nacen con explored = false', function () {
    seedTileTypes();
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/v1/game/create', ['team_name' => 'Equipo Gamma']);
    $gameId = $response->json('data.id');

    $unexplored = Tile::where('game_id', $gameId)->where('explored', false)->count();
    expect($unexplored)->toBe(225);
});

test('el tablero usa al menos 2 tipos de casilla distintos', function () {
    seedTileTypes();
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/v1/game/create', ['team_name' => 'Equipo Delta']);
    $gameId = $response->json('data.id');

    $distinctTypes = Tile::where('game_id', $gameId)
        ->distinct('tile_type_id')
        ->count('tile_type_id');

    expect($distinctTypes)->toBeGreaterThanOrEqual(2);
});

test('dos partidas distintas generan boards completamente independientes', function () {
    seedTileTypes();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $r1 = $this->actingAs($user1)->postJson('/api/v1/game/create', ['team_name' => 'Equipo Uno']);
    $r2 = $this->actingAs($user2)->postJson('/api/v1/game/create', ['team_name' => 'Equipo Dos']);

    $gameId1 = $r1->json('data.id');
    $gameId2 = $r2->json('data.id');

    expect($gameId1)->not->toBe($gameId2);
    expect(Tile::where('game_id', $gameId1)->count())->toBe(225);
    expect(Tile::where('game_id', $gameId2)->count())->toBe(225);

    $ids1 = Tile::where('game_id', $gameId1)->pluck('id')->toArray();
    $ids2 = Tile::where('game_id', $gameId2)->pluck('id')->toArray();
    expect(array_intersect($ids1, $ids2))->toBeEmpty();
});
