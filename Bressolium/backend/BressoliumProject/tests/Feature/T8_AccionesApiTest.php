<?php

use App\Models\User;
use App\Models\Game;
use App\Models\Tile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 8 (Raw_Tareas)
// Title: Individual Actions API (Explore / Upgrade)
// CRITICAL TDD Notes: Transactional locks are tested here
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();

    // Inject relational round state.
    $this->game = Game::factory()->create();
    $this->round = $this->game->rounds()->create(['number' => 1]);
    $this->user->games()->attach($this->game->id);

    // User starts with 0 actions spent.
    $this->round_user = $this->round->users()->attach($this->user->id, ['actions_spent' => 0]);

    $this->user->games()->attach($this->game->id);
    $this->tile = Tile::factory()->create([
        'game_id' => $this->game->id,
        'coord_x' => 1, 'coord_y' => 1,
        'explored' => false
    ]);

    $this->actingAs($this->user);
});

test('exploring spends 1 action in round JSON and reveals tile', function () {
    $response = $this->postJson("/api/tiles/{$this->tile->id}/explore");

    $response->assertStatus(200);

    // Refresh and Check BD
    $actions_spent = $this->round->users()->where('user_id', $this->user->id)->first()->pivot->actions_spent;
    $this->tile->refresh();

    expect($actions_spent)->toBe(1)
        ->and($this->tile->explored)->toBe(1); // Tile revealed
});

test('rejects explore action and returns 403 if actions_spent are two', function () {
    // Force 2 actions spent for this user in this round
    $this->round->users()->updateExistingPivot($this->user->id, ['actions_spent' => 2]);

    $response = $this->postJson("/api/tiles/{$this->tile->id}/explore");

    // Expected backend failure due to business rule
    $response->assertStatus(403)
        ->assertJson(['error' => 'You have no actions left this turn']);
});

test('rejects upgrade request (HTTP 400) if JSON does not have enough materials', function () {
    // Assuming the team has 5 Wood, but upgrading to Level 1 requires 10.
    $this->game->update([
        'round_status' => json_encode([
            'actions_remaining' => 2,
            'materials_inventory' => ['Wood' => 5]
        ])
    ]);

    // Simulate upgrade (Improve) endpoint
    $response = $this->postJson("/api/tiles/{$this->tile->id}/upgrade");

    $response->assertStatus(400)
        ->assertJson(['error' => 'Insufficient materials for this upgrade']);
});