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
