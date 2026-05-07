<?php

namespace App\Events;

use App\Models\Tile;
use Illuminate\Foundation\Events\Dispatchable;

class TileUpgraded
{
    use Dispatchable;

    public function __construct(
        public readonly Tile $tile,
        public readonly string $userId,
    ) {}
}
