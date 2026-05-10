<?php

// ==========================================
// TEST FOR: TASK 29 — Tests Unitarios de Backend
// Repository: Eloquent\RoundRepository
// ==========================================

use App\Models\Game;
use App\Models\Round;
use App\Repositories\Eloquent\RoundRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('RoundRepository::create persiste una ronda en BD', function () {
    $game = Game::factory()->create();
    $repo = new RoundRepository;

    $round = $repo->create(['game_id' => $game->id, 'number' => 1, 'start_date' => now()]);

    expect($round)->toBeInstanceOf(Round::class)
        ->and($round->number)->toBe(1)
        ->and($round->game_id)->toBe($game->id)
        ->and(Round::count())->toBe(1);
});

test('RoundRepository::getLatestRoundForGame devuelve la ronda con número más alto', function () {
    $game = Game::factory()->create();
    $repo = new RoundRepository;

    $repo->create(['game_id' => $game->id, 'number' => 1, 'start_date' => now()]);
    $repo->create(['game_id' => $game->id, 'number' => 2, 'start_date' => now()]);
    $repo->create(['game_id' => $game->id, 'number' => 3, 'start_date' => now()]);

    $latest = $repo->getLatestRoundForGame($game->id);

    expect($latest)->toBeInstanceOf(Round::class)
        ->and($latest->number)->toBe(3);
});

test('RoundRepository::getLatestRoundForGame devuelve null si la partida no tiene rondas', function () {
    $game = Game::factory()->create();
    $repo = new RoundRepository;

    expect($repo->getLatestRoundForGame($game->id))->toBeNull();
});

test('RoundRepository::getLatestRoundForGame no mezcla rondas de distintas partidas', function () {
    $game1 = Game::factory()->create();
    $game2 = Game::factory()->create();
    $repo = new RoundRepository;

    $repo->create(['game_id' => $game1->id, 'number' => 5, 'start_date' => now()]);
    $repo->create(['game_id' => $game2->id, 'number' => 1, 'start_date' => now()]);

    expect($repo->getLatestRoundForGame($game2->id)->number)->toBe(1);
});
