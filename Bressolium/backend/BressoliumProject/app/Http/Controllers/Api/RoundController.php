<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\CloseRoundJob;
use App\Models\Game;
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

        if (! $game->users()->where('user_id', $request->user()->id)->exists()) {
            return $this->rb->error('No perteneces a esta partida.', 403);
        }

        CloseRoundJob::dispatchSync($gameId);

        return $this->rb->success(['message' => 'Jornada cerrada correctamente.']);
    }
}
