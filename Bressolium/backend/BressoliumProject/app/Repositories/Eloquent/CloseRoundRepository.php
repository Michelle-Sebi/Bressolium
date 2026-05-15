<?php

namespace App\Repositories\Eloquent;

use App\Models\Game;
use App\Models\Invention;
use App\Models\Round;
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

    public function resolveTechnologyVote(Round $round): ?array
    {
        $rows = DB::table('votes')
            ->where('round_id', $round->id)
            ->whereNotNull('technology_id')
            ->select('technology_id', DB::raw('count(*) as vote_count'))
            ->groupBy('technology_id')
            ->orderByDesc('vote_count')
            ->limit(2)
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        return [
            'id'     => $rows[0]->technology_id,
            'is_tie' => $rows->count() >= 2 && $rows[0]->vote_count === $rows[1]->vote_count,
        ];
    }

    public function resolveInventionVote(Round $round): ?array
    {
        $rows = DB::table('votes')
            ->where('round_id', $round->id)
            ->whereNotNull('invention_id')
            ->select('invention_id', DB::raw('count(*) as vote_count'))
            ->groupBy('invention_id')
            ->orderByDesc('vote_count')
            ->limit(2)
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        return [
            'id'     => $rows[0]->invention_id,
            'is_tie' => $rows->count() >= 2 && $rows[0]->vote_count === $rows[1]->vote_count,
        ];
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
        $prereqs = $invention->inventionPrerequisites;
        if ($prereqs->isEmpty()) {
            return true;
        }

        $invPrereqIds  = $prereqs->where('prereq_type', 'invention')->pluck('prereq_id');
        $techPrereqIds = $prereqs->where('prereq_type', 'technology')->pluck('prereq_id');

        $gameInvMap = $invPrereqIds->isNotEmpty()
            ? $game->inventions()->whereIn('invention_id', $invPrereqIds)->get()->keyBy('id')
            : collect();

        $activeTechIds = $techPrereqIds->isNotEmpty()
            ? $game->technologies()->wherePivot('is_active', true)
                ->whereIn('technologies.id', $techPrereqIds)
                ->pluck('technologies.id')
            : collect();

        foreach ($prereqs as $prereq) {
            if ($prereq->prereq_type === 'invention') {
                $have = $gameInvMap->has($prereq->prereq_id)
                    ? (int) $gameInvMap[$prereq->prereq_id]->pivot->quantity
                    : 0;
                if ($have < $prereq->quantity) {
                    return false;
                }
            } elseif ($prereq->prereq_type === 'technology') {
                if (! $activeTechIds->contains($prereq->prereq_id)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function inventionResourcesMet(Game $game, Invention $invention): bool
    {
        $costs = $invention->inventionCosts;
        if ($costs->isEmpty()) {
            return true;
        }

        $gameMatMap = $game->materials()
            ->whereIn('material_id', $costs->pluck('resource_id'))
            ->get()
            ->keyBy('id');

        foreach ($costs as $cost) {
            $have = $gameMatMap->has($cost->resource_id)
                ? (int) $gameMatMap[$cost->resource_id]->pivot->quantity
                : 0;
            if ($have < $cost->quantity) {
                return false;
            }
        }

        return true;
    }

    public function buildInvention(Game $game, Invention $invention): void
    {
        // Deduct costs with direct decrements — no SELECT needed (resources already validated)
        foreach ($invention->inventionCosts as $cost) {
            DB::table('game_material')
                ->where('game_id', $game->id)
                ->where('material_id', $cost->resource_id)
                ->decrement('quantity', $cost->quantity);
        }

        $existingInvention = $game->inventions()->where('invention_id', $invention->id)->first();

        if ($existingInvention) {
            $game->inventions()->updateExistingPivot($invention->id, [
                'quantity'  => (int) $existingInvention->pivot->quantity + 1,
                'is_active' => true,
            ]);
        } else {
            $game->inventions()->attach($invention->id, ['quantity' => 1, 'is_active' => true]);
        }
    }

    public function finishGame(Game $game): void
    {
        $game->update(['status' => 'FINISHED']);
    }

    public function produceMaterialsFromExploredTiles(Game $game): void
    {
        // Single JOIN+GROUP BY to sum total production across all explored tiles
        $rows = DB::table('tiles')
            ->join('material_tile_type', 'material_tile_type.tile_type_id', '=', 'tiles.tile_type_id')
            ->where('tiles.game_id', $game->id)
            ->where('tiles.explored', true)
            ->select('material_tile_type.material_id', DB::raw('SUM(material_tile_type.quantity) as total'))
            ->groupBy('material_tile_type.material_id')
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        // Single upsert: increment existing rows, insert new ones
        $placeholders = implode(', ', array_fill(0, $rows->count(), '(?, ?, ?)'));
        $bindings = [];
        foreach ($rows as $row) {
            $bindings[] = $game->id;
            $bindings[] = $row->material_id;
            $bindings[] = (int) $row->total;
        }

        DB::statement(
            "INSERT INTO game_material (game_id, material_id, quantity)
             VALUES {$placeholders}
             ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)",
            $bindings
        );
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
        $now     = now();
        $userIds = $game->users->pluck('id');

        DB::table('round_user')->insert(
            $userIds->map(fn ($id) => [
                'round_id'    => $round->id,
                'user_id'     => $id,
                'actions_spent' => 0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ])->toArray()
        );

        // Each round starts clean — AFK is re-evaluated at close, never carried over
        DB::table('game_user')
            ->where('game_id', $game->id)
            ->whereIn('user_id', $userIds)
            ->update(['is_afk' => false, 'updated_at' => $now]);
    }

    public function markAfkPlayers(Round $round, Game $game): void
    {
        $afkUserIds = DB::table('round_user')
            ->where('round_id', $round->id)
            ->where('actions_spent', 0)
            ->pluck('user_id');

        if ($afkUserIds->isEmpty()) {
            return;
        }

        DB::table('game_user')
            ->where('game_id', $game->id)
            ->whereIn('user_id', $afkUserIds)
            ->update(['is_afk' => true, 'updated_at' => now()]);
    }

    public function markRoundResult(Round $round, string $inventionId, bool $noConsensus): void
    {
        DB::table('rounds')
            ->where('id', $round->id)
            ->update([
                'no_consensus'            => $noConsensus,
                'last_built_invention_id' => $inventionId,
            ]);
    }

    public function markRoundTechResult(Round $round, string $techId, bool $noConsensus): void
    {
        DB::table('rounds')
            ->where('id', $round->id)
            ->update([
                'no_consensus_tech'      => $noConsensus,
                'last_activated_tech_id' => $techId,
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

    public function markRoundEnded(Round $round): void
    {
        $round->update(['ended_at' => now()]);
    }

    public function setPlayerFinishedAt(Round $round, string $userId): void
    {
        $round->users()->updateExistingPivot($userId, ['finished_at' => now()]);
    }

    public function clearPlayerFinishedAt(Round $round, string $userId): void
    {
        $round->users()->updateExistingPivot($userId, ['finished_at' => null]);
    }

    public function isPlayerFinished(Round $round, string $userId): bool
    {
        $pivot = $round->users()->where('user_id', $userId)->first();

        return $pivot !== null && $pivot->pivot->finished_at !== null;
    }

    public function getPlayerActionsSpent(Round $round, string $userId): int
    {
        $pivot = $round->users()->where('user_id', $userId)->first();

        return $pivot ? (int) $pivot->pivot->actions_spent : 0;
    }

    public function hasPlayerVoted(Round $round, string $userId): bool
    {
        return DB::table('votes')
            ->where('round_id', $round->id)
            ->where('user_id', $userId)
            ->exists();
    }

    public function hasPlayerVotedForTechnology(Round $round, string $userId): bool
    {
        return DB::table('votes')
            ->where('round_id', $round->id)
            ->where('user_id', $userId)
            ->whereNotNull('technology_id')
            ->exists();
    }

    public function hasPlayerVotedForInvention(Round $round, string $userId): bool
    {
        return DB::table('votes')
            ->where('round_id', $round->id)
            ->where('user_id', $userId)
            ->whereNotNull('invention_id')
            ->exists();
    }

    public function countFinishedPlayers(Round $round): int
    {
        return $round->users()->wherePivotNotNull('finished_at')->count();
    }

    public function markAllUnfinishedPlayersAsDone(Round $round, Game $game): void
    {
        foreach ($game->users as $user) {
            $pivot = $round->users()->where('user_id', $user->id)->first();
            if ($pivot && $pivot->pivot->finished_at === null) {
                $round->users()->updateExistingPivot($user->id, ['finished_at' => now()]);
            }
        }
    }
}
