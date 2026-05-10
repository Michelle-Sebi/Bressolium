<?php

namespace App\Services;

use Closure;
use Illuminate\Cache\Repository;

class CacheService
{
    public function __construct(private Repository $cache) {}

    public function rememberBoard(string $gameId, Closure $callback): mixed
    {
        return $this->cache->remember("board:{$gameId}", 300, $callback);
    }

    public function rememberSync(string $gameId, string $userId, Closure $callback): mixed
    {
        return $this->cache->remember("sync:{$gameId}:{$userId}", 30, $callback);
    }

    public function invalidateBoard(string $gameId): void
    {
        $this->cache->forget("board:{$gameId}");
    }
}
