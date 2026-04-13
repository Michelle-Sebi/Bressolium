<?php
/**
 * @module GameRepository
 * @description Repositorio para la gestión de datos del modelo Game (Equipos/Partidas).
 */

namespace App\Repositories;

use App\Models\Game;

class GameRepository
{
    /**
     * Crea un nuevo juego/equipo.
     * 
     * @param array $data
     * @return Game
     */
    public function create(array $data): Game
    {
        return Game::create($data);
    }

    /**
     * Busca una partida por nombre exacto.
     * 
     * @param string $name
     * @return Game|null
     */
    public function findByName(string $name): ?Game
    {
        return Game::where('name', $name)->first();
    }

    /**
     * Busca una partida aleatoria con menos de 5 miembros.
     * 
     * @return Game|null
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
     * @return \Illuminate\Database\Eloquent\Collection
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
     * @param string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getGamesByUserId(string $userId)
    {
        return Game::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    }
}
