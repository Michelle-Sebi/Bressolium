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

    // Game with clean JSON
    $this->game = Game::factory()->create([
        'round_status' => json_encode([
            'actions_remaining' => 2,
            'votes' => []
        ])
    ]);
    $this->user->games()->attach($this->game->id);
    $this->actingAs($this->user);
});

test('stores the vote by injecting it recursively into JSON avoiding overrides', function () {
    $tech_id = 99; // Test technology ID

    $response = $this->postJson("/api/game/{$this->game->id}/vote", [
        'tech_id' => $tech_id
    ]);

    $response->assertStatus(200);

    $this->game->refresh();
    $new_status = json_decode($this->game->round_status, true);

    expect(count($new_status['votes']))->toBe(1)
        ->and($new_status['votes'][0]['user_id'])->toBe($this->user->id)
        ->and($new_status['votes'][0]['tech_id'])->toBe($tech_id);
});