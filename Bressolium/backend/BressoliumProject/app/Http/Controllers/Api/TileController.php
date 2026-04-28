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

    public function explore(ExploreActionRequest $request, string $id): JsonResponse
    {
        $dto  = new ExploreActionDTO(tileId: $id, userId: $request->user()->id);
        $tile = $this->actionService->explore($dto);

        return $this->rb->success((new TileResource($tile))->toArray($request));
    }

    public function upgrade(UpgradeActionRequest $request, string $id): JsonResponse
    {
        $dto  = new UpgradeActionDTO(tileId: $id, userId: $request->user()->id);
        $tile = $this->actionService->upgrade($dto);

        return $this->rb->success((new TileResource($tile))->toArray($request));
    }
}
