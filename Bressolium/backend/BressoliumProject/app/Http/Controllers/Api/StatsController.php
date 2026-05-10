<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Round;
use App\Models\User;
use App\Support\ResponseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Laravel\Telescope\Storage\EntryModel;

class StatsController extends Controller
{
    public function __construct(private ResponseBuilder $rb) {}

    /**
     * @OA\Get(
     *     path="/stats",
     *     summary="Métricas del sistema y del juego",
     *     description="Endpoint público (no requiere autenticación). Devuelve métricas operativas del sistema (uptime, estado de la BD, requests/min, errores/min, latencia p95) y métricas de negocio (totales de partidas, jugadores y rondas).",
     *     tags={"Stats"},
     *     @OA\Response(
     *         response=200,
     *         description="Métricas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="system", type="object",
     *                     @OA\Property(property="uptime", type="integer", description="Segundos desde la última modificación de artisan"),
     *                     @OA\Property(property="database", type="string", enum={"ok","error"}, example="ok"),
     *                     @OA\Property(property="requests_per_minute", type="integer"),
     *                     @OA\Property(property="errors_per_minute", type="integer"),
     *                     @OA\Property(property="latency_p95", type="number", format="float")
     *                 ),
     *                 @OA\Property(property="game", type="object",
     *                     @OA\Property(property="total_games", type="integer"),
     *                     @OA\Property(property="waiting_games", type="integer"),
     *                     @OA\Property(property="active_games", type="integer"),
     *                     @OA\Property(property="finished_games", type="integer"),
     *                     @OA\Property(property="total_players", type="integer"),
     *                     @OA\Property(property="total_rounds", type="integer"),
     *                     @OA\Property(property="players", type="array", @OA\Items(type="object"))
     *                 )
     *             ),
     *             @OA\Property(property="error", type="string", nullable=true)
     *         )
     *     )
     * )
     */
    public function stats(): JsonResponse
    {
        return $this->rb->success([
            'system' => $this->getSystemStats(),
            'game' => $this->getGameStats(),
        ]);
    }

    // ─── System ──────────────────────────────────────────────────────────────

    private function getSystemStats(): array
    {
        return [
            'uptime' => $this->getUptime(),
            'database' => $this->getDatabaseStatus(),
            'requests_per_minute' => $this->getRequestsPerMinute(),
            'errors_per_minute' => $this->getErrorsPerMinute(),
            'latency_p95' => $this->getLatencyP95(),
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
            return EntryModel::where('type', 'request')
                ->where('created_at', '>=', now()->subMinute())
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function getErrorsPerMinute(): int
    {
        try {
            return EntryModel::where('type', 'exception')
                ->where('created_at', '>=', now()->subMinute())
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function getLatencyP95(): float
    {
        try {
            $durations = EntryModel::where('type', 'request')
                ->where('created_at', '>=', now()->subMinute())
                ->get()
                ->map(fn ($e) => data_get(json_decode($e->content, true), 'duration'))
                ->filter()
                ->sort()
                ->values();

            if ($durations->isEmpty()) {
                return 0.0;
            }

            $index = max(0, (int) ceil(0.95 * $durations->count()) - 1);

            return (float) ($durations[$index] ?? 0.0);
        } catch (\Throwable) {
            return 0.0;
        }
    }

    // ─── Game ─────────────────────────────────────────────────────────────────

    private function getGameStats(): array
    {
        try {
            $gamesByStatus = Game::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $players = User::withCount('games')
                ->orderByDesc('games_count')
                ->get(['id', 'name', 'games_count']);

            return [
                'total_games' => Game::count(),
                'waiting_games' => (int) ($gamesByStatus['WAITING'] ?? 0),
                'active_games' => (int) ($gamesByStatus['ACTIVE'] ?? 0),
                'finished_games' => (int) ($gamesByStatus['FINISHED'] ?? 0),
                'total_players' => User::count(),
                'total_rounds' => Round::count(),
                'players' => $players->map(fn ($u) => [
                    'name' => $u->name,
                    'games_count' => $u->games_count,
                ])->values(),
            ];
        } catch (\Throwable) {
            return [
                'total_games' => 0,
                'waiting_games' => 0,
                'active_games' => 0,
                'finished_games' => 0,
                'total_players' => 0,
                'total_rounds' => 0,
                'players' => [],
            ];
        }
    }
}
