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
                'id'       => $m->id,
                'name'     => $m->name,
                'quantity' => $m->pivot->quantity,
                'group'    => $m->group,
                'tier'     => $m->tier,
            ])
            ->values()
            ->toArray();
    }

    public function getTechnologies(Game $game): array
    {
        $allTechs = Technology::with('technologyPrerequisites')->get();
        $gameTechMap = $game->technologies()->get()->keyBy('id');

        // Techs desbloqueadas por cada tech (como prerequisito de otra)
        $techsUnlockedBy = [];
        foreach ($allTechs as $t) {
            foreach ($t->technologyPrerequisites as $prereq) {
                if ($prereq->prereq_type === 'technology') {
                    $techsUnlockedBy[$prereq->prereq_id][] = $t->name;
                }
            }
        }

        // Inventos desbloqueados por cada tech (via technology_id)
        $inventionsUnlockedBy = [];
        foreach (Invention::whereNotNull('technology_id')->get(['name', 'technology_id']) as $inv) {
            $inventionsUnlockedBy[$inv->technology_id][] = $inv->name;
        }

        return $allTechs->map(function ($tech) use ($gameTechMap, $allTechs, $techsUnlockedBy, $inventionsUnlockedBy) {
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

            $unlocks = array_merge(
                array_map(fn ($n) => ['type' => 'technology', 'name' => $n], $techsUnlockedBy[$tech->id] ?? []),
                array_map(fn ($n) => ['type' => 'invention',  'name' => $n], $inventionsUnlockedBy[$tech->id] ?? []),
            );

            return [
                'id'        => $tech->id,
                'name'      => $tech->name,
                'is_active' => $isActive,
                'missing'   => $missing,
                'unlocks'   => $unlocks,
            ];
        })->values()->toArray();
    }

    public function getInventions(Game $game): array
    {
        $allInvs = Invention::with(['inventionCosts.resource', 'inventionPrerequisites'])->get();
        $gameInvMap = $game->inventions()->get()->keyBy('id');
        $matMap = $game->materials()->get()->keyBy('id');
        $activeTechIds = $game->technologies()->wherePivot('is_active', true)->pluck('technologies.id');

        return $allInvs->map(function ($inv) use ($gameInvMap, $allInvs, $matMap, $activeTechIds) {
            $quantity = $gameInvMap->has($inv->id) ? (int) $gameInvMap[$inv->id]->pivot->quantity : 0;
            $missing = [];
            $costs   = [];

            foreach ($inv->inventionCosts as $cost) {
                $mat = $matMap->get($cost->resource_id);
                $have = $mat ? (int) $mat->pivot->quantity : 0;
                $needed = (int) $cost->quantity;

                $costs[] = [
                    'type'     => 'resource',
                    'name'     => $cost->resource?->name ?? 'Recurso',
                    'required' => $needed,
                ];

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
                if ($prereq->prereq_type === 'invention') {
                    $prereqEntry = $gameInvMap->get($prereq->prereq_id);
                    $have = $prereqEntry ? (int) $prereqEntry->pivot->quantity : 0;
                    $needed = (int) ($prereq->quantity ?? 1);
                    $prereqInfo = $allInvs->firstWhere('id', $prereq->prereq_id);

                    $costs[] = [
                        'type'     => 'invention',
                        'name'     => $prereqInfo?->name ?? 'Invento',
                        'required' => $needed,
                    ];

                    if ($have < $needed) {
                        $missing[] = [
                            'type'     => 'invention',
                            'name'     => $prereqInfo?->name ?? 'Invento',
                            'required' => $needed,
                            'have'     => $have,
                        ];
                    }
                } elseif ($prereq->prereq_type === 'technology') {
                    $techInfo = Technology::find($prereq->prereq_id);

                    $costs[] = [
                        'type'     => 'technology',
                        'name'     => $techInfo?->name ?? 'Tecnología',
                        'required' => 1,
                    ];

                    if (! $activeTechIds->contains($prereq->prereq_id)) {
                        $missing[] = [
                            'type'     => 'technology',
                            'name'     => $techInfo?->name ?? 'Tecnología',
                            'required' => 1,
                            'have'     => 0,
                        ];
                    }
                }
            }

            return [
                'id'       => $inv->id,
                'name'     => $inv->name,
                'quantity' => $quantity,
                'missing'  => $missing,
                'costs'    => $costs,
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

    public function hasVotedForTechnology(Round $round, string $userId): bool
    {
        return DB::table('votes')
            ->where('round_id', $round->id)
            ->where('user_id', $userId)
            ->whereNotNull('technology_id')
            ->exists();
    }

    public function hasVotedForInvention(Round $round, string $userId): bool
    {
        return DB::table('votes')
            ->where('round_id', $round->id)
            ->where('user_id', $userId)
            ->whereNotNull('invention_id')
            ->exists();
    }

    public function hasFinishedRound(Round $round, string $userId): bool
    {
        $pivot = $round->users()->where('user_id', $userId)->first();

        return $pivot !== null && $pivot->pivot->finished_at !== null;
    }

    public function getLastRoundResult(Round $currentRound): array
    {
        $prev = DB::table('rounds')
            ->where('game_id', $currentRound->game_id)
            ->where('number', $currentRound->number - 1)
            ->first();

        if (! $prev) {
            return [];
        }

        $result = [];

        if ($prev->no_consensus && $prev->last_built_invention_id) {
            $result['no_consensus_inv'] = true;
            $result['built_inv_name']   = DB::table('inventions')
                ->where('id', $prev->last_built_invention_id)
                ->value('name') ?? 'Invento desconocido';
        }

        if ($prev->no_consensus_tech && $prev->last_activated_tech_id) {
            $result['no_consensus_tech'] = true;
            $result['built_tech_name']   = DB::table('technologies')
                ->where('id', $prev->last_activated_tech_id)
                ->value('name') ?? 'Tecnología desconocida';
        }

        return $result;
    }
}
