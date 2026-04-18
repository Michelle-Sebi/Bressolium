<?php

namespace App\Services;

use App\Repositories\BoardRepository;
use Exception;

class BoardService
{
    public function __construct(private BoardRepository $boardRepository) {}

    public function getBoardForUser(string $gameId, string $userId)
    {
        if (!$this->boardRepository->isUserInGame($gameId, $userId)) {
            throw new Exception('Forbidden', 403);
        }

        return $this->boardRepository->getTilesByGameId($gameId);
    }
}
