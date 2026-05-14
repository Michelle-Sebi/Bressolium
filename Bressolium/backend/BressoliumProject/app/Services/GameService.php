<?php

/**
 * @module GameService
 *
 * @description Servicio para orquestar la creación y unión a partidas (equipos).
 */

namespace App\Services;

use App\DTOs\CreateGameDTO;
use App\DTOs\JoinGameDTO;
use App\Jobs\ExpireRoundJob;
use App\Models\Game;
use App\Repositories\Contracts\GameRepositoryInterface;
use App\Repositories\Contracts\RoundRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class GameService
{
    protected $gameRepository;

    protected $roundRepository;

    protected $boardGenerator;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        RoundRepositoryInterface $roundRepository,
        BoardGeneratorService $boardGenerator
    ) {
        $this->gameRepository = $gameRepository;
        $this->roundRepository = $roundRepository;
        $this->boardGenerator = $boardGenerator;
    }

    /**
     * Crea un equipo y su primera ronda inicial.
     *
     * @throws Exception
     */
    public function createGame(CreateGameDTO $dto): Game
    {
        return DB::transaction(function () use ($dto) {
            $game = $this->gameRepository->create([
                'name' => $dto->teamName,
                'status' => 'WAITING',
            ]);

            $game->users()->attach($dto->userId, ['is_afk' => false]);

            $round = $this->roundRepository->create([
                'game_id' => $game->id,
                'number' => 1,
                'start_date' => now(),
            ]);

            $round->users()->attach($dto->userId, ['actions_spent' => 0]);

            $this->boardGenerator->generate($game->id);
            $this->boardGenerator->assignStartingTile($game->id, $dto->userId, 0);
            $this->gameRepository->initializeMaterials($game);

            ExpireRoundJob::dispatch($round->id, $game->id)->afterCommit()->delay(now()->addHours(2));

            return $game;
        });
    }

    /**
     * Une a un usuario a un equipo por su nombre.
     *
     * @throws Exception
     */
    public function joinGame(JoinGameDTO $dto): Game
    {
        $game = $this->gameRepository->findByName($dto->teamName);

        if (! $game) {
            throw new Exception('Game not found');
        }

        if ($game->users()->count() >= 5) {
            throw new Exception('Game is full');
        }

        return DB::transaction(function () use ($game, $dto) {
            if (! $game->users()->where('user_id', $dto->userId)->exists()) {
                $game->users()->attach($dto->userId, ['is_afk' => false]);

                $playerIndex = $game->users()->count() - 1;
                $this->boardGenerator->assignStartingTile($game->id, $dto->userId, $playerIndex);

                $latestRound = $this->roundRepository->getLatestRoundForGame($game->id);
                if ($latestRound) {
                    $latestRound->users()->attach($dto->userId, ['actions_spent' => 0]);
                }

                if ($game->users()->count() >= 5) {
                    $game->update(['status' => 'COMPLETA']);
                }
            }

            return $game;
        });
    }

    /**
     * Une a un usuario a una partida aleatoria disponible.
     *
     * @throws Exception
     */
    public function joinRandomGame(string $userId): Game
    {
        $game = $this->gameRepository->findAvailableRandom();

        if (! $game) {
            throw new Exception('No games available');
        }

        return DB::transaction(function () use ($game, $userId) {
            if (! $game->users()->where('user_id', $userId)->exists()) {
                $game->users()->attach($userId, ['is_afk' => false]);

                $playerIndex = $game->users()->count() - 1;
                $this->boardGenerator->assignStartingTile($game->id, $userId, $playerIndex);

                $latestRound = $this->roundRepository->getLatestRoundForGame($game->id);
                if ($latestRound) {
                    $latestRound->users()->attach($userId, ['actions_spent' => 0]);
                }

                if ($game->users()->count() >= 5) {
                    $game->update(['status' => 'COMPLETA']);
                }
            }

            return $game;
        });
    }

    /**
     * Abandona una partida.
     *
     * @throws Exception
     */
    public function leaveGame(string $gameId, string $userId): void
    {
        $game = Game::find($gameId);

        if (! $game) {
            throw new Exception('Game not found');
        }

        if (! $game->users()->where('user_id', $userId)->exists()) {
            throw new Exception('Not in game');
        }

        $game->users()->detach($userId);

        $remaining = $game->users()->count();

        if ($remaining === 0) {
            $game->delete();
        } elseif ($remaining < 5 && $game->status === 'COMPLETA') {
            $game->update(['status' => 'WAITING']);
        }
    }

    /**
     * Obtiene las partidas en las que participa el usuario.
     */
    public function getMyGames(string $userId)
    {
        return $this->gameRepository->getGamesByUserId($userId);
    }

    /**
     * Obtiene todas las partidas a las que se puede unir.
     */
    public function getAllGames()
    {
        return $this->gameRepository->getAllAvailableGames();
    }
}
