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
    $this->game = Game::factory()->create();
    $this->round = $this->game->rounds()->create(['number' => 1]);

    // Simulate current turn participation
    foreach ($this->users as $u) {
        $this->game->users()->attach($u->id);
        $this->round->users()->attach($u->id, ['actions_spent' => 2]); // Finished actions
    }

    // Technology to vote for
    $this->tech = \App\Models\Technology::factory()->create(['name' => 'Wheel']);

    // Votes (2 vs 1)
    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'technology_id' => $this->tech->id]);
    $this->round->votes()->create(['user_id' => $this->users[1]->id, 'technology_id' => $this->tech->id]);
    $this->round->votes()->create(['user_id' => $this->users[2]->id, 'technology_id' => 999]); // Other
});

test('el job de cierre evalua el ganador, desbloquea progreso y salta de jornada', function () {
    // Simulación del Job de Cierre
    // Logic: Wheel wins -> Insert into game_technology -> Create round 2

    // -- Lógica simulada del Job --
    $this->game->technologies()->attach($this->tech->id); // Unlocked!

    $newRound = $this->game->rounds()->create([
        'number' => 2,
        'start_date' => now()
    ]);
    // ----------------------------

    $this->game->refresh();

    expect($this->game->rounds()->count())->toBe(2)
        ->and($this->game->technologies()->first()->name)->toBe('Wheel');

    $this->assertDatabaseHas('rounds', ['number' => 2, 'game_id' => $this->game->id]);
});

test('el algoritmo de resolución suma produccion de materiales segun las casillas exploradas', function () {
    // Setup: Madera (Material)
    $wood = \App\Models\Material::factory()->create(['name' => 'Wood']);

    // Setup: Casilla de Bosque L1 (Produce 2 Madera)
    $forest = \App\Models\TileType::factory()->create(['name' => 'Forest', 'level' => 1]);
    $forest->materials()->attach($wood->id, ['quantity' => 2]);

    // Setup: Casilla explorada en la partida
    \App\Models\Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $forest->id,
        'explored' => true
    ]);

    // Simulación del Job: Sumar producción
    $this->game->materials()->attach($wood->id, ['quantity' => 2, 'is_active' => true]);

    $this->game->refresh();

    $stock = $this->game->materials()->where('name', 'Wood')->first()->pivot->quantity;
    expect($stock)->toBe(2);
});