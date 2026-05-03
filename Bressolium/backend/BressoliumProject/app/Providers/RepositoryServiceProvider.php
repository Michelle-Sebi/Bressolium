<?php

namespace App\Providers;

use App\Repositories\Contracts\BoardRepositoryInterface;
use App\Repositories\Contracts\GameRepositoryInterface;
use App\Repositories\Contracts\RoundRepositoryInterface;
use App\Repositories\Contracts\SyncRepositoryInterface;
use App\Repositories\Contracts\VoteRepositoryInterface;
use App\Repositories\Contracts\TileRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\BoardRepository;
use App\Repositories\Eloquent\GameRepository;
use App\Repositories\Eloquent\RoundRepository;
use App\Repositories\Eloquent\SyncRepository;
use App\Repositories\Eloquent\VoteRepository;
use App\Repositories\Eloquent\TileRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GameRepositoryInterface::class, GameRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoundRepositoryInterface::class, RoundRepository::class);
        $this->app->bind(BoardRepositoryInterface::class, BoardRepository::class);
        $this->app->bind(TileRepositoryInterface::class, TileRepository::class);
        $this->app->bind(SyncRepositoryInterface::class, SyncRepository::class);
        $this->app->bind(VoteRepositoryInterface::class, VoteRepository::class);
    }
}
