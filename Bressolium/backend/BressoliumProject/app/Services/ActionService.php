<?php

namespace App\Services;

use App\DTOs\ExploreActionDTO;
use App\DTOs\UpgradeActionDTO;
use App\Events\TileExplored;
use App\Events\TileUpgraded;
use App\Exceptions\ActionLimitExceededException;
use App\Exceptions\InsufficientMaterialsException;
use App\Exceptions\PuebloTileActionException;
use App\Exceptions\TechnologyRequiredException;
use App\Exceptions\TileAlreadyExploredException;
use App\Exceptions\TileNotAdjacentException;
use App\Exceptions\TileNotExploredException;
use App\Exceptions\UserNotInGameException;
use App\Models\Game;
use App\Models\Tile;
use App\Repositories\Contracts\TileRepositoryInterface;

class ActionService
{
    public function __construct(
        private TileRepositoryInterface $tileRepo,
        private CacheService $cacheService,
    ) {}

    public function explore(ExploreActionDTO $dto): Tile
    {
        $tile = $this->tileRepo->find($dto->tileId);

        if (! $this->tileRepo->isUserInGame($dto->userId, $tile->game_id)) {
            throw new UserNotInGameException;
        }

        if ($tile->type?->base_type === 'pueblo') {
            throw new PuebloTileActionException;
        }

        $round = $this->tileRepo->getCurrentRound($tile->game_id);
        if ($this->tileRepo->getActionsSpent($round, $dto->userId) >= 2) {
            throw new ActionLimitExceededException;
        }

        if ($tile->explored) {
            throw new TileAlreadyExploredException;
        }

        if (! $this->tileRepo->isAdjacentToUserExplored($tile, $dto->userId)) {
            throw new TileNotAdjacentException;
        }

        $this->tileRepo->markExplored($tile, $dto->userId);
        $this->tileRepo->incrementActionsSpent($round, $dto->userId);

        $tile->refresh()->load('type');
        TileExplored::dispatch($tile, $dto->userId);
        $this->cacheService->invalidateBoard($tile->game_id);

        return $tile;
    }

    public function upgrade(UpgradeActionDTO $dto): Tile
    {
        $tile = $this->tileRepo->find($dto->tileId);

        if (! $this->tileRepo->isUserInGame($dto->userId, $tile->game_id)) {
            throw new UserNotInGameException;
        }

        if ($tile->type?->base_type === 'pueblo') {
            throw new PuebloTileActionException;
        }

        $round = $this->tileRepo->getCurrentRound($tile->game_id);
        if ($this->tileRepo->getActionsSpent($round, $dto->userId) >= 2) {
            throw new ActionLimitExceededException;
        }

        if (! $tile->explored) {
            throw new TileNotExploredException;
        }

        $nextType = $this->tileRepo->findNextTileType($tile);
        if (! $nextType) {
            throw new TileNotExploredException('No hay más niveles de mejora disponibles para esta casilla.');
        }

        $game = Game::find($tile->game_id);

        $requiredTech = $this->tileRepo->getRequiredTechnology($nextType);
        if ($requiredTech !== null) {
            $isActive = $game->technologies()
                ->where('technology_id', $requiredTech->id)
                ->wherePivot('is_active', true)
                ->exists();
            if (! $isActive) {
                throw new TechnologyRequiredException($requiredTech->name);
            }
        }

        $costs = $this->tileRepo->getUpgradeCosts($nextType);
        if (! $this->tileRepo->hasSufficientMaterials($game, $costs)) {
            throw new InsufficientMaterialsException;
        }

        $this->tileRepo->deductMaterials($game, $costs);
        $this->tileRepo->upgradeTile($tile, $nextType);
        $this->tileRepo->incrementActionsSpent($round, $dto->userId);

        $tile->refresh()->load('type');
        TileUpgraded::dispatch($tile, $dto->userId);
        $this->cacheService->invalidateBoard($tile->game_id);

        return $tile;
    }
}
