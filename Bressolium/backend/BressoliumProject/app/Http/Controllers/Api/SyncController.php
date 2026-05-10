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
