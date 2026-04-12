<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Round;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'team_name' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $game = Game::create([
                'name' => $request->team_name,
                'status' => 'WAITING'
            ]);

            // Link user to game
            $game->users()->attach($request->user()->id, ['is_afk' => false]);

            // Create Round 1
            $round = Round::create([
                'game_id' => $game->id,
                'number' => 1,
                'start_date' => now(),
            ]);

            // Link user to round
            $round->users()->attach($request->user()->id, ['actions_spent' => 0]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $game
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function join(Request $request)
    {
        $request->validate([
            'team_name' => 'required|string'
        ]);

        $game = Game::where('name', $request->team_name)->first();

        if (!$game) {
            return response()->json([
                'success' => false,
                'error' => 'Game not found'
            ], 404);
        }

        if ($game->users()->count() >= 5) {
            return response()->json([
                 'success' => false,
                 'error' => 'Game is full'
            ], 400);
        }

        try {
            DB::beginTransaction();

            if (!$game->users()->where('user_id', $request->user()->id)->exists()) {
                $game->users()->attach($request->user()->id, ['is_afk' => false]);
                
                // Add to the active round if exists
                $latestRound = $game->rounds()->orderBy('number', 'desc')->first();
                if ($latestRound) {
                    $latestRound->users()->attach($request->user()->id, ['actions_spent' => 0]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $game
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function joinRandom(Request $request)
    {
        // Find a game with fewer than 5 members
        $game = Game::withCount('users')
            ->having('users_count', '<', 5)
            ->first();

        if (!$game) {
            return response()->json([
                'success' => false,
                'error' => 'No games available'
            ], 404);
        }

        try {
            DB::beginTransaction();

            if (!$game->users()->where('user_id', $request->user()->id)->exists()) {
                $game->users()->attach($request->user()->id, ['is_afk' => false]);
                
                $latestRound = $game->rounds()->orderBy('number', 'desc')->first();
                if ($latestRound) {
                    $latestRound->users()->attach($request->user()->id, ['actions_spent' => 0]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $game
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
