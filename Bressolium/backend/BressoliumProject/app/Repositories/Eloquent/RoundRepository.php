<?php

namespace App\Repositories\Eloquent;

use App\Models\Round;
use App\Repositories\Contracts\RoundRepositoryInterface;

class RoundRepository implements RoundRepositoryInterface
{
    public function create(array $data): Round
    {
        return Round::create($data);
    }

    public function getLatestRoundForGame(string $gameId): ?Round
    {
        return Round::where('game_id', $gameId)
            ->orderBy('number', 'desc')
            ->first();
    }
}
