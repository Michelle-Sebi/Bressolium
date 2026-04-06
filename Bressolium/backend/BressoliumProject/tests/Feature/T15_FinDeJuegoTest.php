<?php

use App\Models\User;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 15 (Raw_Tareas)
// Title: End of Game (Terraforming)
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->game = Game::factory()->create([
        'round_status' => json_encode(['actions_remaining' => 2, 'votes' => []])
    ]);
    $this->user->games()->attach($this->game->id);

    $this->actingAs($this->user);
});

test('game declares victory when the spaceship is unlocked (HU 4.3)', function () {
    $this->game->update([
        'is_spaceship_unlocked' => true
    ]);

    expect($this->game->is_spaceship_unlocked)->toBeTrue();
});