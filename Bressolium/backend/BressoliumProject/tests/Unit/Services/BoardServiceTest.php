<?php

// ==========================================
// TEST FOR: TASK 37 — Cache Service (integración BoardService)
// Validates: el tablero se cachea por game_id usando CacheService.
// ==========================================

use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Services\BoardService;
use App\Services\CacheService;
use Tests\TestCase;

uses(TestCase::class);

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeBoardService($boardRepo = null, $cache = null): BoardService
{
    return new BoardService(
        $boardRepo ?? Mockery::mock(BoardRepositoryInterface::class),
        $cache ?? Mockery::mock(CacheService::class),
    );
}

// ─── getBoardForUser: usa caché ───────────────────────────────────────────────

test('getBoardForUser: usa rememberBoard con el game_id correcto', function () {
    $cache = Mockery::mock(CacheService::class);
    $cache->shouldReceive('rememberBoard')
        ->withArgs(fn ($gameId) => $gameId === 'game-1')
        ->once()
        ->andReturnUsing(fn ($gameId, $cb) => $cb());

    $boardRepo = Mockery::mock(BoardRepositoryInterface::class);
    $boardRepo->shouldReceive('isUserInGame')->with('game-1', 'user-1')->andReturn(true);
    $boardRepo->shouldReceive('getTilesByGameId')->with('game-1')->andReturn(collect([]));

    makeBoardService($boardRepo, $cache)->getBoardForUser('game-1', 'user-1');
});

test('getBoardForUser: no llama al repositorio cuando la caché devuelve el resultado', function () {
    $cachedTiles = collect(['tile-cached']);

    $cache = Mockery::mock(CacheService::class);
    $cache->shouldReceive('rememberBoard')
        ->once()
        ->andReturn($cachedTiles);

    $boardRepo = Mockery::mock(BoardRepositoryInterface::class);
    $boardRepo->shouldReceive('isUserInGame')->andReturn(true);
    $boardRepo->shouldReceive('getTilesByGameId')->never();

    $result = makeBoardService($boardRepo, $cache)->getBoardForUser('game-1', 'user-1');
    expect($result)->toBe($cachedTiles);
});

test('getBoardForUser: lanza Exception sin usar caché si el usuario no pertenece a la partida', function () {
    $cache = Mockery::mock(CacheService::class);
    $cache->shouldReceive('rememberBoard')->never();

    $boardRepo = Mockery::mock(BoardRepositoryInterface::class);
    $boardRepo->shouldReceive('isUserInGame')->andReturn(false);

    expect(fn () => makeBoardService($boardRepo, $cache)->getBoardForUser('game-1', 'user-1'))
        ->toThrow(Exception::class);
});
