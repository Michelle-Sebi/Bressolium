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

test('creating a game initializes empty round_status json and responds with JSON standard', function () {
    $response = $this->postJson('/api/game/create', [
        'team_name' => 'Digital Pioneers',
        'base_culture' => 'Cyberpunk'
    ]);

    $response->assertStatus(200)
        ->assertJson([
        'success' => true,
    ]);

    // BD Assertion: Check that JSON was stored in the DB
    expect(Game::count())->toBe(1);
    $game = Game::first();
    expect($game->base_culture)->toBe('Cyberpunk')
        ->and($game->round_status)->toBeJson()
        ->and($game->users->contains($this->user))->toBeTrue();
});

test('joining by exact name associates the player with the team (HU 1.3)', function () {
    $game = Game::factory()->create(['team_name' => 'The Testers']);

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