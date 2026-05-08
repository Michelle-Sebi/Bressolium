<?php

namespace App\Providers;

use App\Events\VoteCast;
use App\Listeners\CheckQuorumOnVoteCast;
use App\Models\Game;
use App\Models\Tile;
use App\Policies\GamePolicy;
use App\Policies\TilePolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Game::class, GamePolicy::class);
        Gate::policy(Tile::class, TilePolicy::class);

        Event::listen(VoteCast::class, CheckQuorumOnVoteCast::class);
    }
}
