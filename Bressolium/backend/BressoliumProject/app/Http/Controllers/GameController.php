<?php

/**
 * @module GameController
 *
 * @description Controlador para gestionar la creación y unión a equipos.
 * Delega la lógica de negocio en GameService.
 */

namespace App\Http\Controllers;

use App\DTOs\CreateGameDTO;
use App\DTOs\JoinGameDTO;
use App\Http\Resources\GameResource;
use App\Services\GameService;
use Exception;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function create(Request $request)
    {
        $request->validate([
            'team_name' => 'required|string|max:255',
        ]);

        try {
            $dto = new CreateGameDTO(
                teamName: $request->team_name,
                userId: $request->user()->id,
            );
            $game = $this->gameService->createGame($dto);

            return response()->json([
                'success' => true,
                'data' => (new GameResource($game))->toArray($request),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function join(Request $request)
    {
        $request->validate([
            'team_name' => 'required|string',
        ]);

        try {
            $dto = new JoinGameDTO(
                teamName: $request->team_name,
                userId: $request->user()->id,
            );
            $game = $this->gameService->joinGame($dto);

            return response()->json([
                'success' => true,
                'data' => (new GameResource($game))->toArray($request),
            ], 200);
        } catch (Exception $e) {
            $status = ($e->getMessage() === 'Game not found') ? 404 : (($e->getMessage() === 'Game is full') ? 400 : 500);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $status);
        }
    }

    public function myGames(Request $request)
    {
        try {
            $games = $this->gameService->getMyGames($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => GameResource::collection($games)->toArray($request),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function allGames()
    {
        try {
            $games = $this->gameService->getAllGames();

            return response()->json([
                'success' => true,
                'data' => GameResource::collection($games)->toArray(request()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function joinRandom(Request $request)
    {
        try {
            $game = $this->gameService->joinRandomGame($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => (new GameResource($game))->toArray($request),
            ], 200);
        } catch (Exception $e) {
            $status = ($e->getMessage() === 'No games available') ? 404 : 500;

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $status);
        }
    }
}
