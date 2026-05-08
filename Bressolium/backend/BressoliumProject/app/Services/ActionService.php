<?php

namespace App\Services;

use App\DTOs\ExploreActionDTO;
use App\DTOs\UpgradeActionDTO;
use App\Events\TileExplored;
use App\Events\TileUpgraded;
use App\Exceptions\ActionLimitExceededException;
use App\Exceptions\PuebloTileActionException;
use App\Exceptions\TileAlreadyExploredException;
use App\Exceptions\TileNotAdjacentException;
use App\Exceptions\TileNotExploredException;
use App\Exceptions\TechnologyRequiredException;
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

        if ($tile->type?->base_type === 'pueblo') {
            throw new PuebloTileActionException();
        }

        $round = $this->tileRepo->getCurrentRound($tile->game_id);
        if ($this->tileRepo->getActionsSpent($round, $dto->userId) >= 2) {
            throw new ActionLimitExceededException();
        }

        if ($tile->explored) {
            throw new TileAlreadyExploredException();
        }

        if (!$this->tileRepo->isAdjacentToUserExplored($tile, $dto->userId)) {
            throw new TileNotAdjacentException();
        }

        $this->tileRepo->markExplored($tile, $dto->userId);
        $this->tileRepo->incrementActionsSpent($round, $dto->userId);

        $tile->refresh()->load('type');
        TileExplored::dispatch($tile, $dto->userId);

        return $tile;
    }

    public function upgrade(UpgradeActionDTO $dto): Tile
    {
        $tile = $this->tileRepo->find($dto->tileId);

        if (!$this->tileRepo->isUserInGame($dto->userId, $tile->game_id)) {
            throw new UserNotInGameException();
        }

        if ($tile->type?->base_type === 'pueblo') {
            throw new PuebloTileActionException();
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

        $requiredTech = $this->tileRepo->getRequiredTechnology($nextType);
        if ($requiredTech !== null) {
            $game = Game::find($tile->game_id);
            $isActive = $game->technologies()
                ->where('technology_id', $requiredTech->id)
                ->wherePivot('is_active', true)
                ->exists();
            if (!$isActive) {
                throw new TechnologyRequiredException($requiredTech->name);
            }
        }

        $this->tileRepo->upgradeTile($tile, $nextType);
        $this->tileRepo->incrementActionsSpent($round, $dto->userId);

        $tile->refresh()->load('type');
        TileUpgraded::dispatch($tile, $dto->userId);

        return $tile;
    }
}
