<?php

namespace App\DTOs;

final readonly class UpgradeActionDTO
{
    public function __construct(
        public string $tileId,
        public string $userId,
    ) {}
}
