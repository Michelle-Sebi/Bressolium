<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateGameDTO;
use App\DTOs\JoinGameDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\JoinGameRequest;
use App\Http\Resources\GameResource;
use App\Services\GameService;
use Exception;

class GameController extends Controller
{
    public function __construct(protected GameService $gameService) {}

    public function create(CreateGameRequest $request)
    {
        try {
            $dto  = new CreateGameDTO(teamName: $request->team_name, userId: $request->user()->id);
            $game = $this->gameService->createGame($dto);
            return response()->json(['success' => true, 'data' => (new GameResource($game))->toArray($request)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function join(JoinGameRequest $request)
    {
        try {
            $dto  = new JoinGameDTO(teamName: $request->team_name, userId: $request->user()->id);
            $game = $this->gameService->joinGame($dto);
            return response()->json(['success' => true, 'data' => (new GameResource($game))->toArray($request)], 200);
        } catch (Exception $e) {
            $status = match ($e->getMessage()) {
                'Game not found' => 404,
                'Game is full'   => 400,
                default          => 500,
            };
            return response()->json(['success' => false, 'error' => $e->getMessage()], $status);
        }
    }

    public function myGames(\Illuminate\Http\Request $request)
    {
        try {
            $games = $this->gameService->getMyGames($request->user()->id);
            return response()->json(['success' => true, 'data' => GameResource::collection($games)->toArray($request)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function allGames()
    {
        try {
            $games = $this->gameService->getAllGames();
            return response()->json(['success' => true, 'data' => GameResource::collection($games)->toArray(request())], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function joinRandom(\Illuminate\Http\Request $request)
    {
        try {
            $game = $this->gameService->joinRandomGame($request->user()->id);
            return response()->json(['success' => true, 'data' => (new GameResource($game))->toArray($request)], 200);
        } catch (Exception $e) {
            $status = ($e->getMessage() === 'No games available') ? 404 : 500;
            return response()->json(['success' => false, 'error' => $e->getMessage()], $status);
        }
    }
}
