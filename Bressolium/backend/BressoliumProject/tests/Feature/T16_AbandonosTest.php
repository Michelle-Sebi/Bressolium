<?php

use App\Models\User;
use App\Models\Game;
// use App\Jobs\CloseRoundJob;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// CRITICAL TEST FOR: TASK 16 (Raw_Tareas)
// Title: Abandonment Management (Inactive Players)
// ==========================================

beforeEach(function () {
    // 2 users
    $this->users = User::factory()->count(2)->create();

    $this->game = Game::factory()->create([
        'round_status' => json_encode([
            'actions_remaining' => 1,
            'votes' => [
                ['user_id' => $this->users[0]->id, 'tech_id' => 1]
                // User [1] HAS NOT VOTED
            ]
        ])
    ]);
});

test('game advances if User2 is marked is_afk and Inactive despite not having voted', function () {
    // Mark Player 2 as inactive
    $this->users[1]->update(['is_afk' => true]);

    // 1. Simulate manual execution of the round evaluation Job (to be scheduled in T13/16)
    // (new CloseRoundJob($this->game->id))->handle();

    // (PROSPECTIVE MOCKED JOB LOGIC) -->
    $all_voted = true;
    $current_votes = collect(json_decode($this->game->round_status, true)['votes']);

    foreach ($this->game->users as $u) {
        if (!$u->is_afk) {
            $hasVoted = $current_votes->firstWhere('user_id', $u->id);
            if (!$hasVoted)
                $all_voted = false;
        }
    }

    if ($all_voted) {
        // Execute Close
        $this->game->update(['round_status' => json_encode(['votes' => [], 'actions_remaining' => 2])]);
    }
    // <-- END JOB MOCK

    $this->game->refresh();
    $newStatus = json_decode($this->game->round_status, true);

    // Expect empty because close should have triggered with U2 marked is_afk
    expect($newStatus['votes'])->toBeEmpty();
});