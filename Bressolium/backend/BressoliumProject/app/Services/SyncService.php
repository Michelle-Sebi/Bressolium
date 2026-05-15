<?php

namespace App\Services;

use App\DTOs\SyncResponseDTO;
use App\Models\Game;
use App\Repositories\Contracts\SyncRepositoryInterface;

class SyncService
{
    public function __construct(
        private SyncRepositoryInterface $syncRepository,
        private CacheService $cacheService,
    ) {}

    public function sync(Game $game, string $userId): SyncResponseDTO
    {
        // Cache a plain array — immune to class-shape changes (avoids __PHP_Incomplete_Class on DTO evolution)
        $data = $this->cacheService->rememberSync(
            $game->id,
            $userId,
            function () use ($game, $userId) {
                $round = $this->syncRepository->getCurrentRound($game->id);

                return [
                    'currentRound' => $round ? [
                        'number'     => $round->number,
                        'start_date' => $round->start_date,
                    ] : [],
                    'userActions' => [
                        'actions_spent' => $round
                            ? $this->syncRepository->getActionsSpent($round, $userId)
                            : 0,
                    ],
                    'inventory'       => $this->syncRepository->getInventory($game),
                    'technologies'    => $this->syncRepository->getTechnologies($game),
                    'inventions'      => $this->syncRepository->getInventions($game),
                    'hasVoted'        => $round ? $this->syncRepository->hasVotedThisRound($round, $userId)      : false,
                    'hasVotedTech'    => $round ? $this->syncRepository->hasVotedForTechnology($round, $userId) : false,
                    'hasVotedInv'     => $round ? $this->syncRepository->hasVotedForInvention($round, $userId)  : false,
                    'hasFinished'     => $round ? $this->syncRepository->hasFinishedRound($round, $userId)      : false,
                    'lastRoundResult' => $round ? $this->syncRepository->getLastRoundResult($round)             : [],
                    'gameStatus'      => $game->status,
                    'playersCount'    => $game->users()->count(),
                ];
            },
        );

        return new SyncResponseDTO(
            currentRound:    $data['currentRound'],
            userActions:     $data['userActions'],
            inventory:       $data['inventory'],
            technologies:    $data['technologies'],
            inventions:      $data['inventions'],
            hasVoted:        $data['hasVoted'],
            hasVotedTech:    $data['hasVotedTech'],
            hasVotedInv:     $data['hasVotedInv'],
            hasFinished:     $data['hasFinished'],
            lastRoundResult: $data['lastRoundResult'],
            gameStatus:      $data['gameStatus'],
            playersCount:    $data['playersCount'],
        );
    }
}
