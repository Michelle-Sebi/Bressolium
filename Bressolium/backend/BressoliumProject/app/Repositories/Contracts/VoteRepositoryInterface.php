<?php

namespace App\Repositories\Contracts;

use App\Models\Vote;

interface VoteRepositoryInterface
{
    public function hasVotedThisRound(string $roundId, string $userId): bool;

    public function hasVotedForTechnology(string $roundId, string $userId): bool;

    public function hasVotedForInvention(string $roundId, string $userId): bool;

    public function isTechnologyCompleted(string $gameId, string $technologyId): bool;

    public function hasEnoughMaterialsForInvention(string $gameId, string $inventionId): bool;

    public function store(string $roundId, string $userId, ?string $technologyId, ?string $inventionId): Vote;
}
