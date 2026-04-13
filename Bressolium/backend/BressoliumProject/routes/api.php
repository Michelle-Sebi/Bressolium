<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/game/create', [\App\Http\Controllers\GameController::class, 'create']);
    Route::post('/game/join', [\App\Http\Controllers\GameController::class, 'join']);
    Route::post('/game/join-random', [\App\Http\Controllers\GameController::class, 'joinRandom']);
    Route::get('/game/my', [\App\Http\Controllers\GameController::class, 'myGames']);
    Route::get('/game/all', [\App\Http\Controllers\GameController::class, 'allGames']);
});
