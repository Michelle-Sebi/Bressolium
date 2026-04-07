<?php

use App\Models\User;
use App\Models\Game;
use App\Models\Tile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 7 (Raw_Tareas)
// Title: Board Generator and API Controller
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->game = Game::factory()->create();
    $this->user->games()->attach($this->game->id);
    $this->actingAs($this->user);
});

test('endpoint /api/board returns json tile matrix associated with the active game', function () {
    // Simulate tiles created via event or seeder when the game starts
    Tile::factory()->count(10)->create(['game_id' => $this->game->id]);

    $response = $this->getJson("/api/board/{$this->game->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
        'success',
        'data' => [
            '*' => ['id', 'coord_x', 'coord_y', 'tile_type_id', 'explored']
        ],
        'error'
    ]);

    expect(count($response->json('data')))->toBe(10);
});