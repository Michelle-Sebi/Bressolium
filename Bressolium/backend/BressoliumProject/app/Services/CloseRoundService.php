<?php

namespace App\Services;

use App\Events\GameFinished;
use App\Events\InventionBuilt;
use App\Events\MaterialsProduced;
use App\Events\RoundClosed;
use App\Jobs\ExpireRoundJob;
use App\Models\Game;
use App\Models\Round;
use App\Repositories\Contracts\CloseRoundRepositoryInterface;

class CloseRoundService
{
    public function __construct(
        private readonly CloseRoundRepositoryInterface $repository,
        private readonly CacheService $cacheService,
    ) {}

    public function process(string $gameId): void
    {
        $game  = $this->repository->findGameWithUsers($gameId);
        $round = $this->repository->getLatestRound($game);

        // Guard against double-processing (e.g. timer + button pressed simultaneously)
        if ($round->ended_at !== null) {
            return;
        }

        $this->repository->markRoundEnded($round);

        $this->resolveTechnologyWinner($game, $round);
        $gameFinished = $this->resolveInventionWinner($game, $round);

        if ($gameFinished) {
            return;
        }

        $this->repository->produceMaterialsFromExploredTiles($game);
        MaterialsProduced::dispatch($game);

        $this->repository->markAfkPlayers($round, $game);

        $newRound = $this->repository->createNextRound($game);
        $this->repository->initializePlayersForRound($newRound, $game);

        ExpireRoundJob::dispatch($newRound->id, $game->id)->delay(now()->addHours(2));

        $this->invalidateSyncCacheForAllPlayers($game);

        RoundClosed::dispatch($game, $round);
    }

    private function invalidateSyncCacheForAllPlayers(Game $game): void
    {
        foreach ($game->users as $user) {
            $this->cacheService->invalidateSync($game->id, $user->id);
        }
        $this->cacheService->invalidateBoard($game->id);
    }

    private function resolveTechnologyWinner(Game $game, Round $round): void
    {
        $vote = $this->repository->resolveTechnologyVote($round);

        if ($vote) {
            $this->repository->activateTechnology($game, $vote['id']);
            $this->repository->markRoundTechResult($round, $vote['id'], $vote['is_tie']);
        }
    }

    private function resolveInventionWinner(Game $game, Round $round): bool
    {
        $vote = $this->repository->resolveInventionVote($round);

        if (! $vote) {
            return false;
        }

        $invention = $this->repository->getInventionWithDependencies($vote['id']);

        if (! $invention) {
            return false;
        }

        if (! $this->repository->inventionPrerequisitesMet($game, $invention)) {
            return false;
        }

        if (! $this->repository->inventionResourcesMet($game, $invention)) {
            return false;
        }

        $this->repository->buildInvention($game, $invention);
        $this->repository->markRoundResult($round, $vote['id'], $vote['is_tie']);
        InventionBuilt::dispatch($game, $invention);

        if ($invention->is_final) {
            $this->repository->finishGame($game);
            GameFinished::dispatch($game);

            return true;
        }

        return false;
    }
}
