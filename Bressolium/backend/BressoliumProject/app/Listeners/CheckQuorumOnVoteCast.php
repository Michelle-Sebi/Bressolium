<?php

namespace App\Listeners;

use App\Events\VoteCast;
use App\Jobs\CloseRoundJob;
use App\Repositories\Contracts\CloseRoundRepositoryInterface;

class CheckQuorumOnVoteCast
{
    public function __construct(private CloseRoundRepositoryInterface $repository) {}

    public function handle(VoteCast $event): void
    {
        $game  = $this->repository->findGameWithUsers($event->gameId);
        $round = $this->repository->getLatestRound($game);

        if ($this->repository->allNonAfkPlayersHaveVoted($round, $game)) {
            CloseRoundJob::dispatchSync($event->gameId);
        }
    }
}
