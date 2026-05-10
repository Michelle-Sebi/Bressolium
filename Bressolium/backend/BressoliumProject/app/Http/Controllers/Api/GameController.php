<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateGameDTO;
use App\DTOs\JoinGameDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\JoinGameRequest;
use App\Http\Resources\GameResource;
use App\Services\GameService;
use App\Support\ResponseBuilder;
use Exception;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(
        protected GameService $gameService,
        protected ResponseBuilder $rb,
    ) {}

    /**
     * @OA\Post(
     *     path="/game/create",
     *     summary="Crea una nueva partida y se une el usuario actual",
     *     description="El usuario que crea la partida queda automáticamente unido a ella. Genera el tablero, asigna casilla inicial e inicializa materiales.",
     *     tags={"Game"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"team_name"},
     *             @OA\Property(property="team_name", type="string", example="Los Vikingos")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Partida creada", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=422, description="Datos inválidos", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function create(CreateGameRequest $request)
    {
        try {
            $dto = new CreateGameDTO(teamName: $request->team_name, userId: $request->user()->id);
            $game = $this->gameService->createGame($dto);

            return $this->rb->success((new GameResource($game))->toArray($request));
        } catch (Exception $e) {
            return $this->rb->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/game/join",
     *     summary="Une al usuario a una partida existente por nombre de equipo",
     *     tags={"Game"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"team_name"},
     *             @OA\Property(property="team_name", type="string", example="Los Vikingos")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Unión correcta", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
     *     @OA\Response(response=400, description="La partida ya tiene 5 jugadores", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=404, description="Partida no encontrada", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function join(JoinGameRequest $request)
    {
        try {
            $dto = new JoinGameDTO(teamName: $request->team_name, userId: $request->user()->id);
            $game = $this->gameService->joinGame($dto);

            return $this->rb->success((new GameResource($game))->toArray($request));
        } catch (Exception $e) {
            $status = match ($e->getMessage()) {
                'Game not found' => 404,
                'Game is full' => 400,
                default => 500,
            };

            return $this->rb->error($e->getMessage(), $status);
        }
    }

    /**
     * @OA\Get(
     *     path="/game/my",
     *     summary="Lista las partidas del usuario autenticado",
     *     tags={"Game"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Listado de partidas", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function myGames(Request $request)
    {
        try {
            $games = $this->gameService->getMyGames($request->user()->id);

            return $this->rb->success(GameResource::collection($games)->toArray($request));
        } catch (Exception $e) {
            return $this->rb->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/game/all",
     *     summary="Lista todas las partidas disponibles para unirse",
     *     description="Devuelve partidas en estado WAITING con menos de 5 jugadores.",
     *     tags={"Game"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Listado de partidas disponibles", @OA\JsonContent(ref="#/components/schemas/ApiResponse"))
     * )
     */
    public function allGames()
    {
        try {
            $games = $this->gameService->getAllGames();

            return $this->rb->success(GameResource::collection($games)->toArray(request()));
        } catch (Exception $e) {
            return $this->rb->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/game/join-random",
     *     summary="Une al usuario a una partida disponible aleatoria",
     *     tags={"Game"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Unión correcta", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
     *     @OA\Response(response=404, description="No hay partidas disponibles", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function joinRandom(Request $request)
    {
        try {
            $game = $this->gameService->joinRandomGame($request->user()->id);

            return $this->rb->success((new GameResource($game))->toArray($request));
        } catch (Exception $e) {
            $status = ($e->getMessage() === 'No games available') ? 404 : 500;

            return $this->rb->error($e->getMessage(), $status);
        }
    }
}
