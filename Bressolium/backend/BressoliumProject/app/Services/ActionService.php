<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Tile;
use App\Repositories\TileRepository;
use Illuminate\Http\JsonResponse;

class ActionService
{
    public function __construct(private TileRepository $tileRepo) {}

    public function explore(string $tileId, string $userId): array
    {
        $tile = $this->tileRepo->find($tileId);

        if (!$this->tileRepo->isUserInGame($userId, $tile->game_id)) {
            return ['status' => 403, 'error' => 'Forbidden'];
        }

        $round = $this->tileRepo->getCurrentRound($tile->game_id);
        if ($this->tileRepo->getActionsSpent($round, $userId) >= 2) {
            return ['status' => 403, 'error' => 'No actions remaining'];
        }

        if ($tile->explored) {
            return ['status' => 422, 'error' => 'Tile already explored'];
        }

        $this->tileRepo->markExplored($tile, $userId);
        $this->tileRepo->incrementActionsSpent($round, $userId);

        $tile->refresh()->load('type');
        return ['status' => 200, 'data' => $tile];
    }

    public function upgrade(string $tileId, string $userId): array
    {
        $tile = $this->tileRepo->find($tileId);

        if (!$this->tileRepo->isUserInGame($userId, $tile->game_id)) {
            return ['status' => 403, 'error' => 'Forbidden'];
        }

        $round = $this->tileRepo->getCurrentRound($tile->game_id);
        if ($this->tileRepo->getActionsSpent($round, $userId) >= 2) {
            return ['status' => 403, 'error' => 'No actions remaining'];
        }

        if (!$tile->explored) {
            return ['status' => 422, 'error' => 'Tile not explored'];
        }

        $nextType = $this->tileRepo->findNextTileType($tile);
        if (!$nextType) {
            return ['status' => 422, 'error' => 'No upgrade available'];
        }

        $costs = $this->tileRepo->getUpgradeCosts($nextType);
        $game  = Game::find($tile->game_id);

        if (!$this->tileRepo->hasSufficientMaterials($game, $costs)) {
            return ['status' => 400, 'error' => 'Insufficient materials'];
        }

        $this->tileRepo->deductMaterials($game, $costs);
        $this->tileRepo->upgradeTile($tile, $nextType);
        $this->tileRepo->incrementActionsSpent($round, $userId);

        $tile->refresh()->load('type');
        return ['status' => 200, 'data' => $tile];
    }
}
