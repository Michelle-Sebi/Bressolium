<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TileResource;
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

    public function show(Request $request, string $gameId)
    {
        $game = Game::findOrFail($gameId);

        $this->authorize('view', $game);

        try {
            $tiles = $this->boardService->getBoardForUser($gameId, $request->user()->id);

            return $this->rb->success(TileResource::collection($tiles)->toArray($request));
        } catch (Exception $e) {
            $status = $e->getCode() === 403 ? 403 : 500;

            return $this->rb->error($e->getMessage(), $status);
        }
    }
}
