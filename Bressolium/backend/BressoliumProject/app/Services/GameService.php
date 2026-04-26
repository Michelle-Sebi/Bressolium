<?php
/**
 * @module GameService
 * @description Servicio para orquestar la creación y unión a partidas (equipos).
 */

namespace App\Services;

use App\Repositories\Contracts\GameRepositoryInterface;
use App\Repositories\Contracts\RoundRepositoryInterface;
use App\Models\Game;
use App\Services\BoardGeneratorService;
use Illuminate\Support\Facades\DB;
use Exception;

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
     * @param string $teamName
     * @param string $userId
     * @return Game
     * @throws Exception
     */
    public function createGame(string $teamName, string $userId): Game
    {
        return DB::transaction(function () use ($teamName, $userId) {
            $game = $this->gameRepository->create([
                'name' => $teamName,
                'status' => 'WAITING'
            ]);

            // Vincular usuario al juego
            $game->users()->attach($userId, ['is_afk' => false]);

            // Crear Ronda 1
            $round = $this->roundRepository->create([
                'game_id' => $game->id,
                'number' => 1,
                'start_date' => now(),
            ]);

            // Vincular usuario a la ronda
            $round->users()->attach($userId, ['actions_spent' => 0]);

            // Generar tablero 15×15
            $this->boardGenerator->generate($game->id);

            return $game;
        });
    }

    /**
     * Une a un usuario a un equipo por su nombre.
     * 
     * @param string $teamName
     * @param string $userId
     * @return Game
     * @throws Exception
     */
    public function joinGame(string $teamName, string $userId): Game
    {
        $game = $this->gameRepository->findByName($teamName);

        if (!$game) {
            throw new Exception('Game not found');
        }

        if ($game->users()->count() >= 5) {
            throw new Exception('Game is full');
        }

        return DB::transaction(function () use ($game, $userId) {
            if (!$game->users()->where('user_id', $userId)->exists()) {
                $game->users()->attach($userId, ['is_afk' => false]);
                
                $latestRound = $this->roundRepository->getLatestRoundForGame($game->id);
                if ($latestRound) {
                    $latestRound->users()->attach($userId, ['actions_spent' => 0]);
                }
            }

            return $game;
        });
    }

    /**
     * Une a un usuario a una partida aleatoria disponible.
     * 
     * @param string $userId
     * @return Game
     * @throws Exception
     */
    public function joinRandomGame(string $userId): Game
    {
        $game = $this->gameRepository->findAvailableRandom();

        if (!$game) {
            throw new Exception('No games available');
        }

        return DB::transaction(function () use ($game, $userId) {
            if (!$game->users()->where('user_id', $userId)->exists()) {
                $game->users()->attach($userId, ['is_afk' => false]);
                
                $latestRound = $this->roundRepository->getLatestRoundForGame($game->id);
                if ($latestRound) {
                    $latestRound->users()->attach($userId, ['actions_spent' => 0]);
                }
            }

            return $game;
        });
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
