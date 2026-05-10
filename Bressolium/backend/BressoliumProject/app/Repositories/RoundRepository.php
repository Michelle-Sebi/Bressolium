<?php

/**
 * @module RoundRepository
 *
 * @description Repositorio para la gestión de datos del modelo Round.
 */

namespace App\Repositories;

use App\Models\Round;

class RoundRepository
{
    /**
     * Crea un nuevo registro de ronda.
     */
    public function create(array $data): Round
    {
        return Round::create($data);
    }

    /**
     * Obtiene la última ronda de una partida.
     */
    public function getLatestRoundForGame(string $gameId): ?Round
    {
        return Round::where('game_id', $gameId)
            ->orderBy('number', 'desc')
            ->first();
    }
}
