<?php

/**
 * @module GameRepository
 *
 * @description Repositorio para la gestión de datos del modelo Game (Equipos/Partidas).
 */

namespace App\Repositories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;

class GameRepository
{
    /**
     * Crea un nuevo juego/equipo.
     */
    public function create(array $data): Game
    {
        return Game::create($data);
    }

    /**
     * Busca una partida por nombre exacto.
     */
    public function findByName(string $name): ?Game
    {
        return Game::where('name', $name)->first();
    }

    /**
     * Busca una partida aleatoria con menos de 5 miembros.
     */
    public function findAvailableRandom(): ?Game
    {
        return Game::withCount('users')
            ->having('users_count', '<', 5)
            ->first();
    }

    /**
     * Obtiene todas las partidas disponibles (menos de 5 usuarios).
     *
     * @return Collection
     */
    public function getAllAvailableGames()
    {
        return Game::withCount('users')
            ->having('users_count', '<', 5)
            ->get();
    }

    /**
     * Obtiene las partidas de un usuario específico.
     *
     * @return Collection
     */
    public function getGamesByUserId(string $userId)
    {
        return Game::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    }
}
