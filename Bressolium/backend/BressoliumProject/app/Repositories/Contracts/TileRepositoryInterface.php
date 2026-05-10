<?php

namespace App\Repositories\Contracts;

use App\Models\Game;
use App\Models\Round;
use App\Models\Technology;
use App\Models\Tile;
use App\Models\TileType;
use Illuminate\Support\Collection;

interface TileRepositoryInterface
{
    public function find(string $id): ?Tile;

    public function isUserInGame(string $userId, string $gameId): bool;

    public function getCurrentRound(string $gameId): ?Round;

    public function getActionsSpent(Round $round, string $userId): int;

    public function incrementActionsSpent(Round $round, string $userId): void;

    public function markExplored(Tile $tile, string $userId): void;

    public function findNextTileType(Tile $tile): ?TileType;

    public function getUpgradeCosts(TileType $nextType): Collection;

    public function hasSufficientMaterials(Game $game, Collection $costs): bool;

    public function deductMaterials(Game $game, Collection $costs): void;

    public function getRequiredTechnology(TileType $nextType): ?Technology;

    public function upgradeTile(Tile $tile, TileType $nextType): void;

    public function isAdjacentToUserExplored(Tile $tile, string $userId): bool;
}
