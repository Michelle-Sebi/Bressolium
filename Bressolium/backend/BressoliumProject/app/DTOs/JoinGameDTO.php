<?php

namespace App\DTOs;

final readonly class JoinGameDTO
{
    public function __construct(
        public string $teamName,
        public string $userId,
    ) {}
}
