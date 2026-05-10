<?php

// ==========================================
// TEST FOR: TASK 37 — Cache Service
// Validates: CacheService centraliza guardar/recuperar/invalidar,
//            tablero y sync cacheados por game_id,
//            caché invalidada al explorar o mejorar una casilla.
// ==========================================

use App\Services\CacheService;
use Illuminate\Cache\Repository;
use Tests\TestCase;

uses(TestCase::class);

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeCache(?Repository $store = null): CacheService
{
    return new CacheService($store ?? Mockery::mock(Repository::class));
}

// ─── rememberBoard: miss ──────────────────────────────────────────────────────

test('rememberBoard: llama al callback y devuelve su valor cuando la caché está vacía', function () {
    $store = Mockery::mock(Repository::class);
    $store->shouldReceive('remember')
        ->once()
        ->andReturnUsing(fn ($key, $ttl, $cb) => $cb());

    $called = false;
    $result = makeCache($store)->rememberBoard('game-1', function () use (&$called) {
        $called = true;

        return ['tiles' => []];
    });

    expect($called)->toBeTrue()
        ->and($result)->toBe(['tiles' => []]);
});

// ─── rememberBoard: hit ───────────────────────────────────────────────────────

test('rememberBoard: devuelve el valor cacheado sin ejecutar el callback en un hit', function () {
    $store = Mockery::mock(Repository::class);
    $store->shouldReceive('remember')
        ->once()
        ->andReturn(['tiles' => ['cached']]);

    $called = false;
    $result = makeCache($store)->rememberBoard('game-1', function () use (&$called) {
        $called = true;

        return [];
    });

    expect($called)->toBeFalse()
        ->and($result)->toBe(['tiles' => ['cached']]);
});

// ─── rememberBoard: clave incluye game_id ────────────────────────────────────

test('rememberBoard: la clave de caché contiene el game_id', function () {
    $store = Mockery::mock(Repository::class);
    $store->shouldReceive('remember')
        ->withArgs(fn ($key) => str_contains($key, 'game-abc'))
        ->once()
        ->andReturn([]);

    makeCache($store)->rememberBoard('game-abc', fn () => []);
});

test('rememberBoard: claves de games distintos no colisionan', function () {
    $seenKeys = [];

    $store = Mockery::mock(Repository::class);
    $store->shouldReceive('remember')
        ->twice()
        ->andReturnUsing(function ($key) use (&$seenKeys) {
            $seenKeys[] = $key;

            return [];
        });

    makeCache($store)->rememberBoard('game-1', fn () => []);
    makeCache($store)->rememberBoard('game-2', fn () => []);

    expect($seenKeys[0])->not->toBe($seenKeys[1]);
});

// ─── rememberSync: miss ───────────────────────────────────────────────────────

test('rememberSync: llama al callback y devuelve su valor cuando la caché está vacía', function () {
    $store = Mockery::mock(Repository::class);
    $store->shouldReceive('remember')
        ->once()
        ->andReturnUsing(fn ($key, $ttl, $cb) => $cb());

    $called = false;
    $result = makeCache($store)->rememberSync('game-1', 'user-1', function () use (&$called) {
        $called = true;

        return ['round' => 1];
    });

    expect($called)->toBeTrue()
        ->and($result)->toBe(['round' => 1]);
});

// ─── rememberSync: hit ────────────────────────────────────────────────────────

test('rememberSync: devuelve el valor cacheado sin ejecutar el callback en un hit', function () {
    $store = Mockery::mock(Repository::class);
    $store->shouldReceive('remember')
        ->once()
        ->andReturn(['round' => 2]);

    $called = false;
    $result = makeCache($store)->rememberSync('game-1', 'user-1', function () use (&$called) {
        $called = true;

        return [];
    });

    expect($called)->toBeFalse()
        ->and($result)->toBe(['round' => 2]);
});

// ─── rememberSync: clave incluye game_id ─────────────────────────────────────

test('rememberSync: la clave de caché contiene el game_id', function () {
    $store = Mockery::mock(Repository::class);
    $store->shouldReceive('remember')
        ->withArgs(fn ($key) => str_contains($key, 'game-xyz'))
        ->once()
        ->andReturn([]);

    makeCache($store)->rememberSync('game-xyz', 'user-1', fn () => []);
});

test('rememberSync: el mismo game con distintos usuarios genera claves distintas', function () {
    $seenKeys = [];

    $store = Mockery::mock(Repository::class);
    $store->shouldReceive('remember')
        ->twice()
        ->andReturnUsing(function ($key) use (&$seenKeys) {
            $seenKeys[] = $key;

            return [];
        });

    makeCache($store)->rememberSync('game-1', 'user-A', fn () => []);
    makeCache($store)->rememberSync('game-1', 'user-B', fn () => []);

    expect($seenKeys[0])->not->toBe($seenKeys[1]);
});

// ─── invalidateBoard ──────────────────────────────────────────────────────────

test('invalidateBoard: elimina la clave de caché del tablero para el game_id dado', function () {
    $store = Mockery::mock(Repository::class);
    $store->shouldReceive('forget')
        ->withArgs(fn ($key) => str_contains($key, 'game-1'))
        ->once();

    makeCache($store)->invalidateBoard('game-1');
});

test('invalidateBoard: la clave eliminada contiene el game_id correcto', function () {
    $capturedKey = null;

    $store = Mockery::mock(Repository::class);
    $store->shouldReceive('forget')
        ->once()
        ->andReturnUsing(function ($key) use (&$capturedKey) {
            $capturedKey = $key;
        });

    makeCache($store)->invalidateBoard('game-99');

    expect($capturedKey)->toContain('game-99');
});

test('invalidateBoard: no elimina la caché de un game_id diferente', function () {
    $store = Mockery::mock(Repository::class);
    $store->shouldReceive('forget')
        ->withArgs(fn ($key) => ! str_contains($key, 'game-2'))
        ->once();

    makeCache($store)->invalidateBoard('game-1');
});
