<?php

namespace App\DTOs;

final readonly class ExploreActionDTO
{
    public function __construct(
        public string $tileId,
        public string $userId,
    ) {}
}
