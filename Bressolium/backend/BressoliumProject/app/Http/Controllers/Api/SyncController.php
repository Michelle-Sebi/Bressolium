<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncRequest;
use App\Http\Resources\SyncResource;
use App\Models\Game;
use App\Services\SyncService;
use App\Support\ResponseBuilder;
use Illuminate\Http\JsonResponse;

class SyncController extends Controller
{
    public function __construct(
        private SyncService $syncService,
        private ResponseBuilder $rb,
    ) {}

    /**
     * @OA\Get(
     *     path="/game/{gameId}/sync",
     *     summary="Estado completo del juego para el frontend",
     *     description="Devuelve la jornada actual, acciones gastadas, inventario, tecnologías, inventos, estado de voto del jugador y el resultado de la última jornada. Es el endpoint que el frontend pollea cada 30 segundos.",
     *     tags={"Sync"},
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
     *         description="Estado del juego",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_round", type="object",
     *                     @OA\Property(property="number", type="integer", example=3),
     *                     @OA\Property(property="start_date", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(property="user_actions", type="object",
     *                     @OA\Property(property="actions_spent", type="integer", example=1)
     *                 ),
     *                 @OA\Property(property="inventory", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="progress", type="object",
     *                     @OA\Property(property="technologies", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="inventions", type="array", @OA\Items(type="object"))
     *                 ),
     *                 @OA\Property(property="has_voted", type="boolean", example=false, description="true si ha votado en alguna categoría"),
     *                 @OA\Property(property="has_voted_tech", type="boolean", example=false, description="true si ha votado por tecnología"),
     *                 @OA\Property(property="has_voted_inv", type="boolean", example=false, description="true si ha votado por invento"),
     *                 @OA\Property(property="has_finished", type="boolean", example=false, description="true si completó acciones y votos"),
     *                 @OA\Property(property="game_status", type="string", enum={"WAITING","ACTIVE","COMPLETA","FINISHED"}),
     *                 @OA\Property(property="players_count", type="integer", example=3),
     *                 @OA\Property(property="last_round_result", type="object", nullable=true, description="Resultado de la jornada anterior: no_consensus_inv/tech, built_inv_name, built_tech_name")
     *             ),
     *             @OA\Property(property="error", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=403, description="No perteneces a esta partida", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=404, description="Partida no encontrada", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function sync(SyncRequest $request, string $gameId): JsonResponse
    {
        $game = Game::find($gameId);

        if (! $game) {
            return $this->rb->error('Partida no encontrada.', 404);
        }

        $userId = $request->user()->id;

        if (! $game->users()->where('user_id', $userId)->exists()) {
            return $this->rb->error('No perteneces a esta partida.', 403);
        }

        $dto = $this->syncService->sync($game, $userId);

        return $this->rb->success((new SyncResource($dto))->toArray($request));
    }
}
