<?php

namespace App\Services;

use App\DTOs\ExploreActionDTO;
use App\DTOs\UpgradeActionDTO;
use App\Models\Game;
use App\Models\Tile;
use App\Repositories\Contracts\TileRepositoryInterface;

class ActionService
{
    public function __construct(private TileRepositoryInterface $tileRepo) {}

    public function explore(ExploreActionDTO $dto): array
    {
        $tile = $this->tileRepo->find($dto->tileId);

        if (!$this->tileRepo->isUserInGame($dto->userId, $tile->game_id)) {
            return ['status' => 403, 'error' => 'Forbidden'];
        }

        $round = $this->tileRepo->getCurrentRound($tile->game_id);
        if ($this->tileRepo->getActionsSpent($round, $dto->userId) >= 2) {
            return ['status' => 403, 'error' => 'No actions remaining'];
        }

        if ($tile->explored) {
            return ['status' => 422, 'error' => 'Tile already explored'];
        }

        $this->tileRepo->markExplored($tile, $dto->userId);
        $this->tileRepo->incrementActionsSpent($round, $dto->userId);

        $tile->refresh()->load('type');
        return ['status' => 200, 'data' => $tile];
    }

    public function upgrade(UpgradeActionDTO $dto): array
    {
        $tile = $this->tileRepo->find($dto->tileId);

        if (!$this->tileRepo->isUserInGame($dto->userId, $tile->game_id)) {
            return ['status' => 403, 'error' => 'Forbidden'];
        }

        $round = $this->tileRepo->getCurrentRound($tile->game_id);
        if ($this->tileRepo->getActionsSpent($round, $dto->userId) >= 2) {
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
        $this->tileRepo->incrementActionsSpent($round, $dto->userId);

        $tile->refresh()->load('type');
        return ['status' => 200, 'data' => $tile];
    }
}
