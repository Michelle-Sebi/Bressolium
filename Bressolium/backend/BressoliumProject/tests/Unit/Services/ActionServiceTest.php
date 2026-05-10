<?php

// ==========================================
// TEST FOR: TASK 29 — Tests Unitarios de Backend
// Service: ActionService (mocked TileRepository)
// Nota: upgrade() llama Game::find() internamente (acoplamiento al ORM),
// por lo que los tests que llegan a esa línea usan RefreshDatabase.
// ==========================================

use App\DTOs\ExploreActionDTO;
use App\Services\CacheService;
use App\DTOs\UpgradeActionDTO;
use App\Exceptions\ActionLimitExceededException;
use App\Exceptions\InsufficientMaterialsException;
use App\Exceptions\TileAlreadyExploredException;
use App\Exceptions\TileNotAdjacentException;
use App\Exceptions\TileNotExploredException;
use App\Exceptions\UserNotInGameException;
use App\Models\Game;
use App\Models\Round;
use App\Models\Tile;
use App\Models\TileType;
use App\Repositories\Contracts\TileRepositoryInterface;
use App\Services\ActionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// ─── Helpers ──────────────────────────────────────────────────────────────────

function mockTileObj(string $gameId = 'game-1', bool $explored = false): MockInterface
{
    $tile = Mockery::mock(Tile::class);
    $tile->shouldReceive('getAttribute')->with('game_id')->andReturn($gameId);
    $tile->shouldReceive('getAttribute')->with('explored')->andReturn($explored);
    $tile->shouldReceive('getAttribute')->with('type')->andReturn(null);
    $tile->shouldReceive('refresh')->andReturnSelf();
    $tile->shouldReceive('load')->andReturnSelf();

    return $tile;
}

function makeAction($tileRepo, $cache = null): ActionService
{
    if ($cache === null) {
        $cache = Mockery::mock(CacheService::class);
        $cache->shouldReceive('invalidateBoard')->andReturn(null);
    }

    return new ActionService($tileRepo, $cache);
}

// ─── explore: excepciones ─────────────────────────────────────────────────────

test('explore: lanza UserNotInGameException si el usuario no pertenece a la partida', function () {
    $dto = new ExploreActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj('game-1');

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->with('tile-1')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->with('user-1', 'game-1')->andReturn(false);

    expect(fn () => makeAction($repo)->explore($dto))
        ->toThrow(UserNotInGameException::class);
});

test('explore: lanza ActionLimitExceededException cuando actions_spent >= 2', function () {
    $dto = new ExploreActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj('game-1');
    $round = Mockery::mock(Round::class);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(2);

    expect(fn () => makeAction($repo)->explore($dto))
        ->toThrow(ActionLimitExceededException::class);
});

test('explore: lanza TileAlreadyExploredException si la casilla ya fue explorada', function () {
    $dto = new ExploreActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj('game-1', explored: true);
    $round = Mockery::mock(Round::class);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(0);

    expect(fn () => makeAction($repo)->explore($dto))
        ->toThrow(TileAlreadyExploredException::class);
});

// ─── explore: camino feliz ────────────────────────────────────────────────────

test('explore: llama a markExplored e incrementActionsSpent en el camino feliz', function () {
    $dto = new ExploreActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj('game-1', explored: false);
    $round = Mockery::mock(Round::class);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(0);
    $repo->shouldReceive('isAdjacentToUserExplored')->with($tile, 'user-1')->andReturn(true);
    $repo->shouldReceive('markExplored')->with($tile, 'user-1')->once();
    $repo->shouldReceive('incrementActionsSpent')->with($round, 'user-1')->once();

    $result = makeAction($repo)->explore($dto);
    expect($result)->toBe($tile);
});

test('explore: lanza TileNotAdjacentException si la casilla no es adyacente al territorio del jugador', function () {
    $dto = new ExploreActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj('game-1', explored: false);
    $round = Mockery::mock(Round::class);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(0);
    $repo->shouldReceive('isAdjacentToUserExplored')->with($tile, 'user-1')->andReturn(false);

    expect(fn () => makeAction($repo)->explore($dto))
        ->toThrow(TileNotAdjacentException::class);
});

// ─── upgrade: excepciones antes de Game::find ────────────────────────────────

test('upgrade: lanza UserNotInGameException si el usuario no pertenece a la partida', function () {
    $dto = new UpgradeActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj('game-1', explored: true);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(false);

    expect(fn () => makeAction($repo)->upgrade($dto))
        ->toThrow(UserNotInGameException::class);
});

test('upgrade: lanza ActionLimitExceededException cuando actions_spent >= 2', function () {
    $dto = new UpgradeActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj('game-1', explored: true);
    $round = Mockery::mock(Round::class);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(2);

    expect(fn () => makeAction($repo)->upgrade($dto))
        ->toThrow(ActionLimitExceededException::class);
});

test('upgrade: lanza TileNotExploredException si la casilla no fue explorada', function () {
    $dto = new UpgradeActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj('game-1', explored: false);
    $round = Mockery::mock(Round::class);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(0);

    expect(fn () => makeAction($repo)->upgrade($dto))
        ->toThrow(TileNotExploredException::class);
});

test('upgrade: lanza TileNotExploredException si no hay siguiente nivel de mejora', function () {
    $dto = new UpgradeActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj('game-1', explored: true);
    $round = Mockery::mock(Round::class);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(0);
    $repo->shouldReceive('findNextTileType')->andReturn(null);

    expect(fn () => makeAction($repo)->upgrade($dto))
        ->toThrow(TileNotExploredException::class);
});

// ─── upgrade: tecnología requerida (necesita DB para Game::find) ─────────────

