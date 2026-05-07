<?php

namespace App\Services;

use App\Events\GameFinished;
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
        $gameFinished = $this->resolveInventionWinner($game, $round);

        if ($gameFinished) {
            return;
        }

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

    private function resolveInventionWinner(Game $game, Round $round): bool
    {
        $inventionId = $this->repository->getMostVotedInventionId($round);

        if (!$inventionId) {
            return false;
        }

        $invention = $this->repository->getInventionWithDependencies($inventionId);

        if (!$invention) {
            return false;
        }

        if (!$this->repository->inventionPrerequisitesMet($game, $invention)) {
            return false;
        }

        if (!$this->repository->inventionResourcesMet($game, $invention)) {
            return false;
        }

        $this->repository->buildInvention($game, $invention);
        InventionBuilt::dispatch($game, $invention);

        if ($invention->is_final) {
            $this->repository->finishGame($game);
            GameFinished::dispatch($game);
            return true;
        }

        return false;
    }
}
