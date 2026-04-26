<?php

namespace App\DTOs;

final readonly class CreateGameDTO
{
    public function __construct(
        public string $teamName,
        public string $userId,
    ) {}
}
