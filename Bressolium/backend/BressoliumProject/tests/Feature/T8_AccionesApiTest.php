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

test('un usuario no puede explorar si ya gasto 2 acciones', function () {
    $this->round->users()->updateExistingPivot($this->user->id, ['actions_spent' => 2]);

    $response = $this->postJson("/api/tiles/{$this->tile->id}/explore");

    $response->assertStatus(403);
});

test('evolucionar una casilla cambia su tile_type_id y resta materiales del equipo', function () {
    // Setup: 10 Wood en el inventario del equipo (game_material)
    $wood = \App\Models\Material::factory()->create(['name' => 'Wood']);
    $this->game->materials()->attach($wood->id, ['quantity' => 10, 'is_active' => true]);

    // Setup: Siguiente nivel del tipo de casilla
    $nextType = \App\Models\TileType::factory()->create(['name' => 'Forest', 'level' => 2]);

    $response = $this->postJson("/api/tiles/{$this->tile->id}/upgrade");

    $response->assertStatus(200);

    // Verificaciones
    $this->tile->refresh();
    expect($this->tile->tile_type_id)->toBe($nextType->id);

    $inventoryWood = $this->game->materials()->where('name', 'Wood')->first()->pivot->quantity;
    expect($inventoryWood)->toBeLessThan(10); // Asumiendo que costo > 0
});

test('falla la evolucion (HTTP 400) si el equipo no tiene materiales suficientes en game_material', function () {
    $wood = \App\Models\Material::factory()->create(['name' => 'Wood']);
    $this->game->materials()->attach($wood->id, ['quantity' => 0, 'is_active' => true]);

    $response = $this->postJson("/api/tiles/{$this->tile->id}/upgrade");

    $response->assertStatus(400)
        ->assertJsonFragment(['error' => 'Insufficient materials']);
});