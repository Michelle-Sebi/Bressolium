<?php

namespace App\DTOs;

final readonly class SyncResponseDTO
{
    public function __construct(
        public array $currentRound,
        public array $userActions,
        public array $inventory,
        public array $technologies,
        public array $inventions,
        public bool $hasVoted = false,
        public bool $hasFinished = false,
        public array $lastRoundResult = [],
    ) {}
}
