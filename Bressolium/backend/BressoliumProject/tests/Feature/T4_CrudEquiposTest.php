<?php

use App\Models\User;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 4 (Raw_Tareas)
// Title: CRUD Endpoints for Teams (Games)
// Requirements: HU 1.2 (Creation), HU 1.3 (Join) and HU 1.4 (Random Assignment)
// ==========================================

beforeEach(function () {
    // Authenticate a user for all these tests
    $this->user = User::factory()->create();
    $this->actingAs($this->user); // Simulates Sanctum token
});

test('creating a game initializes the first round and responds with JSON standard', function () {
    $response = $this->postJson('/api/game/create', [
        'team_name' => 'Digital Pioneers'
    ]);

    $response->assertStatus(200)
        ->assertJson([
        'success' => true,
    ]);

    // BD Assertion: Check that game is stored in the DB
    expect(Game::count())->toBe(1);
    $game = Game::first();
    expect($game->users->contains($this->user))->toBeTrue();
        
    // Check Round 1 was created
    $round = \App\Models\Round::where('game_id', $game->id)->first();
    expect($round)->not->toBeNull()
        ->and($round->number)->toBe(1);
        
    // Check round_user table was populated for initial members
    $roundUserCount = \Illuminate\Support\Facades\DB::table('round_user')
        ->where('round_id', $round->id)
        ->where('user_id', $this->user->id)
        ->count();
    expect($roundUserCount)->toBe(1);
});

test('joining by exact name associates the player with the team (HU 1.3)', function () {
    $game = Game::factory()->create(['name' => 'The Testers']);

    $response = $this->postJson('/api/game/join', [
        'team_name' => 'The Testers'
    ]);

    $response->assertStatus(200);
    expect($this->user->games->pluck('id'))->toContain($game->id);
});

test('random assignment finds teams with fewer than 5 members (HU 1.4)', function () {
    // Full Game (Simulated)
    $fullGame = Game::factory()->hasUsers(5)->create();
    // Game With Slot (Simulated)
    $freeGame = Game::factory()->hasUsers(2)->create();

    $response = $this->postJson('/api/game/join-random');

    $response->assertStatus(200);
    // User should land in the game with a slot, not the full one
    expect($freeGame->fresh()->users->contains($this->user))->toBeTrue()
        ->and($fullGame->fresh()->users->contains($this->user))->toBeFalse();
});