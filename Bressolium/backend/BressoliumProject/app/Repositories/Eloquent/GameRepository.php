<?php

namespace App\Repositories\Eloquent;

use App\Models\Game;
use App\Models\Material;
use App\Repositories\Contracts\GameRepositoryInterface;

class GameRepository implements GameRepositoryInterface
{
    public function create(array $data): Game
    {
        return Game::create($data);
    }

    public function findByName(string $name): ?Game
    {
        return Game::where('name', $name)->first();
    }

    public function findAvailableRandom(): ?Game
    {
        return Game::withCount('users')
            ->having('users_count', '<', 5)
            ->first();
    }

    public function getAllAvailableGames()
    {
        return Game::withCount('users')
            ->having('users_count', '<', 5)
            ->get();
    }

    public function getGamesByUserId(string $userId)
    {
        return Game::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('users:id,name')->get();
    }

    public function initializeMaterials(Game $game): void
    {
        $materialIds = Material::pluck('id');
        $syncData = $materialIds->mapWithKeys(fn ($id) => [$id => ['quantity' => 0]])->all();
        $game->materials()->sync($syncData);
    }
}
