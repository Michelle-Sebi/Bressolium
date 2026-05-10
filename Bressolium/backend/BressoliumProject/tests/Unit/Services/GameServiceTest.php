<?php

// ==========================================
// TEST FOR: TASK 29 — Tests Unitarios de Backend
// Service: GameService (mocked dependencies, no DB)
// ==========================================

use App\DTOs\CreateGameDTO;
use App\DTOs\JoinGameDTO;
use App\Models\Game;
use App\Models\Round;
use App\Repositories\Contracts\GameRepositoryInterface;
use App\Repositories\Contracts\RoundRepositoryInterface;
use App\Services\BoardGeneratorService;
use App\Services\GameService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Tests\TestCase;

uses(TestCase::class);

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeGameService(
    $gameRepo = null,
    $roundRepo = null,
    $boardGen = null,
): GameService {
    return new GameService(
        $gameRepo ?? Mockery::mock(GameRepositoryInterface::class),
        $roundRepo ?? Mockery::mock(RoundRepositoryInterface::class),
        $boardGen ?? Mockery::mock(BoardGeneratorService::class),
    );
}

/** Crea un mock de BelongsToMany (tipo requerido por Game::users() y Round::users()). */
function btmMock(): MockInterface
{
    return Mockery::mock(BelongsToMany::class);
}

/** Crea un mock de Game con users() preconfigurado para attach simple. */
function mockGame(string $id = 'game-uuid'): MockInterface
{
    $rel = btmMock();
    $rel->shouldReceive('attach')->andReturn(null);

    $game = Mockery::mock(Game::class);
    $game->shouldReceive('getAttribute')->with('id')->andReturn($id);
    $game->shouldReceive('users')->andReturn($rel);

    return $game;
}

/** Crea un mock de Round con users() preconfigurado para attach simple. */
function mockRoundWithUsers(): MockInterface
{
    $rel = btmMock();
    $rel->shouldReceive('attach')->andReturn(null);

    $round = Mockery::mock(Round::class);
    $round->shouldReceive('users')->andReturn($rel);

    return $round;
}

// ─── createGame ───────────────────────────────────────────────────────────────

test('createGame: llama al repo con name y status WAITING', function () {
    $dto = new CreateGameDTO(teamName: 'Los Vikingos', userId: 'user-1');

    $gameRepo = Mockery::mock(GameRepositoryInterface::class);
    $gameRepo->shouldReceive('create')
        ->with(['name' => 'Los Vikingos', 'status' => 'WAITING'])
        ->once()
        ->andReturn(mockGame());
    $gameRepo->shouldReceive('initializeMaterials')->once()->andReturn(null);

    $roundRepo = Mockery::mock(RoundRepositoryInterface::class);
    $roundRepo->shouldReceive('create')->andReturn(mockRoundWithUsers());

    $boardGen = Mockery::mock(BoardGeneratorService::class);
    $boardGen->shouldReceive('generate')->andReturn(null);
    $boardGen->shouldReceive('assignStartingTile')->andReturn(null);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    $result = makeGameService($gameRepo, $roundRepo, $boardGen)->createGame($dto);
    expect($result)->toBeInstanceOf(Game::class);
});

test('createGame: crea ronda número 1 con el game_id correcto', function () {
    $dto = new CreateGameDTO(teamName: 'Equipo A', userId: 'user-1');
    $game = mockGame('gid-123');

    $gameRepo = Mockery::mock(GameRepositoryInterface::class);
    $gameRepo->shouldReceive('create')->andReturn($game);
    $gameRepo->shouldReceive('initializeMaterials')->once()->andReturn(null);

    $roundRepo = Mockery::mock(RoundRepositoryInterface::class);
    $roundRepo->shouldReceive('create')
        ->withArgs(fn ($data) => $data['game_id'] === 'gid-123' && $data['number'] === 1)
        ->once()
        ->andReturn(mockRoundWithUsers());

    $boardGen = Mockery::mock(BoardGeneratorService::class);
    $boardGen->shouldReceive('generate')->andReturn(null);
    $boardGen->shouldReceive('assignStartingTile')->andReturn(null);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    makeGameService($gameRepo, $roundRepo, $boardGen)->createGame($dto);
});

test('createGame: genera el tablero con el game_id correcto', function () {
    $dto = new CreateGameDTO(teamName: 'X', userId: 'user-1');
    $game = mockGame('gid-abc');

    $gameRepo = Mockery::mock(GameRepositoryInterface::class);
    $gameRepo->shouldReceive('create')->andReturn($game);
    $gameRepo->shouldReceive('initializeMaterials')->once()->andReturn(null);

    $roundRepo = Mockery::mock(RoundRepositoryInterface::class);
    $roundRepo->shouldReceive('create')->andReturn(mockRoundWithUsers());

    $boardGen = Mockery::mock(BoardGeneratorService::class);
    $boardGen->shouldReceive('generate')->with('gid-abc')->once()->andReturn(null);
    $boardGen->shouldReceive('assignStartingTile')->andReturn(null);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    makeGameService($gameRepo, $roundRepo, $boardGen)->createGame($dto);
});

// ─── joinGame ─────────────────────────────────────────────────────────────────

test('joinGame: lanza Exception cuando la partida no existe', function () {
    $dto = new JoinGameDTO(teamName: 'NoExiste', userId: 'user-1');

    $gameRepo = Mockery::mock(GameRepositoryInterface::class);
    $gameRepo->shouldReceive('findByName')->with('NoExiste')->andReturn(null);

    expect(fn () => makeGameService($gameRepo)->joinGame($dto))
        ->toThrow(Exception::class, 'Game not found');
});

