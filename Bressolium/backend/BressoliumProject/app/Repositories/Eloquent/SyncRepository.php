<?php

namespace App\Repositories\Eloquent;

use App\Models\Game;
use App\Models\Invention;
use App\Models\Round;
use App\Models\Technology;
use App\Repositories\Contracts\SyncRepositoryInterface;
use Illuminate\Support\Facades\DB;

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
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'quantity' => $m->pivot->quantity,
            ])
            ->values()
            ->toArray();
    }

    public function getTechnologies(Game $game): array
    {
        $allTechs = Technology::with('technologyPrerequisites')->get();
        $gameTechMap = $game->technologies()->get()->keyBy('id');

        return $allTechs->map(function ($tech) use ($gameTechMap, $allTechs) {
            $isActive = isset($gameTechMap[$tech->id])
                && (bool) $gameTechMap[$tech->id]->pivot->is_active;

            $missing = [];

            foreach ($tech->technologyPrerequisites as $prereq) {
                if ($prereq->prereq_type !== 'technology') {
                    continue;
                }

                $prereqEntry = $gameTechMap->get($prereq->prereq_id);
                $prereqActive = $prereqEntry ? (bool) $prereqEntry->pivot->is_active : false;

                if (! $prereqActive) {
                    $prereqInfo = $allTechs->firstWhere('id', $prereq->prereq_id);
                    $missing[] = [
                        'type' => 'technology',
                        'name' => $prereqInfo?->name ?? 'Desconocido',
                    ];
                }
            }

            return [
                'id' => $tech->id,
                'name' => $tech->name,
                'is_active' => $isActive,
                'missing' => $missing,
            ];
        })->values()->toArray();
    }

    public function getInventions(Game $game): array
    {
        $allInvs = Invention::with(['inventionCosts.resource', 'inventionPrerequisites'])->get();
        $gameInvMap = $game->inventions()->get()->keyBy('id');
        $matMap = $game->materials()->get()->keyBy('id');
        $activeTechIds = $game->technologies()->wherePivot('is_active', true)->get()->pluck('id');

        return $allInvs->map(function ($inv) use ($gameInvMap, $allInvs, $matMap, $activeTechIds) {
            $quantity = $gameInvMap->has($inv->id) ? (int) $gameInvMap[$inv->id]->pivot->quantity : 0;
            $missing = [];

            foreach ($inv->inventionCosts as $cost) {
                $mat = $matMap->get($cost->resource_id);
                $have = $mat ? (int) $mat->pivot->quantity : 0;
                $needed = (int) $cost->quantity;

                if ($have < $needed) {
                    $missing[] = [
                        'type' => 'resource',
                        'name' => $cost->resource?->name ?? 'Recurso',
                        'required' => $needed,
                        'have' => $have,
                    ];
                }
            }

            foreach ($inv->inventionPrerequisites as $prereq) {
                if ($prereq->prereq_type === 'invention') {
                    $prereqEntry = $gameInvMap->get($prereq->prereq_id);
                    $have = $prereqEntry ? (int) $prereqEntry->pivot->quantity : 0;
                    $needed = (int) ($prereq->quantity ?? 1);

                    if ($have < $needed) {
                        $prereqInfo = $allInvs->firstWhere('id', $prereq->prereq_id);
                        $missing[] = [
                            'type' => 'invention',
                            'name' => $prereqInfo?->name ?? 'Invento',
                            'required' => $needed,
                            'have' => $have,
                        ];
                    }
                } elseif ($prereq->prereq_type === 'technology') {
                    if (! $activeTechIds->contains($prereq->prereq_id)) {
                        $techInfo = Technology::find($prereq->prereq_id);
                        $missing[] = [
                            'type' => 'technology',
                            'name' => $techInfo?->name ?? 'Tecnología',
                            'required' => 1,
                            'have' => 0,
                        ];
                    }
                }
            }

            return [
                'id' => $inv->id,
                'name' => $inv->name,
                'quantity' => $quantity,
                'missing' => $missing,
            ];
        })->values()->toArray();
    }

    public function hasVotedThisRound(Round $round, string $userId): bool
    {
        return DB::table('votes')
            ->where('round_id', $round->id)
            ->where('user_id', $userId)
            ->exists();
    }

    public function getLastRoundResult(Round $currentRound): array
    {
        $prev = DB::table('rounds')
            ->where('game_id', $currentRound->game_id)
            ->where('number', $currentRound->number - 1)
            ->first();

        if (! $prev || ! $prev->no_consensus || ! $prev->last_built_invention_id) {
            return [];
        }

        $name = DB::table('inventions')
            ->where('id', $prev->last_built_invention_id)
            ->value('name');

        return [
            'no_consensus' => true,
            'built_name'   => $name ?? 'Invento desconocido',
        ];
    }
}
