<?php

namespace App\Services;

use App\Repositories\Contracts\BoardRepositoryInterface;
use Exception;

class BoardService
{
    public function __construct(
        private BoardRepositoryInterface $boardRepository,
        private CacheService $cacheService,
    ) {}

    public function getBoardForUser(string $gameId, string $userId): array
    {
        if (! $this->boardRepository->isUserInGame($gameId, $userId)) {
            throw new Exception('Forbidden', 403);
        }

        return $this->cacheService->rememberBoard(
            $gameId,
            fn () => $this->boardRepository->getTilesByGameId($gameId)
                ->map(fn ($tile) => [
                    'id'                   => $tile->id,
                    'coord_x'              => $tile->coord_x,
                    'coord_y'              => $tile->coord_y,
                    'tile_type_id'         => $tile->tile_type_id,
                    'explored'             => (bool) $tile->explored,
                    'explored_by_player_id' => $tile->explored_by_player_id,
                    'type'         => $tile->type ? [
                        'id'        => $tile->type->id,
                        'name'      => $tile->type->name,
                        'base_type' => $tile->type->base_type,
                        'level'     => $tile->type->level,
                    ] : null,
                ])
                ->toArray(),
        );
    }
}
