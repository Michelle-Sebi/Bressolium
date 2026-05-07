<?php

namespace App\Repositories;

use App\Models\Game;
use App\Models\Tile;
use Illuminate\Support\Str;

class BoardRepository
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

    public function markAsStartingTile(string $gameId, int $x, int $y, string $userId): void
    {
        Tile::where('game_id', $gameId)
            ->where('coord_x', $x)
            ->where('coord_y', $y)
            ->update([
                'assigned_player'       => $userId,
                'explored'              => true,
                'explored_by_player_id' => $userId,
                'explored_at'           => now(),
            ]);
    }
}
