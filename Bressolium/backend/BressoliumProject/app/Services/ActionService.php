<?php

namespace App\Services;

use App\DTOs\ExploreActionDTO;
use App\DTOs\UpgradeActionDTO;
use App\Exceptions\ActionLimitExceededException;
use App\Exceptions\InsufficientMaterialsException;
use App\Exceptions\TileAlreadyExploredException;
use App\Exceptions\TileNotExploredException;
use App\Exceptions\UserNotInGameException;
use App\Models\Game;
use App\Models\Tile;
use App\Repositories\Contracts\TileRepositoryInterface;

class ActionService
{
    public function __construct(private TileRepositoryInterface $tileRepo) {}

    public function explore(ExploreActionDTO $dto): Tile
    {
        $tile = $this->tileRepo->find($dto->tileId);

        if (!$this->tileRepo->isUserInGame($dto->userId, $tile->game_id)) {
            throw new UserNotInGameException();
        }

        $round = $this->tileRepo->getCurrentRound($tile->game_id);
        if ($this->tileRepo->getActionsSpent($round, $dto->userId) >= 2) {
            throw new ActionLimitExceededException();
        }

        if ($tile->explored) {
            throw new TileAlreadyExploredException();
        }

        $this->tileRepo->markExplored($tile, $dto->userId);
        $this->tileRepo->incrementActionsSpent($round, $dto->userId);

        return $tile->refresh()->load('type');
    }

    public function upgrade(UpgradeActionDTO $dto): Tile
    {
        $tile = $this->tileRepo->find($dto->tileId);

        if (!$this->tileRepo->isUserInGame($dto->userId, $tile->game_id)) {
            throw new UserNotInGameException();
        }

        $round = $this->tileRepo->getCurrentRound($tile->game_id);
        if ($this->tileRepo->getActionsSpent($round, $dto->userId) >= 2) {
            throw new ActionLimitExceededException();
        }

        if (!$tile->explored) {
            throw new TileNotExploredException();
        }

        $nextType = $this->tileRepo->findNextTileType($tile);
        if (!$nextType) {
            throw new TileNotExploredException('No hay más niveles de mejora disponibles para esta casilla.');
        }

        $costs = $this->tileRepo->getUpgradeCosts($nextType);
        $game  = Game::find($tile->game_id);

        if (!$this->tileRepo->hasSufficientMaterials($game, $costs)) {
            throw new InsufficientMaterialsException();
        }

        $this->tileRepo->deductMaterials($game, $costs);
        $this->tileRepo->upgradeTile($tile, $nextType);
        $this->tileRepo->incrementActionsSpent($round, $dto->userId);

        return $tile->refresh()->load('type');
    }
}
