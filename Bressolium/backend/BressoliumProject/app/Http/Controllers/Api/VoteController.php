<?php

namespace App\Http\Controllers\Api;

use App\DTOs\VoteDTO;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\VoteService;
use App\Support\ResponseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function __construct(
        private VoteService $voteService,
        private ResponseBuilder $rb,
    ) {}

    /**
     * @OA\Post(
     *     path="/game/{gameId}/vote",
     *     summary="Vota una tecnología o un invento en la jornada actual",
     *     description="El usuario vota por una tecnología y/o un invento en la jornada actual. Se puede votar en ambas categorías (una llamada con ambos campos o dos llamadas separadas). No se puede repetir el voto en la misma categoría. Enviar ambos campos a null lanza un error de validación. La jornada se cierra automáticamente cuando todos los jugadores han completado 2 acciones y han votado en ambas categorías.",
     *     tags={"Vote"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="gameId",
     *         in="path",
     *         required=true,
     *         description="UUID de la partida",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="technology_id", type="string", format="uuid", nullable=true, example="01HFG..."),
     *             @OA\Property(property="invention_id", type="string", format="uuid", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Voto registrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="vote_id", type="string", format="uuid")
     *             ),
     *             @OA\Property(property="error", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=403, description="No perteneces a esta partida", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=404, description="Partida no encontrada", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=422, description="Ya votaste en esa categoría / tecnología ya investigada / materiales insuficientes / ningún campo enviado", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function vote(Request $request, string $gameId): JsonResponse
    {
        $game = Game::find($gameId);

        if (! $game) {
            return $this->rb->error('Partida no encontrada.', 404);
        }

        $userId = $request->user()->id;

        if (! $game->users()->where('user_id', $userId)->exists()) {
            return $this->rb->error('No perteneces a esta partida.', 403);
        }

        $dto = new VoteDTO(
            gameId: $gameId,
            userId: $userId,
            technologyId: $request->input('technology_id'),
            inventionId: $request->input('invention_id'),
        );

        $vote = $this->voteService->vote($dto);

        return $this->rb->success(['vote_id' => $vote->id]);
    }
}
