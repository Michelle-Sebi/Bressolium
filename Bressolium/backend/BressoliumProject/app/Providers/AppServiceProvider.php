<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\Tile;
use App\Policies\GamePolicy;
use App\Policies\TilePolicy;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Repository::class, fn () => $this->app['cache.store']);
    }

    public function boot(): void
    {
        Gate::policy(Game::class, GamePolicy::class);
        Gate::policy(Tile::class, TilePolicy::class);
    }
}
