<?php

namespace App\Repositories\Contracts;

use App\Models\Game;

interface GameRepositoryInterface
{
    public function create(array $data): Game;

    public function findByName(string $name): ?Game;

    public function findAvailableRandom(): ?Game;

    public function getAllAvailableGames();

    public function getGamesByUserId(string $userId);

    public function initializeMaterials(Game $game): void;
}
