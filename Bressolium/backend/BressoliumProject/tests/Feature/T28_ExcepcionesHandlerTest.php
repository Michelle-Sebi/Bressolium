<?php

use App\Exceptions\ActionLimitExceededException;
use App\Exceptions\InsufficientMaterialsException;
use App\Exceptions\TileAlreadyExploredException;
use App\Exceptions\TileNotExploredException;
use App\Exceptions\UserNotInGameException;
use App\Models\Game;
use App\Models\Tile;
use App\Models\User;
use App\Services\ActionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

// ==========================================
// TEST FOR: TASK 28
// Title: [Refactor] Excepciones Personalizadas y Handler Global
// ==========================================

// ─── Clases de excepción existen en App\Exceptions ────────────────────────────

test('InsufficientMaterialsException existe en App\Exceptions', function () {
    expect(class_exists(InsufficientMaterialsException::class))->toBeTrue();
});

test('ActionLimitExceededException existe en App\Exceptions', function () {
    expect(class_exists(ActionLimitExceededException::class))->toBeTrue();
});

test('TileAlreadyExploredException existe en App\Exceptions', function () {
    expect(class_exists(TileAlreadyExploredException::class))->toBeTrue();
});

test('TileNotExploredException existe en App\Exceptions', function () {
    expect(class_exists(TileNotExploredException::class))->toBeTrue();
});

test('UserNotInGameException existe en App\Exceptions', function () {
    expect(class_exists(UserNotInGameException::class))->toBeTrue();
});

// ─── Cada excepción extiende RuntimeException o Exception ─────────────────────

test('InsufficientMaterialsException extiende RuntimeException', function () {
    expect(is_a(InsufficientMaterialsException::class, RuntimeException::class, true))->toBeTrue();
});

test('ActionLimitExceededException extiende RuntimeException', function () {
    expect(is_a(ActionLimitExceededException::class, RuntimeException::class, true))->toBeTrue();
});

test('TileAlreadyExploredException extiende RuntimeException', function () {
    expect(is_a(TileAlreadyExploredException::class, RuntimeException::class, true))->toBeTrue();
});

test('TileNotExploredException extiende RuntimeException', function () {
    expect(is_a(TileNotExploredException::class, RuntimeException::class, true))->toBeTrue();
});

test('UserNotInGameException extiende RuntimeException', function () {
    expect(is_a(UserNotInGameException::class, RuntimeException::class, true))->toBeTrue();
});

// ─── Cada excepción lleva el código HTTP correcto vía getCode() ────────────────

test('UserNotInGameException devuelve código HTTP 403', function () {
    expect((new UserNotInGameException)->getCode())->toBe(403);
});

test('ActionLimitExceededException devuelve código HTTP 403', function () {
    expect((new ActionLimitExceededException)->getCode())->toBe(403);
});

test('TileAlreadyExploredException devuelve código HTTP 422', function () {
    expect((new TileAlreadyExploredException)->getCode())->toBe(422);
});

test('TileNotExploredException devuelve código HTTP 422', function () {
    expect((new TileNotExploredException)->getCode())->toBe(422);
});

test('InsufficientMaterialsException devuelve código HTTP 400', function () {
    expect((new InsufficientMaterialsException)->getCode())->toBe(400);
});

// ─── ActionService lanza excepciones, no devuelve arrays con status ───────────

test('ActionService::explore no devuelve array con clave status', function () {
    $reflection = new ReflectionClass(ActionService::class);
    $method = $reflection->getMethod('explore');

    // El tipo de retorno debe ser Tile (o no ser array): si declara array, falla.
    $returnType = $method->getReturnType()?->getName();

    expect($returnType)->not->toBe('array');
});

test('ActionService::upgrade no devuelve array con clave status', function () {
    $reflection = new ReflectionClass(ActionService::class);
    $method = $reflection->getMethod('upgrade');

    $returnType = $method->getReturnType()?->getName();

    expect($returnType)->not->toBe('array');
});

// ─── Handler global: excepciones se convierten en JSON con formato estándar ───

