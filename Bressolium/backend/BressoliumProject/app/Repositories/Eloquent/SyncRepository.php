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
        $gameTechs = $game->technologies()
            ->with('technologyPrerequisites')
            ->get();

        return $gameTechs->map(function ($tech) use ($gameTechs) {
            $missing = [];

            foreach ($tech->technologyPrerequisites as $prereq) {
                if ($prereq->prereq_type !== 'technology') {
                    continue;
                }

                $prereqTech = $gameTechs->firstWhere('id', $prereq->prereq_id);
                $isActive   = $prereqTech ? (bool) $prereqTech->pivot->is_active : false;

                if (! $isActive) {
                    $missing[] = [
                        'type' => 'technology',
                        'name' => $prereqTech?->name ?? 'Desconocido',
                    ];
                }
            }

            return [
                'id'        => $tech->id,
                'name'      => $tech->name,
                'is_active' => (bool) $tech->pivot->is_active,
                'missing'   => $missing,
            ];
        })->values()->toArray();
    }

    public function getInventions(Game $game): array
    {
        $gameInvs = $game->inventions()
            ->with(['inventionCosts.resource', 'inventionPrerequisites'])
            ->get();

        $matMap = $game->materials()->get()->keyBy('id');

        return $gameInvs->map(function ($inv) use ($gameInvs, $matMap) {
            $missing = [];

            foreach ($inv->inventionCosts as $cost) {
                $mat    = $matMap->get($cost->resource_id);
                $have   = $mat ? (int) $mat->pivot->quantity : 0;
                $needed = (int) $cost->quantity;

                if ($have < $needed) {
                    $missing[] = [
                        'type'     => 'resource',
                        'name'     => $cost->resource?->name ?? 'Recurso',
                        'required' => $needed,
                        'have'     => $have,
                    ];
                }
            }

            foreach ($inv->inventionPrerequisites as $prereq) {
                if ($prereq->prereq_type !== 'invention') {
                    continue;
                }

                $prereqInv = $gameInvs->firstWhere('id', $prereq->prereq_id);
                $have      = $prereqInv ? (int) $prereqInv->pivot->quantity : 0;
                $needed    = (int) ($prereq->quantity ?? 1);

                if ($have < $needed) {
                    $missing[] = [
                        'type'     => 'invention',
                        'name'     => $prereqInv?->name ?? 'Invento',
                        'required' => $needed,
                        'have'     => $have,
                    ];
                }
            }

            return [
                'id'       => $inv->id,
                'name'     => $inv->name,
                'quantity' => (int) $inv->pivot->quantity,
                'missing'  => $missing,
            ];
        })->values()->toArray();
    }
}
