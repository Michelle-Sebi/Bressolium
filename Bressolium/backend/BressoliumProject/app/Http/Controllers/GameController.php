<?php
/**
 * @module GameController
 * @description Controlador para gestionar la creación y unión a equipos.
 * Delega la lógica de negocio en GameService.
 */

namespace App\Http\Controllers;

use App\Services\GameService;
use Illuminate\Http\Request;
use Exception;

class GameController extends Controller
{
    protected $gameService;

    /**
     * @param GameService $gameService
     */
    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Crea una nueva partida/equipo.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $request->validate([
            'team_name' => 'required|string|max:255',
        ]);

        try {
            $game = $this->gameService->createGame($request->team_name, $request->user()->id);
            return response()->json([
                'success' => true,
                'data' => $game
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Une al usuario a un equipo existente por su nombre.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function join(Request $request)
    {
        $request->validate([
            'team_name' => 'required|string'
        ]);

        try {
            $game = $this->gameService->joinGame($request->team_name, $request->user()->id);
            return response()->json([
                'success' => true,
                'data' => $game
            ], 200);
        } catch (Exception $e) {
            $status = ($e->getMessage() === 'Game not found') ? 404 : (($e->getMessage() === 'Game is full') ? 400 : 500);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $status);
        }
    }

    /**
     * Une al usuario a una partida aleatoria disponible.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function joinRandom(Request $request)
    {
        try {
            $game = $this->gameService->joinRandomGame($request->user()->id);
            return response()->json([
                'success' => true,
                'data' => $game
            ], 200);
        } catch (Exception $e) {
            $status = ($e->getMessage() === 'No games available') ? 404 : 500;
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $status);
        }
    }
}
