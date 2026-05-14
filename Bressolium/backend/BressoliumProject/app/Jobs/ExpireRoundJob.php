<?php

namespace App\Jobs;

use App\Models\Round;
use App\Repositories\Contracts\CloseRoundRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireRoundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $roundId,
        private readonly string $gameId,
    ) {}

    public function handle(CloseRoundRepositoryInterface $repo): void
    {
        $round = Round::find($this->roundId);

        if (! $round) {
            return;
        }

        // Skip if this round was already closed (a newer round exists)
        $latestRound = Round::where('game_id', $this->gameId)->latest('number')->first();
        if (! $latestRound || $latestRound->id !== $round->id) {
            return;
        }

        $game = $repo->findGameWithUsers($this->gameId);

        // Mark all players who haven't finished yet as done (timer ran out)
        $repo->markAllUnfinishedPlayersAsDone($round, $game);

        $total    = $game->users()->count();
        $finished = $repo->countFinishedPlayers($round);

        if ($finished >= $total) {
            CloseRoundJob::dispatch($this->gameId);
        }
    }
}
