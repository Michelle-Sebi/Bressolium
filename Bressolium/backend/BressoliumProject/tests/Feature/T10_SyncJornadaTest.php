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
    $this->game = Game::factory()->create([
        'round_status' => json_encode([
            'actions_remaining' => 2,
            'votes' => []
        ])
    ]);

    $this->user->games()->attach($this->game->id);
    $this->actingAs($this->user);
});

test('endpoint GET /api/game/sync returns the round JSON clean', function () {
    $response = $this->getJson("/api/game/{$this->game->id}/sync");

    $response->assertStatus(200)
        ->assertJsonStructure([
        'success',
        'data' => [
            'round_status' => [
                'actions_remaining',
                'votes'
            ]
        ]
    ]);

    expect($response->json('data.round_status.actions_remaining'))->toBe(2);
});