test('joinGame: lanza Exception cuando la partida está llena (5 usuarios)', function () {
    $dto = new JoinGameDTO(teamName: 'Full', userId: 'user-1');

    $rel = btmMock();
    $rel->shouldReceive('count')->andReturn(5);

    $game = Mockery::mock(Game::class);
    $game->shouldReceive('users')->andReturn($rel);

    $gameRepo = Mockery::mock(GameRepositoryInterface::class);
    $gameRepo->shouldReceive('findByName')->andReturn($game);

    expect(fn () => makeGameService($gameRepo)->joinGame($dto))
        ->toThrow(Exception::class, 'Game is full');
});

test('joinGame: adjunta al usuario si no pertenece ya a la partida', function () {
    $dto = new JoinGameDTO(teamName: 'Open', userId: 'user-new');

    $rel = btmMock();
    $rel->shouldReceive('count')->andReturn(2);
    $rel->shouldReceive('where')->with('user_id', 'user-new')->andReturnSelf();
    $rel->shouldReceive('exists')->andReturn(false);
    $rel->shouldReceive('attach')->with('user-new', ['is_afk' => false])->once();

    $game = Mockery::mock(Game::class);
    $game->shouldReceive('getAttribute')->with('id')->andReturn('gid-open');
    $game->shouldReceive('users')->andReturn($rel);

    $roundRel = btmMock();
    $roundRel->shouldReceive('attach')->with('user-new', ['actions_spent' => 0])->once();
    $round = Mockery::mock(Round::class);
    $round->shouldReceive('users')->andReturn($roundRel);

    $gameRepo = Mockery::mock(GameRepositoryInterface::class);
    $gameRepo->shouldReceive('findByName')->andReturn($game);

    $roundRepo = Mockery::mock(RoundRepositoryInterface::class);
    $roundRepo->shouldReceive('getLatestRoundForGame')->with('gid-open')->andReturn($round);

    $boardGen = Mockery::mock(BoardGeneratorService::class);
    $boardGen->shouldReceive('assignStartingTile')->andReturn(null);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    $result = makeGameService($gameRepo, $roundRepo, $boardGen)->joinGame($dto);
    expect($result)->toBe($game);
});

test('joinGame: NO adjunta al usuario si ya está en la partida', function () {
    $dto = new JoinGameDTO(teamName: 'Already', userId: 'user-existing');

    $rel = btmMock();
    $rel->shouldReceive('count')->andReturn(2);
    $rel->shouldReceive('where')->with('user_id', 'user-existing')->andReturnSelf();
    $rel->shouldReceive('exists')->andReturn(true);
    $rel->shouldReceive('attach')->never();

    $game = Mockery::mock(Game::class);
    $game->shouldReceive('getAttribute')->with('id')->andReturn('gid-x');
    $game->shouldReceive('users')->andReturn($rel);

    $gameRepo = Mockery::mock(GameRepositoryInterface::class);
    $gameRepo->shouldReceive('findByName')->andReturn($game);

    $roundRepo = Mockery::mock(RoundRepositoryInterface::class);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    makeGameService($gameRepo, $roundRepo)->joinGame($dto);
});

// ─── joinRandomGame ───────────────────────────────────────────────────────────

test('joinRandomGame: lanza Exception cuando no hay partidas disponibles', function () {
    $gameRepo = Mockery::mock(GameRepositoryInterface::class);
    $gameRepo->shouldReceive('findAvailableRandom')->andReturn(null);

    expect(fn () => makeGameService($gameRepo)->joinRandomGame('user-1'))
        ->toThrow(Exception::class, 'No games available');
});

test('joinRandomGame: adjunta al usuario a la partida disponible', function () {
    $rel = btmMock();
    $rel->shouldReceive('where')->andReturnSelf();
    $rel->shouldReceive('exists')->andReturn(false);
    $rel->shouldReceive('attach')->with('user-rand', ['is_afk' => false])->once();
    $rel->shouldReceive('count')->andReturn(1);

    $game = Mockery::mock(Game::class);
    $game->shouldReceive('getAttribute')->with('id')->andReturn('gid-rand');
    $game->shouldReceive('users')->andReturn($rel);

    $roundRel = btmMock();
    $roundRel->shouldReceive('attach')->once();
    $round = Mockery::mock(Round::class);
    $round->shouldReceive('users')->andReturn($roundRel);

    $gameRepo = Mockery::mock(GameRepositoryInterface::class);
    $gameRepo->shouldReceive('findAvailableRandom')->andReturn($game);

    $roundRepo = Mockery::mock(RoundRepositoryInterface::class);
    $roundRepo->shouldReceive('getLatestRoundForGame')->andReturn($round);

    $boardGen = Mockery::mock(BoardGeneratorService::class);
    $boardGen->shouldReceive('assignStartingTile')->andReturn(null);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    $result = makeGameService($gameRepo, $roundRepo, $boardGen)->joinRandomGame('user-rand');
    expect($result)->toBe($game);
});

// ─── getMyGames / getAllGames ──────────────────────────────────────────────────

test('getMyGames: delega en el repositorio con el userId', function () {
    $collection = collect([]);

    $gameRepo = Mockery::mock(GameRepositoryInterface::class);
    $gameRepo->shouldReceive('getGamesByUserId')->with('user-1')->once()->andReturn($collection);

    $result = makeGameService($gameRepo)->getMyGames('user-1');
    expect($result)->toBe($collection);
});

test('getAllGames: delega en el repositorio', function () {
    $collection = collect([]);

    $gameRepo = Mockery::mock(GameRepositoryInterface::class);
    $gameRepo->shouldReceive('getAllAvailableGames')->once()->andReturn($collection);

    $result = makeGameService($gameRepo)->getAllGames();
    expect($result)->toBe($collection);
});
