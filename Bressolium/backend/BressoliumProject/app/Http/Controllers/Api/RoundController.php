<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\CloseRoundJob;
use App\Models\Game;
use App\Models\Round;
use App\Support\ResponseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    public function __construct(private ResponseBuilder $rb) {}

    /**
     * @OA\Post(
     *     path="/game/{gameId}/close-round",
     *     summary="Cierra la jornada actual y genera la siguiente",
     *     description="Resuelve los votos de tecnologías e inventos, activa los más votados, produce materiales según las casillas exploradas, marca jugadores AFK y crea la siguiente jornada.",
     *     tags={"Round"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="gameId",
     *         in="path",
     *         required=true,
     *         description="UUID de la partida",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jornada cerrada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="Jornada cerrada correctamente.")
     *             ),
     *             @OA\Property(property="error", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=403, description="No perteneces a esta partida", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=404, description="Partida no encontrada", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function close(Request $request, string $gameId): JsonResponse
    {
        $game = Game::find($gameId);

        if (! $game) {
            return $this->rb->error('Partida no encontrada.', 404);
        }

        $userId = $request->user()->id;

        if (! $game->users()->where('user_id', $userId)->exists()) {
            return $this->rb->error('No perteneces a esta partida.', 403);
        }

        $round = Round::where('game_id', $gameId)->whereNull('ended_at')->latest('number')->first();

        if (! $round) {
            return $this->rb->error('No hay jornada activa.', 404);
        }

        // Marcar a este jugador como listo para terminar
        $round->users()->updateExistingPivot($userId, ['finished_at' => now()]);

        // Cerrar la jornada solo cuando todos los jugadores han terminado
        $totalPlayers    = $game->users()->count();
        $finishedPlayers = $round->users()->wherePivotNotNull('finished_at')->count();

        if ($finishedPlayers >= $totalPlayers) {
            try {
                CloseRoundJob::dispatchSync($gameId);
            } catch (\Throwable $e) {
                // Revert finished_at so the player can retry — avoids permanent deadlock
                $round->users()->updateExistingPivot($userId, ['finished_at' => null]);
                throw $e;
            }
            return $this->rb->success(['message' => 'Jornada cerrada correctamente.']);
        }

        return $this->rb->success(['message' => 'Listo. Esperando al resto de jugadores.']);
    }
}
