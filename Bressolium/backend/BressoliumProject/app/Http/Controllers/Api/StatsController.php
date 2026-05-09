<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;
use App\Support\ResponseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function __construct(private ResponseBuilder $rb) {}

    public function stats(): JsonResponse
    {
        return $this->rb->success([
            'system' => $this->getSystemStats(),
            'game'   => $this->getGameStats(),
        ]);
    }

    // ─── System ──────────────────────────────────────────────────────────────

    private function getSystemStats(): array
    {
        return [
            'uptime'              => $this->getUptime(),
            'database'            => $this->getDatabaseStatus(),
            'requests_per_minute' => $this->getRequestsPerMinute(),
            'errors_per_minute'   => $this->getErrorsPerMinute(),
            'latency_p95'         => $this->getLatencyP95(),
        ];
    }

    private function getUptime(): int
    {
        return max(0, time() - (int) filemtime(base_path('artisan')));
    }

    private function getDatabaseStatus(): string
    {
        try {
            DB::connection()->getPdo();
            return 'ok';
        } catch (\Throwable) {
            return 'error';
        }
    }

    private function getRequestsPerMinute(): int
    {
        try {
            return \Laravel\Telescope\Storage\EntryModel
                ::where('type', 'request')
                ->where('created_at', '>=', now()->subMinute())
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function getErrorsPerMinute(): int
    {
        try {
            return \Laravel\Telescope\Storage\EntryModel
                ::where('type', 'exception')
                ->where('created_at', '>=', now()->subMinute())
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function getLatencyP95(): float
    {
        try {
            $durations = \Laravel\Telescope\Storage\EntryModel
                ::where('type', 'request')
                ->where('created_at', '>=', now()->subMinute())
                ->get()
                ->map(fn ($e) => data_get(json_decode($e->content, true), 'duration'))
                ->filter()
                ->sort()
                ->values();

            if ($durations->isEmpty()) return 0.0;

            $index = max(0, (int) ceil(0.95 * $durations->count()) - 1);
            return (float) ($durations[$index] ?? 0.0);
        } catch (\Throwable) {
            return 0.0;
        }
    }

    // ─── Game ─────────────────────────────────────────────────────────────────

    private function getGameStats(): array
    {
        $gamesByStatus = Game::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $players = User::withCount('games')
            ->orderByDesc('games_count')
            ->get(['id', 'name', 'games_count']);

        return [
            'total_games'    => Game::count(),
            'waiting_games'  => (int) ($gamesByStatus['WAITING']  ?? 0),
            'active_games'   => (int) ($gamesByStatus['ACTIVE']   ?? 0),
            'finished_games' => (int) ($gamesByStatus['FINISHED'] ?? 0),
            'total_players'  => User::count(),
            'total_rounds'   => Round::count(),
            'players'        => $players->map(fn ($u) => [
                'name'        => $u->name,
                'games_count' => $u->games_count,
            ])->values(),
        ];
    }
}
