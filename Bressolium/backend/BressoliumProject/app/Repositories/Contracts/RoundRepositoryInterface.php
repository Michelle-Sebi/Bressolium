<?php

namespace App\Repositories\Contracts;

use App\Models\Round;

interface RoundRepositoryInterface
{
    public function create(array $data): Round;

    public function getLatestRoundForGame(string $gameId): ?Round;
}
