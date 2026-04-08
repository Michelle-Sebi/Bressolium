<?php

use App\Models\User;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 11 (Raw_Tareas)
// Title: Progress Voting API
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->game = Game::factory()->create();
    $this->round = $this->game->rounds()->create(['number' => 1]);

    $this->user->games()->attach($this->game->id);
    $this->actingAs($this->user);
});

test('almacena el voto en la tabla votes vinculandolo a la jornada actual', function () {
    $tech = \App\Models\Technology::factory()->create();

    $response = $this->postJson("/api/game/{$this->game->id}/vote", [
        'technology_id' => $tech->id
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('votes', [
        'round_id' => $this->round->id,
        'user_id' => $this->user->id,
        'technology_id' => $tech->id
    ]);
});

test('permite votar por un invento en lugar de una tecnologia', function () {
    $inv = \App\Models\Invention::factory()->create();

    $response = $this->postJson("/api/game/{$this->game->id}/vote", [
        'invention_id' => $inv->id
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('votes', [
        'user_id' => $this->user->id,
        'invention_id' => $inv->id
    ]);
});