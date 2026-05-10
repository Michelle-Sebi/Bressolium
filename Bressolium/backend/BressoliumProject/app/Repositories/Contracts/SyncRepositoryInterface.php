<?php

namespace App\Repositories\Contracts;

use App\Models\Game;
use App\Models\Round;

interface SyncRepositoryInterface
{
    public function getCurrentRound(string $gameId): ?Round;

    public function getActionsSpent(Round $round, string $userId): int;

    public function getInventory(Game $game): array;

    public function getTechnologies(Game $game): array;

    public function getInventions(Game $game): array;

    public function hasVotedThisRound(Round $round, string $userId): bool;
}
