<?php

namespace App\Http\Controllers;

use App\DTOs\ExploreActionDTO;
use App\DTOs\UpgradeActionDTO;
use App\Http\Resources\TileResource;
use App\Services\ActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TileController extends Controller
{
    public function __construct(private ActionService $actionService) {}

    public function explore(Request $request, string $id): JsonResponse
    {
        $dto = new ExploreActionDTO(tileId: $id, userId: $request->user()->id);
        $result = $this->actionService->explore($dto);
        return $this->respond($request, $result);
    }

    public function upgrade(Request $request, string $id): JsonResponse
    {
        $dto = new UpgradeActionDTO(tileId: $id, userId: $request->user()->id);
        $result = $this->actionService->upgrade($dto);
        return $this->respond($request, $result);
    }

    private function respond(Request $request, array $result): JsonResponse
    {
        $status = $result['status'];
        $error  = $result['error'] ?? null;
        $data   = $result['data'] ?? null;

        if ($status === 200 && $data !== null) {
            $data = (new TileResource($data))->toArray($request);
        }

        return response()->json([
            'success' => $status === 200,
            'data'    => $data,
            'error'   => $error,
        ], $status);
    }
}
