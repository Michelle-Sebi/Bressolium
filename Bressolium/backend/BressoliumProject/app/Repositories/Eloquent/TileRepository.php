<?php

namespace App\Repositories\Eloquent;

use App\Models\Game;
use App\Models\Round;
use App\Models\Technology;
use App\Models\Tile;
use App\Models\TileType;
use App\Repositories\Contracts\TileRepositoryInterface;

class TileRepository implements TileRepositoryInterface
{
    public function find(string $id): ?Tile
    {
        return Tile::find($id);
    }

    public function isUserInGame(string $userId, string $gameId): bool
    {
        return Game::whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->where('id', $gameId)
            ->exists();
    }

    public function getCurrentRound(string $gameId): ?Round
    {
        return Round::where('game_id', $gameId)
            ->whereNull('ended_at')
            ->latest()
            ->first();
    }

    public function getActionsSpent(Round $round, string $userId): int
    {
        $pivot = $round->users()->where('user_id', $userId)->first();
        return $pivot ? (int) $pivot->pivot->actions_spent : 0;
    }

    public function incrementActionsSpent(Round $round, string $userId): void
    {
        $current = $this->getActionsSpent($round, $userId);
        $round->users()->updateExistingPivot($userId, ['actions_spent' => $current + 1]);
    }

    public function markExplored(Tile $tile, string $userId): void
    {
        $tile->update([
            'explored'              => true,
            'explored_by_player_id' => $userId,
            'explored_at'           => now(),
        ]);
    }

    public function findNextTileType(Tile $tile): ?TileType
    {
        $current = TileType::find($tile->tile_type_id);
        if (!$current) {
            return null;
        }

        return TileType::where('base_type', $current->base_type)
            ->where('level', $current->level + 1)
            ->first();
    }

    public function getRequiredTechnology(TileType $nextType): ?Technology
    {
        $material = $nextType->materials()->wherePivotNotNull('tech_required')->first();
        if (!$material) {
            return null;
        }
        return Technology::find($material->pivot->tech_required);
    }

    public function upgradeTile(Tile $tile, TileType $nextType): void
    {
        $tile->update(['tile_type_id' => $nextType->id]);
    }
}
