<?php

namespace App\Events;

use App\Models\Game;
use App\Models\Round;
use Illuminate\Foundation\Events\Dispatchable;

class RoundClosed
{
    use Dispatchable;

    public function __construct(
        public readonly Game $game,
        public readonly Round $round,
    ) {}
}
