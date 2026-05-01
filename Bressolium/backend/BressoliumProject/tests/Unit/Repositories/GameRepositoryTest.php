<?php

// ==========================================
// TEST FOR: TASK 29 — Tests Unitarios de Backend
// Repository: Eloquent\GameRepository
// ==========================================

use App\Models\Game;
use App\Models\User;
use App\Repositories\Eloquent\GameRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('GameRepository::create persiste una partida en BD', function () {
    $repo = new GameRepository();
    $game = $repo->create(['name' => 'Los Vikingos', 'status' => 'WAITING']);

    expect($game)->toBeInstanceOf(Game::class)
        ->and($game->name)->toBe('Los Vikingos')
        ->and($game->status)->toBe('WAITING')
        ->and(Game::count())->toBe(1);
});

test('GameRepository::findByName devuelve la partida con ese nombre exacto', function () {
    Game::factory()->create(['name' => 'Equipo A']);
    Game::factory()->create(['name' => 'Equipo B']);

    $repo   = new GameRepository();
    $result = $repo->findByName('Equipo A');

    expect($result)->toBeInstanceOf(Game::class)
        ->and($result->name)->toBe('Equipo A');
});

test('GameRepository::findByName devuelve null si no existe', function () {
    $repo = new GameRepository();
    expect($repo->findByName('NoExiste'))->toBeNull();
});

test('GameRepository::findAvailableRandom devuelve una partida con menos de 5 usuarios', function () {
    $free = Game::factory()->hasUsers(2)->create();
    Game::factory()->hasUsers(5)->create(); // llena

    $repo   = new GameRepository();
    $result = $repo->findAvailableRandom();

    expect($result)->toBeInstanceOf(Game::class)
        ->and($result->id)->toBe($free->id);
});

test('GameRepository::findAvailableRandom devuelve null si todas están llenas', function () {
    Game::factory()->hasUsers(5)->create();

    $repo = new GameRepository();
    expect($repo->findAvailableRandom())->toBeNull();
});

test('GameRepository::getAllAvailableGames devuelve solo partidas con menos de 5 usuarios', function () {
    Game::factory()->hasUsers(2)->create();
    Game::factory()->hasUsers(3)->create();
    Game::factory()->hasUsers(5)->create(); // llena, no debe aparecer

    $repo    = new GameRepository();
    $results = $repo->getAllAvailableGames();

    expect($results)->toHaveCount(2);
});

test('GameRepository::getGamesByUserId devuelve solo las partidas del usuario dado', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $game1 = Game::factory()->create();
    $game2 = Game::factory()->create();
    $game3 = Game::factory()->create();

    $game1->users()->attach($user->id);
    $game2->users()->attach($user->id);
    $game3->users()->attach($other->id);

    $repo    = new GameRepository();
    $results = $repo->getGamesByUserId($user->id);

    expect($results)->toHaveCount(2)
        ->and($results->pluck('id'))->toContain($game1->id)
        ->and($results->pluck('id'))->toContain($game2->id)
        ->and($results->pluck('id'))->not->toContain($game3->id);
});
