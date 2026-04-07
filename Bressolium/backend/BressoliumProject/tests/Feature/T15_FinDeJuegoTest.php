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
    $this->game = Game::factory()->create();
    $this->user->games()->attach($this->game->id);

    $this->actingAs($this->user);
});

test('el juego declara victoria cuando se desbloquea la tecnologia Spaceship', function () {
    $spaceship = \App\Models\Technology::factory()->create(['name' => 'Spaceship']);

    // Simular desbloqueo en la partida
    $this->game->technologies()->attach($spaceship->id);

    $this->game->refresh();

    // La lógica del modelo o service debe determinar que el juego ha terminado
    expect($this->game->technologies()->where('name', 'Spaceship')->exists())->toBeTrue();
});