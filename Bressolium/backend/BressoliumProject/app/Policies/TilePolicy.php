<?php

namespace App\Policies;

use App\Models\Tile;
use App\Models\User;

class TilePolicy
{
    public function explore(User $user, Tile $tile): bool
    {
        return $tile->game->users()->where('users.id', $user->id)->exists();
    }

    public function upgrade(User $user, Tile $tile): bool
    {
        return $tile->game->users()->where('users.id', $user->id)->exists();
    }
}
