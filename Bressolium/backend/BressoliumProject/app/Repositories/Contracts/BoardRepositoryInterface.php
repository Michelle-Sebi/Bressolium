<?php

namespace App\Repositories\Contracts;

interface BoardRepositoryInterface
{
    public function getTilesByGameId(string $gameId);

    public function isUserInGame(string $gameId, string $userId): bool;

    public function createMany(array $tiles): void;
}
