<?php

namespace App\Services;

use App\Repositories\Contracts\BoardRepositoryInterface;
use Exception;

class BoardService
{
    public function __construct(private BoardRepositoryInterface $boardRepository) {}

    public function getBoardForUser(string $gameId, string $userId)
    {
        if (!$this->boardRepository->isUserInGame($gameId, $userId)) {
            throw new Exception('Forbidden', 403);
        }

        return $this->boardRepository->getTilesByGameId($gameId);
    }
}