test('upgrade: lanza TechnologyRequiredException si la tecnología requerida no está activa', function () {
    $game = Game::factory()->create();

    $dto = new UpgradeActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj($game->id, explored: true);
    $round = Mockery::mock(Round::class);
    $nextType = Mockery::mock(TileType::class);
    $requiredTech = Mockery::mock(\App\Models\Technology::class);
    $requiredTech->shouldReceive('getAttribute')->with('id')->andReturn('tech-required');
    $requiredTech->shouldReceive('getAttribute')->with('name')->andReturn('Escritura');

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(0);
    $repo->shouldReceive('findNextTileType')->andReturn($nextType);
    $repo->shouldReceive('getRequiredTechnology')->andReturn($requiredTech);

    expect(fn () => makeAction($repo)->upgrade($dto))
        ->toThrow(\App\Exceptions\TechnologyRequiredException::class);
});

test('upgrade: lanza InsufficientMaterialsException si no hay materiales suficientes', function () {
    $game = Game::factory()->create();

    $dto = new UpgradeActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj($game->id, explored: true);
    $round = Mockery::mock(Round::class);
    $nextType = Mockery::mock(TileType::class);
    $costs = collect([]);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(0);
    $repo->shouldReceive('findNextTileType')->andReturn($nextType);
    $repo->shouldReceive('getRequiredTechnology')->andReturn(null);
    $repo->shouldReceive('getUpgradeCosts')->andReturn($costs);
    $repo->shouldReceive('hasSufficientMaterials')->andReturn(false);

    expect(fn () => makeAction($repo)->upgrade($dto))
        ->toThrow(InsufficientMaterialsException::class);
});

test('upgrade: mejora la casilla e incrementa acciones en camino feliz sin tecnología requerida', function () {
    $game = Game::factory()->create();

    $dto = new UpgradeActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj($game->id, explored: true);
    $round = Mockery::mock(Round::class);
    $nextType = Mockery::mock(TileType::class);
    $costs = collect([]);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(0);
    $repo->shouldReceive('findNextTileType')->andReturn($nextType);
    $repo->shouldReceive('getRequiredTechnology')->andReturn(null);
    $repo->shouldReceive('getUpgradeCosts')->andReturn($costs);
    $repo->shouldReceive('hasSufficientMaterials')->andReturn(true);
    $repo->shouldReceive('deductMaterials')->once();
    $repo->shouldReceive('upgradeTile')->with($tile, $nextType)->once();
    $repo->shouldReceive('incrementActionsSpent')->with($round, 'user-1')->once();

    $result = makeAction($repo)->upgrade($dto);
    expect($result)->toBe($tile);
});

// ─── T37: invalidación de caché ───────────────────────────────────────────────

test('explore: invalida la caché del tablero con el game_id correcto en el camino feliz', function () {
    $dto = new ExploreActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj('game-cache-test', explored: false);
    $round = Mockery::mock(Round::class);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(0);
    $repo->shouldReceive('isAdjacentToUserExplored')->andReturn(true);
    $repo->shouldReceive('markExplored')->once();
    $repo->shouldReceive('incrementActionsSpent')->once();

    $cache = Mockery::mock(CacheService::class);
    $cache->shouldReceive('invalidateBoard')->with('game-cache-test')->once();

    makeAction($repo, $cache)->explore($dto);
});

test('explore: no invalida la caché si la exploración falla por límite de acciones', function () {
    $dto = new ExploreActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj('game-1', explored: false);
    $round = Mockery::mock(Round::class);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(2);

    $cache = Mockery::mock(CacheService::class);
    $cache->shouldReceive('invalidateBoard')->never();

    expect(fn () => makeAction($repo, $cache)->explore($dto))
        ->toThrow(ActionLimitExceededException::class);
});

test('upgrade: invalida la caché del tablero con el game_id correcto en el camino feliz', function () {
    $game = Game::factory()->create();

    $dto = new UpgradeActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj($game->id, explored: true);
    $round = Mockery::mock(Round::class);
    $nextType = Mockery::mock(TileType::class);
    $costs = collect([]);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(0);
    $repo->shouldReceive('findNextTileType')->andReturn($nextType);
    $repo->shouldReceive('getRequiredTechnology')->andReturn(null);
    $repo->shouldReceive('getUpgradeCosts')->andReturn($costs);
    $repo->shouldReceive('hasSufficientMaterials')->andReturn(true);
    $repo->shouldReceive('deductMaterials')->once();
    $repo->shouldReceive('upgradeTile')->with($tile, $nextType)->once();
    $repo->shouldReceive('incrementActionsSpent')->with($round, 'user-1')->once();

    $cache = Mockery::mock(CacheService::class);
    $cache->shouldReceive('invalidateBoard')->with($game->id)->once();

    makeAction($repo, $cache)->upgrade($dto);
});

test('upgrade: no invalida la caché si no hay siguiente nivel de mejora', function () {
    $dto = new UpgradeActionDTO(tileId: 'tile-1', userId: 'user-1');
    $tile = mockTileObj('game-1', explored: true);
    $round = Mockery::mock(Round::class);

    $repo = Mockery::mock(TileRepositoryInterface::class);
    $repo->shouldReceive('find')->andReturn($tile);
    $repo->shouldReceive('isUserInGame')->andReturn(true);
    $repo->shouldReceive('getCurrentRound')->andReturn($round);
    $repo->shouldReceive('getActionsSpent')->andReturn(0);
    $repo->shouldReceive('findNextTileType')->andReturn(null);

    $cache = Mockery::mock(CacheService::class);
    $cache->shouldReceive('invalidateBoard')->never();

    expect(fn () => makeAction($repo, $cache)->upgrade($dto))
        ->toThrow(TileNotExploredException::class);
});
