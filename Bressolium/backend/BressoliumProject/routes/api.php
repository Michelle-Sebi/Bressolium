<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\TileController;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/game/create',      [GameController::class, 'create']);
        Route::post('/game/join',        [GameController::class, 'join']);
        Route::post('/game/join-random', [GameController::class, 'joinRandom']);
        Route::get('/game/my',           [GameController::class, 'myGames']);
        Route::get('/game/all',          [GameController::class, 'allGames']);

        Route::get('/board/{gameId}', [BoardController::class, 'show']);

        Route::get('/game/{gameId}/sync', [SyncController::class, 'sync']);

        Route::post('/tiles/{id}/explore', [TileController::class, 'explore']);
        Route::post('/tiles/{id}/upgrade', [TileController::class, 'upgrade']);
    });
});