describe('handler global convierte excepciones a JSON (requiere DB)', function () {
    uses(RefreshDatabase::class);

    test('UserNotInGameException en explore devuelve 403 JSON con success=false', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $otherUser = User::factory()->create();
        $game = Game::factory()->create();
        $game->users()->attach($otherUser->id);

        $tile = Tile::where('game_id', $game->id)->first();
        if (! $tile) {
            $this->markTestSkipped('No hay casillas en la partida; requiere tablero generado.');
        }

        $response = $this->withToken($token)
            ->postJson("/api/v1/tiles/{$tile->id}/explore");

        $response->assertStatus(403)
            ->assertJson(['success' => false])
            ->assertJsonStructure(['success', 'data', 'error']);
    });

    test('TileAlreadyExploredException en explore devuelve 422 JSON con success=false', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $game = Game::factory()->create();
        $game->users()->attach($user->id);
        $round = $game->rounds()->create(['number' => 1]);
        $round->users()->attach($user->id, ['actions_spent' => 0]);

        $tile = Tile::where('game_id', $game->id)->first();
        if (! $tile) {
            $this->markTestSkipped('No hay casillas en la partida; requiere tablero generado.');
        }

        // Marcar la casilla como ya explorada
        $tile->update(['explored' => true, 'explored_by_player_id' => $user->id]);

        $response = $this->withToken($token)
            ->postJson("/api/v1/tiles/{$tile->id}/explore");

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonStructure(['success', 'data', 'error']);
    });

    test('TileNotExploredException en upgrade devuelve 422 JSON con success=false', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $game = Game::factory()->create();
        $game->users()->attach($user->id);
        $round = $game->rounds()->create(['number' => 1]);
        $round->users()->attach($user->id, ['actions_spent' => 0]);

        $tile = Tile::where('game_id', $game->id)->first();
        if (! $tile) {
            $this->markTestSkipped('No hay casillas en la partida; requiere tablero generado.');
        }

        // Casilla sin explorar
        $tile->update(['explored' => false]);

        $response = $this->withToken($token)
            ->postJson("/api/v1/tiles/{$tile->id}/upgrade");

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonStructure(['success', 'data', 'error']);
    });

    test('ActionLimitExceededException en explore devuelve 403 JSON cuando acciones agotadas', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $game = Game::factory()->create();
        $game->users()->attach($user->id);
        $round = $game->rounds()->create(['number' => 1]);
        // Agotar las acciones del jugador (máximo 2)
        $round->users()->attach($user->id, ['actions_spent' => 2]);

        $tile = Tile::where('game_id', $game->id)->first();
        if (! $tile) {
            $this->markTestSkipped('No hay casillas en la partida; requiere tablero generado.');
        }

        $tile->update(['explored' => false]);

        $response = $this->withToken($token)
            ->postJson("/api/v1/tiles/{$tile->id}/explore");

        $response->assertStatus(403)
            ->assertJson(['success' => false])
            ->assertJsonStructure(['success', 'data', 'error']);
    });

    test('InsufficientMaterialsException en upgrade devuelve 400 JSON con success=false', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $game = Game::factory()->create();
        $game->users()->attach($user->id);
        $round = $game->rounds()->create(['number' => 1]);
        $round->users()->attach($user->id, ['actions_spent' => 0]);

        $tile = Tile::where('game_id', $game->id)->first();
        if (! $tile) {
            $this->markTestSkipped('No hay casillas en la partida; requiere tablero generado.');
        }

        // Casilla explorada pero sin materiales en el inventario para subir de nivel
        $tile->update(['explored' => true, 'explored_by_player_id' => $user->id]);
        // game_material vacío → upgrade fallará por materiales insuficientes

        $response = $this->withToken($token)
            ->postJson("/api/v1/tiles/{$tile->id}/upgrade");

        // 400 si hay nivel siguiente disponible y no hay materiales; si no hay
        // nivel siguiente, puede ser 422 — ambos son error conocido no 500.
        $response->assertJsonStructure(['success', 'data', 'error'])
            ->assertJson(['success' => false]);

        expect($response->status())->toBeIn([400, 422]);
    });
});
