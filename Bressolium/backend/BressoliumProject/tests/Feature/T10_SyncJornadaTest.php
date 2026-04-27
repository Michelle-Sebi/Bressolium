<?php

use App\Models\User;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 10 (Raw_Tareas)
// Title: Relational Sync Endpoint for Rounds
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->game = Game::factory()->create();
    $this->round = $this->game->rounds()->create(['number' => 1]);

    $this->user->games()->attach($this->game->id);
    $this->round->users()->attach($this->user->id, ['actions_spent' => 1]);
    $this->actingAs($this->user);
});

test('endpoint GET /api/game/sync devuelve el estado relacional completo del equipo', function () {
    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync");

    $response->assertStatus(200)
        ->assertJsonStructure([
        'success',
        'data' => [
            'current_round' => ['number', 'start_date'],
            'user_actions' => ['actions_spent'],
            'inventory' => [
                '*' => ['name', 'quantity']
            ],
            'progress' => [
                'technologies' => ['*'],
                'inventions' => ['*']
            ]
        ]
    ]);

    expect($response->json('data.user_actions.actions_spent'))->toBe(1);
});