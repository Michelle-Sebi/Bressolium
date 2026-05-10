<?php

namespace App\Services;

use App\DTOs\SyncResponseDTO;
use App\Models\Game;
use App\Repositories\Contracts\SyncRepositoryInterface;

class SyncService
{
    public function __construct(
        private SyncRepositoryInterface $syncRepository,
    ) {}

    public function sync(Game $game, string $userId): SyncResponseDTO
    {
        $round = $this->syncRepository->getCurrentRound($game->id);

        return new SyncResponseDTO(
            currentRound: $round ? [
                'number' => $round->number,
                'start_date' => $round->start_date,
            ] : [],
            userActions: [
                'actions_spent' => $round
                    ? $this->syncRepository->getActionsSpent($round, $userId)
                    : 0,
            ],
            inventory: $this->syncRepository->getInventory($game),
            technologies: $this->syncRepository->getTechnologies($game),
            inventions: $this->syncRepository->getInventions($game),
            hasVoted: $round
                ? $this->syncRepository->hasVotedThisRound($round, $userId)
                : false,
            lastRoundResult: $round
                ? $this->syncRepository->getLastRoundResult($round)
                : [],
        );
    }
}
