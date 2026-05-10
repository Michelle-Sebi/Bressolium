<?php

// ==========================================
// TEST FOR: TASK 37 — Cache Service (integración SyncService)
// Validates: el sync se cachea por game_id usando CacheService.
// ==========================================

use App\Models\Game;
use App\Repositories\Contracts\SyncRepositoryInterface;
use App\Services\CacheService;
use App\Services\SyncService;
use Tests\TestCase;

uses(TestCase::class);

// ─── Helpers ──────────────────────────────────────────────────────────────────

function mockSyncGame(string $id = 'game-1'): Game
{
    $game = Mockery::mock(Game::class);
    $game->shouldReceive('getAttribute')->with('id')->andReturn($id);

    return $game;
}

function makeSyncService($syncRepo = null, $cache = null): SyncService
{
    return new SyncService(
        $syncRepo ?? Mockery::mock(SyncRepositoryInterface::class),
        $cache ?? Mockery::mock(CacheService::class),
    );
}

// ─── sync: usa caché ──────────────────────────────────────────────────────────

test('sync: usa rememberSync con el game_id del modelo', function () {
    $game = mockSyncGame('game-42');

    $cache = Mockery::mock(CacheService::class);
    $cache->shouldReceive('rememberSync')
        ->withArgs(fn ($gameId) => $gameId === 'game-42')
        ->once()
        ->andReturnUsing(fn ($_gameId, $_userId, $cb) => $cb());

    $syncRepo = Mockery::mock(SyncRepositoryInterface::class);
    $syncRepo->shouldReceive('getCurrentRound')->andReturn(null);
    $syncRepo->shouldReceive('getInventory')->andReturn([]);
    $syncRepo->shouldReceive('getTechnologies')->andReturn([]);
    $syncRepo->shouldReceive('getInventions')->andReturn([]);

    makeSyncService($syncRepo, $cache)->sync($game, 'user-1');
});

test('sync: no llama al repositorio cuando la caché devuelve el resultado', function () {
    $game = mockSyncGame('game-1');
    $cachedDto = new \App\DTOs\SyncResponseDTO(
        currentRound: [],
        userActions: [],
        inventory: [],
        technologies: [],
        inventions: [],
    );

    $cache = Mockery::mock(CacheService::class);
    $cache->shouldReceive('rememberSync')
        ->once()
        ->andReturn($cachedDto);

    $syncRepo = Mockery::mock(SyncRepositoryInterface::class);
    $syncRepo->shouldReceive('getCurrentRound')->never();
    $syncRepo->shouldReceive('getInventory')->never();
    $syncRepo->shouldReceive('getTechnologies')->never();
    $syncRepo->shouldReceive('getInventions')->never();

    $result = makeSyncService($syncRepo, $cache)->sync($game, 'user-1');
    expect($result)->toBe($cachedDto);
});

test('sync: usa el userId como parte de la clave de caché', function () {
    $game = mockSyncGame('game-1');
    $capturedUserId = null;

    $cache = Mockery::mock(CacheService::class);
    $cache->shouldReceive('rememberSync')
        ->withArgs(function ($_gameId, $userId) use (&$capturedUserId) {
            $capturedUserId = $userId;

            return true;
        })
        ->once()
        ->andReturnUsing(fn ($_gameId, $_userId, $cb) => $cb());

    $syncRepo = Mockery::mock(SyncRepositoryInterface::class);
    $syncRepo->shouldReceive('getCurrentRound')->andReturn(null);
    $syncRepo->shouldReceive('getInventory')->andReturn([]);
    $syncRepo->shouldReceive('getTechnologies')->andReturn([]);
    $syncRepo->shouldReceive('getInventions')->andReturn([]);

    makeSyncService($syncRepo, $cache)->sync($game, 'user-abc');

    expect($capturedUserId)->toBe('user-abc');
});
