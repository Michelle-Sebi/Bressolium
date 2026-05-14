<?php

namespace App\Listeners;

use App\Events\VoteCast;
use App\Services\RoundProgressService;

class CheckQuorumOnVoteCast
{
    public function __construct(private RoundProgressService $progress) {}

    public function handle(VoteCast $event): void
    {
        $this->progress->markDoneIfReady($event->gameId, $event->userId);
    }
}
