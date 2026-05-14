<?php

namespace App\Repositories\Eloquent;

use App\Models\Game;
use App\Models\InventionCost;
use App\Models\Vote;
use App\Repositories\Contracts\VoteRepositoryInterface;

class VoteRepository implements VoteRepositoryInterface
{
    public function hasVotedThisRound(string $roundId, string $userId): bool
    {
        return Vote::where('round_id', $roundId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function hasVotedForTechnology(string $roundId, string $userId): bool
    {
        return Vote::where('round_id', $roundId)
            ->where('user_id', $userId)
            ->whereNotNull('technology_id')
            ->exists();
    }

    public function hasVotedForInvention(string $roundId, string $userId): bool
    {
        return Vote::where('round_id', $roundId)
            ->where('user_id', $userId)
            ->whereNotNull('invention_id')
            ->exists();
    }

    public function isTechnologyCompleted(string $gameId, string $technologyId): bool
    {
        return Game::find($gameId)
            ?->technologies()
            ->where('technology_id', $technologyId)
            ->where('is_active', true)
            ->exists() ?? false;
    }

    public function hasEnoughMaterialsForInvention(string $gameId, string $inventionId): bool
    {
        $costs = InventionCost::where('invention_id', $inventionId)->get();

        if ($costs->isEmpty()) {
            return true;
        }

        $game = Game::find($gameId);

        foreach ($costs as $cost) {
            $stock = $game->materials()
                ->where('material_id', $cost->resource_id)
                ->first()
                ?->pivot->quantity ?? 0;

            if ($stock < $cost->quantity) {
                return false;
            }
        }

        return true;
    }

    public function store(string $roundId, string $userId, ?string $technologyId, ?string $inventionId): Vote
    {
        return Vote::create([
            'round_id' => $roundId,
            'user_id' => $userId,
            'technology_id' => $technologyId,
            'invention_id' => $inventionId,
        ]);
    }
}
