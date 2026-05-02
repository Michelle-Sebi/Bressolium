<?php

namespace App\Repositories\Eloquent;

use App\Models\Game;
use App\Models\Round;
use App\Repositories\Contracts\SyncRepositoryInterface;

class SyncRepository implements SyncRepositoryInterface
{
    public function getCurrentRound(string $gameId): ?Round
    {
        return Round::where('game_id', $gameId)
            ->whereNull('ended_at')
            ->latest('number')
            ->first();
    }

    public function getActionsSpent(Round $round, string $userId): int
    {
        $pivot = $round->users()->where('user_id', $userId)->first();
        return $pivot?->pivot->actions_spent ?? 0;
    }

    public function getInventory(Game $game): array
    {
        return $game->materials()
            ->get()
            ->map(fn($m) => [
                'id'       => $m->id,
                'name'     => $m->name,
                'quantity' => $m->pivot->quantity,
            ])
            ->values()
            ->toArray();
    }

    public function getTechnologies(Game $game): array
    {
        return $game->technologies()
            ->get()
            ->map(fn($t) => [
                'id'        => $t->id,
                'name'      => $t->name,
                'is_active' => (bool) $t->pivot->is_active,
            ])
            ->values()
            ->toArray();
    }

    public function getInventions(Game $game): array
    {
        return $game->inventions()
            ->get()
            ->map(fn($i) => [
                'id'       => $i->id,
                'name'     => $i->name,
                'quantity' => (int) $i->pivot->quantity,
            ])
            ->values()
            ->toArray();
    }
}
