<?php

namespace App\Services;

use App\Events\InventionBuilt;
use App\Events\MaterialsProduced;
use App\Events\RoundClosed;
use App\Models\Game;
use App\Models\Round;
use App\Repositories\Contracts\CloseRoundRepositoryInterface;

class CloseRoundService
{
    public function __construct(private readonly CloseRoundRepositoryInterface $repository) {}

    public function process(string $gameId): void
    {
        $game  = $this->repository->findGameWithUsers($gameId);
        $round = $this->repository->getLatestRound($game);

        $this->resolveTechnologyWinner($game, $round);
        $this->resolveInventionWinner($game, $round);

        $this->repository->produceMaterialsFromExploredTiles($game);
        MaterialsProduced::dispatch($game);

        $newRound = $this->repository->createNextRound($game);
        $this->repository->initializePlayersForRound($newRound, $game);

        RoundClosed::dispatch($game, $round);
    }

    private function resolveTechnologyWinner(Game $game, Round $round): void
    {
        $technologyId = $this->repository->getMostVotedTechnologyId($round);

        if ($technologyId) {
            $this->repository->activateTechnology($game, $technologyId);
        }
    }

    private function resolveInventionWinner(Game $game, Round $round): void
    {
        $inventionId = $this->repository->getMostVotedInventionId($round);

        if (!$inventionId) {
            return;
        }

        $invention = $this->repository->getInventionWithDependencies($inventionId);

        if (!$invention) {
            return;
        }

        if (!$this->repository->inventionPrerequisitesMet($game, $invention)) {
            return;
        }

        if (!$this->repository->inventionResourcesMet($game, $invention)) {
            return;
        }

        $this->repository->buildInvention($game, $invention);
        InventionBuilt::dispatch($game, $invention);
    }
}
