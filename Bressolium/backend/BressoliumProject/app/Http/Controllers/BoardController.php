<?php

namespace App\Http\Controllers;

use App\Services\BoardService;
use Exception;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function __construct(private BoardService $boardService) {}

    public function show(Request $request, string $gameId)
    {
        try {
            $tiles = $this->boardService->getBoardForUser($gameId, $request->user()->id);

            return response()->json([
                'success' => true,
                'data'    => $tiles,
                'error'   => null,
            ]);
        } catch (Exception $e) {
            $status = $e->getCode() === 403 ? 403 : 500;

            return response()->json([
                'success' => false,
                'data'    => null,
                'error'   => $e->getMessage(),
            ], $status);
        }
    }
}
