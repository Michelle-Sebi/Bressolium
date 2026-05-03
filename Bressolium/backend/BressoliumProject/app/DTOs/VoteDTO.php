<?php

namespace App\DTOs;

final readonly class VoteDTO
{
    public function __construct(
        public string  $gameId,
        public string  $userId,
        public ?string $technologyId = null,
        public ?string $inventionId  = null,
    ) {}
}
