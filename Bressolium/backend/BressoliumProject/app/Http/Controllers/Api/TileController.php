<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ExploreActionDTO;
use App\DTOs\UpgradeActionDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExploreActionRequest;
use App\Http\Requests\UpgradeActionRequest;
use App\Http\Resources\TileResource;
use App\Services\ActionService;
use Illuminate\Http\JsonResponse;

class TileController extends Controller
{
    public function __construct(private ActionService $actionService) {}

    public function explore(ExploreActionRequest $request, string $id): JsonResponse
    {
        $dto  = new ExploreActionDTO(tileId: $id, userId: $request->user()->id);
        $tile = $this->actionService->explore($dto);

        return response()->json([
            'success' => true,
            'data'    => (new TileResource($tile))->toArray($request),
            'error'   => null,
        ], 200);
    }

    public function upgrade(UpgradeActionRequest $request, string $id): JsonResponse
    {
        $dto  = new UpgradeActionDTO(tileId: $id, userId: $request->user()->id);
        $tile = $this->actionService->upgrade($dto);

        return response()->json([
            'success' => true,
            'data'    => (new TileResource($tile))->toArray($request),
            'error'   => null,
        ], 200);
    }
}
