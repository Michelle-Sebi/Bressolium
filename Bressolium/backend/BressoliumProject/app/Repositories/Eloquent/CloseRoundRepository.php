<?php

namespace App\Repositories\Eloquent;

use App\Models\Game;
use App\Models\Invention;
use App\Models\Round;
use App\Models\Tile;
use App\Repositories\Contracts\CloseRoundRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CloseRoundRepository implements CloseRoundRepositoryInterface
{
    public function findGameWithUsers(string $gameId): Game
    {
        return Game::with('users')->findOrFail($gameId);
    }

    public function getLatestRound(Game $game): Round
    {
        return $game->rounds()->orderByDesc('number')->firstOrFail();
    }

    public function getMostVotedTechnologyId(Round $round): ?string
    {
        $result = DB::table('votes')
            ->where('round_id', $round->id)
            ->whereNotNull('technology_id')
            ->select('technology_id', DB::raw('count(*) as vote_count'))
            ->groupBy('technology_id')
            ->orderByDesc('vote_count')
            ->first();

        return $result?->technology_id;
    }

    public function getMostVotedInventionId(Round $round): ?string
    {
        $result = DB::table('votes')
            ->where('round_id', $round->id)
            ->whereNotNull('invention_id')
            ->select('invention_id', DB::raw('count(*) as vote_count'))
            ->groupBy('invention_id')
            ->orderByDesc('vote_count')
            ->first();

        return $result?->invention_id;
    }

    public function activateTechnology(Game $game, string $technologyId): void
    {
        $game->technologies()->syncWithoutDetaching([
            $technologyId => ['is_active' => true],
        ]);
    }

    public function getInventionWithDependencies(string $inventionId): ?Invention
    {
        return Invention::with(['inventionCosts', 'inventionPrerequisites'])
            ->find($inventionId);
    }

    public function inventionPrerequisitesMet(Game $game, Invention $invention): bool
    {
        $activeTechIds = $game->technologies()->wherePivot('is_active', true)->pluck('technologies.id');

        foreach ($invention->inventionPrerequisites as $prerequisite) {
            if ($prerequisite->prereq_type === 'invention') {
                $gameInvention = $game->inventions()
                    ->where('invention_id', $prerequisite->prereq_id)
                    ->first();

                $currentQuantity = $gameInvention ? (int) $gameInvention->pivot->quantity : 0;

                if ($currentQuantity < $prerequisite->quantity) {
                    return false;
                }
            } elseif ($prerequisite->prereq_type === 'technology') {
                if (! $activeTechIds->contains($prerequisite->prereq_id)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function inventionResourcesMet(Game $game, Invention $invention): bool
    {
        foreach ($invention->inventionCosts as $cost) {
            $gameMaterial = $game->materials()
                ->where('material_id', $cost->resource_id)
                ->first();

            $currentQuantity = $gameMaterial ? (int) $gameMaterial->pivot->quantity : 0;

            if ($currentQuantity < $cost->quantity) {
                return false;
            }
        }

        return true;
    }

    public function buildInvention(Game $game, Invention $invention): void
    {
        foreach ($invention->inventionCosts as $cost) {
            $gameMaterial = $game->materials()
                ->where('material_id', $cost->resource_id)
                ->first();

            $currentQuantity = $gameMaterial ? (int) $gameMaterial->pivot->quantity : 0;

            $game->materials()->updateExistingPivot($cost->resource_id, [
                'quantity' => $currentQuantity - $cost->quantity,
            ]);
        }

        $existingInvention = $game->inventions()
            ->where('invention_id', $invention->id)
            ->first();

        if ($existingInvention) {
            $currentQuantity = (int) $existingInvention->pivot->quantity;

            $game->inventions()->updateExistingPivot($invention->id, [
                'quantity' => $currentQuantity + 1,
                'is_active' => true,
            ]);
        } else {
            $game->inventions()->attach($invention->id, [
                'quantity' => 1,
                'is_active' => true,
            ]);
        }
    }

    public function finishGame(Game $game): void
    {
        $game->update(['status' => 'FINISHED']);
    }

    public function produceMaterialsFromExploredTiles(Game $game): void
    {
        $exploredTiles = Tile::where('game_id', $game->id)
            ->where('explored', true)
            ->with('type.materials')
            ->get();

        foreach ($exploredTiles as $tile) {
            foreach ($tile->type->materials as $material) {
                $production = (int) $material->pivot->quantity;

                $gameMaterial = $game->materials()
                    ->where('material_id', $material->id)
                    ->first();

                if ($gameMaterial) {
                    $currentQuantity = (int) $gameMaterial->pivot->quantity;

                    $game->materials()->updateExistingPivot($material->id, [
                        'quantity' => $currentQuantity + $production,
                    ]);
                } else {
                    $game->materials()->attach($material->id, [
                        'quantity' => $production,
                    ]);
                }
            }
        }
    }

    public function createNextRound(Game $game): Round
    {
        $latestNumber = $game->rounds()->max('number');

        return $game->rounds()->create([
            'number' => $latestNumber + 1,
            'start_date' => now(),
        ]);
    }

    public function initializePlayersForRound(Round $round, Game $game): void
    {
        foreach ($game->users as $user) {
            $round->users()->attach($user->id, ['actions_spent' => 0]);
            // Cada jornada empieza limpia: el AFK se re-evalúa al cierre, no se arrastra
            $game->users()->updateExistingPivot($user->id, ['is_afk' => false]);
        }
    }

    public function markAfkPlayers(Round $round, Game $game): void
    {
        foreach ($game->users as $user) {
            $roundUser = $round->users()->where('user_id', $user->id)->first();

            if ($roundUser && (int) $roundUser->pivot->actions_spent === 0) {
                $game->users()->updateExistingPivot($user->id, ['is_afk' => true]);
            }
        }
    }

    public function hasInventionVoteTie(Round $round): bool
    {
        $results = DB::table('votes')
            ->where('round_id', $round->id)
            ->whereNotNull('invention_id')
            ->select('invention_id', DB::raw('count(*) as vote_count'))
            ->groupBy('invention_id')
            ->orderByDesc('vote_count')
            ->get();

        if ($results->count() < 2) {
            return false;
        }

        return $results[0]->vote_count === $results[1]->vote_count;
    }

    public function markRoundResult(Round $round, string $inventionId, bool $noConsensus): void
    {
        DB::table('rounds')
            ->where('id', $round->id)
            ->update([
                'no_consensus'             => $noConsensus,
                'last_built_invention_id'  => $inventionId,
            ]);
    }

    public function allNonAfkPlayersHaveVoted(Round $round, Game $game): bool
    {
        $activePlayerCount = $game->users()->wherePivot('is_afk', false)->count();

        if ($activePlayerCount === 0) {
            return false;
        }

        $voteCount = DB::table('votes')
            ->where('round_id', $round->id)
            ->count();

        return $voteCount >= $activePlayerCount;
    }
}
