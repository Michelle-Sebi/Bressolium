<?php

namespace App\Services;

use App\Jobs\CloseRoundJob;
use App\Repositories\Contracts\CloseRoundRepositoryInterface;

class RoundProgressService
{
    public function __construct(private readonly CloseRoundRepositoryInterface $repo) {}

    /**
     * Marks a player as finished if they have spent 2 actions AND voted.
     * Then triggers round closure if all players are done.
     */
    public function markDoneIfReady(string $gameId, string $userId): void
    {
        $game  = $this->repo->findGameWithUsers($gameId);
        $round = $this->repo->getLatestRound($game);

        if ($round->ended_at !== null) {
            return;
        }

        if ($this->repo->isPlayerFinished($round, $userId)) {
            return;
        }

        $actionsSpent  = $this->repo->getPlayerActionsSpent($round, $userId);
        $hasVotedTech  = $this->repo->hasPlayerVotedForTechnology($round, $userId);
        $hasVotedInv   = $this->repo->hasPlayerVotedForInvention($round, $userId);

        if ($actionsSpent >= 2 && $hasVotedTech && $hasVotedInv) {
            $this->repo->setPlayerFinishedAt($round, $userId);

            $total    = $game->users()->count();
            $finished = $this->repo->countFinishedPlayers($round);

            if ($finished >= $total) {
                try {
                    CloseRoundJob::dispatchSync($gameId);
                } catch (\Throwable $e) {
                    $this->repo->clearPlayerFinishedAt($round, $userId);
                    throw $e;
                }
            }
        }
    }
}
