<?php

namespace App\Events;

use App\Models\Game;
use App\Models\Invention;
use Illuminate\Foundation\Events\Dispatchable;

class InventionBuilt
{
    use Dispatchable;

    public function __construct(
        public readonly Game $game,
        public readonly Invention $invention,
    ) {}
}
