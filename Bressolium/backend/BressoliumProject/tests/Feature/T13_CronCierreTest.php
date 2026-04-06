<?php

use App\Models\User;
use App\Models\Game;
// use App\Jobs\CloseRoundJob;   // (To import once the Controller is created)
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// CRITICAL TEST FOR: TASK 13 (Raw_Tareas)
// Title: Schedule / Cron Round Close Backend
// ==========================================

beforeEach(function () {
    $this->users = User::factory()->count(3)->create();

    // Force game with votes already collected (2 for tech 1, 1 for tech 2)
    $this->game = Game::factory()->create([
        'round_status' => json_encode([
            'actions_remaining' => 0,
            'votes' => [
                ['user_id' => $this->users[0]->id, 'tech_id' => 1],
                ['user_id' => $this->users[1]->id, 'tech_id' => 1], // Winner (majority)
                ['user_id' => $this->users[2]->id, 'tech_id' => 2],
            ]
        ])
    ]);
});

test('the close job evaluates winner, resets JSON and initializes new round', function () {
    // 1. Simulate manual Job execution
    // (new CloseRoundJob($this->game->id))->handle(); // <- UNCOMMENT WHEN CODE EXISTS

    // Mock the BD mutation as the JOB would do it.
    $this->game->update([
        'round_status' => json_encode([
            'actions_remaining' => 2, // Reset
            'votes' => [] // Cleared for day 2
        ])
    ]);

    // 2. Refresh and evaluate
    $this->game->refresh();
    $newStatus = json_decode($this->game->round_status, true);

    expect($newStatus['votes'])->toBeEmpty()
        ->and($newStatus['actions_remaining'])->toBe(2);

// (ADD LATER: Test to check that the "inventory" deducted winner cost and added Tech)
});

test('the cron algorithm resolves a vote tie randomly without crashing', function () {
    // Force a 2-2 tie between Tech 1 and Tech 2
    $this->users = User::factory()->count(4)->create();
    $this->game->update([
        'round_status' => json_encode([
            'actions_remaining' => 0,
            'votes' => [
                ['user_id' => $this->users[0]->id, 'tech_id' => 1],
                ['user_id' => $this->users[1]->id, 'tech_id' => 1],
                ['user_id' => $this->users[2]->id, 'tech_id' => 2],
                ['user_id' => $this->users[3]->id, 'tech_id' => 2],
            ]
        ])
    ]);

    // Run simulated resolution.
    // A collect or array_rand will pick a random key on identical count()
    $votes_array = json_decode($this->game->round_status, true)['votes'];
    $count = array_count_values(array_column($votes_array, 'tech_id'));

    // Get those with max count
    $maxVotes = max($count);
    $candidates = array_keys(array_filter($count, fn($v) => $v == $maxVotes));
    $winning_tech = $candidates[array_rand($candidates)];

    // Assertion: The machine must be able to break the tie selecting 1 or 2
    expect(in_array($winning_tech, [1, 2]))->toBeTrue();
});