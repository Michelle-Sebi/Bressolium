<?php

namespace App\Repositories\Eloquent;

use App\Models\Game;
use App\Models\Tile;
use App\Repositories\Contracts\BoardRepositoryInterface;
use Illuminate\Support\Str;

class BoardRepository implements BoardRepositoryInterface
{
    public function getTilesByGameId(string $gameId)
    {
        return Tile::where('game_id', $gameId)
            ->with('type')
            ->orderBy('coord_x')
            ->orderBy('coord_y')
            ->get();
    }

    public function isUserInGame(string $gameId, string $userId): bool
    {
        return Game::where('id', $gameId)
            ->whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->exists();
    }

    public function createMany(array $tiles): void
    {
        $now  = now();
        $rows = array_map(fn ($tile) => array_merge([
            'id'         => (string) Str::uuid(),
            'explored'   => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ], $tile), $tiles);

        Tile::insert($rows);
    }
}
