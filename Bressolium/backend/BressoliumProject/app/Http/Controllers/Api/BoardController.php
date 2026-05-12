<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\BoardService;
use App\Support\ResponseBuilder;
use Exception;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function __construct(
        private BoardService $boardService,
        private ResponseBuilder $rb,
    ) {}

    /**
     * @OA\Get(
     *     path="/board/{gameId}",
     *     summary="Obtiene el tablero completo de una partida",
     *     description="Devuelve todas las casillas de la partida con su estado actual de exploración. La respuesta se cachea por game_id y se invalida cuando se realiza alguna acción sobre el tablero.",
     *     tags={"Board"},
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
     *         description="Tablero de 15x15 casillas",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="coord_x", type="integer"),
     *                 @OA\Property(property="coord_y", type="integer"),
     *                 @OA\Property(property="explored", type="boolean"),
     *                 @OA\Property(property="tile_type_id", type="string")
     *             )),
     *             @OA\Property(property="error", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=403, description="No perteneces a esta partida", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=404, description="Partida no encontrada", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function show(Request $request, string $gameId)
    {
        $game = Game::findOrFail($gameId);

        $this->authorize('view', $game);

        try {
            $tiles = $this->boardService->getBoardForUser($gameId, $request->user()->id);

            return $this->rb->success($tiles);
        } catch (Exception $e) {
            $status = $e->getCode() === 403 ? 403 : 500;

            return $this->rb->error($e->getMessage(), $status);
        }
    }
}
