<?php

namespace App\Http\Controllers;

use App\Services\ActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TileController extends Controller
{
    public function __construct(private ActionService $actionService) {}

    public function explore(Request $request, string $id): JsonResponse
    {
        $result = $this->actionService->explore($id, $request->user()->id);
        return $this->respond($result);
    }

    public function upgrade(Request $request, string $id): JsonResponse
    {
        $result = $this->actionService->upgrade($id, $request->user()->id);
        return $this->respond($result);
    }

    private function respond(array $result): JsonResponse
    {
        $status = $result['status'];
        $data   = $result['data'] ?? null;
        $error  = $result['error'] ?? null;

        return response()->json([
            'success' => $status === 200,
            'data'    => $data,
            'error'   => $error,
        ], $status);
    }
}
