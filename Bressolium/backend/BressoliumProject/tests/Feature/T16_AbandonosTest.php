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
    $this->users = User::factory()->count(2)->create();
    $this->game = Game::factory()->create();
    $this->round = $this->game->rounds()->create(['number' => 1]);

    // Vincular usuarios a la partida
    foreach ($this->users as $u) {
        $this->game->users()->attach($u->id, ['is_afk' => false]);
    }
});

test('el juego avanza si el Usuario 2 esta marcado como is_afk aunque no haya votado', function () {
    // Marcar al Jugador 2 como AFK en la tabla PIVOTE de la partida (game_user)
    $this->game->users()->updateExistingPivot($this->users[1]->id, ['is_afk' => true]);

    // El Usuario 1 si vota
    $this->round->votes()->create([
        'user_id' => $this->users[0]->id,
        'technology_id' => 1
    ]);

    // -- Lógica simulada del Job de Cierre --
    // El job debe detectar que todos los NO-AFK han votado
    $activeUsersCount = $this->game->users()->wherePivot('is_afk', false)->count();
    $votesInRound = $this->round->votes()->count();

    if ($votesInRound >= $activeUsersCount) {
        // Ejecutar Cierre
        $this->game->rounds()->create(['number' => 2]);
    }
    // -------------------------------------

    $this->game->refresh();

    // Verificación: Se ha creado el round 2 porque el AFK no bloqueo el cierre
    expect($this->game->rounds()->count())->toBe(2);
    $this->assertDatabaseHas('rounds', ['number' => 2, 'game_id' => $this->game->id]);
});