<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ExploreActionDTO;
use App\DTOs\UpgradeActionDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExploreActionRequest;
use App\Http\Requests\UpgradeActionRequest;
use App\Http\Resources\TileResource;
use App\Services\ActionService;
use App\Support\ResponseBuilder;
use Illuminate\Http\JsonResponse;

class TileController extends Controller
{
    public function __construct(
        private ActionService $actionService,
        private ResponseBuilder $rb,
    ) {}

    /**
     * @OA\Post(
     *     path="/tiles/{id}/explore",
     *     summary="Explora una casilla",
     *     description="Marca una casilla como explorada por el usuario. Consume una acción de la jornada actual. La casilla debe ser adyacente a otra ya explorada por el jugador.",
     *     tags={"Tile"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID de la casilla a explorar",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Casilla explorada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="coord_x", type="integer", example=5),
     *                 @OA\Property(property="coord_y", type="integer", example=7),
     *                 @OA\Property(property="explored", type="boolean", example=true),
     *                 @OA\Property(property="type", type="object")
     *             ),
     *             @OA\Property(property="error", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=403, description="El usuario no pertenece a esta partida", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=422, description="Casilla ya explorada / no adyacente / sin acciones / casilla pueblo", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function explore(ExploreActionRequest $request, string $id): JsonResponse
    {
        $dto = new ExploreActionDTO(tileId: $id, userId: $request->user()->id);
        $tile = $this->actionService->explore($dto);

        return $this->rb->success((new TileResource($tile))->toArray($request));
    }

    /**
     * @OA\Post(
     *     path="/tiles/{id}/upgrade",
     *     summary="Mejora una casilla al siguiente nivel",
     *     description="Sube de nivel el tipo de la casilla. Requiere que la casilla esté explorada, que existan los materiales necesarios en la partida y, en algunos casos, que la tecnología requerida esté activa. Consume una acción.",
     *     tags={"Tile"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="UUID de la casilla a mejorar",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Casilla mejorada correctamente",
     *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
     *     ),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=403, description="El usuario no pertenece a esta partida", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=422, description="Materiales insuficientes / tecnología no disponible / sin acciones / casilla no explorada / sin niveles superiores", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function upgrade(UpgradeActionRequest $request, string $id): JsonResponse
    {
        $dto = new UpgradeActionDTO(tileId: $id, userId: $request->user()->id);
        $tile = $this->actionService->upgrade($dto);

        return $this->rb->success((new TileResource($tile))->toArray($request));
    }
}
