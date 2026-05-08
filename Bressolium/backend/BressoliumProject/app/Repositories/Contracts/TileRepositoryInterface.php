<?php

namespace App\Repositories\Contracts;

use App\Models\Round;
use App\Models\Technology;
use App\Models\Tile;
use App\Models\TileType;

interface TileRepositoryInterface
{
    public function find(string $id): ?Tile;

    public function isUserInGame(string $userId, string $gameId): bool;

    public function getCurrentRound(string $gameId): ?Round;

    public function getActionsSpent(Round $round, string $userId): int;

    public function incrementActionsSpent(Round $round, string $userId): void;

    public function markExplored(Tile $tile, string $userId): void;

    public function findNextTileType(Tile $tile): ?TileType;

    public function getRequiredTechnology(TileType $nextType): ?Technology;

    public function upgradeTile(Tile $tile, TileType $nextType): void;

    public function isAdjacentToUserExplored(Tile $tile, string $userId): bool;
}
