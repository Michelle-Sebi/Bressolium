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
