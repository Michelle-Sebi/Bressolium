<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class VoteCast
{
    use Dispatchable;

    public function __construct(
        public readonly string $userId,
        public readonly string $gameId,
    ) {}
}
