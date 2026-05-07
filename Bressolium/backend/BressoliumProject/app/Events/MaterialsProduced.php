<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Foundation\Events\Dispatchable;

class MaterialsProduced
{
    use Dispatchable;

    public function __construct(
        public readonly Game $game,
    ) {}
}
