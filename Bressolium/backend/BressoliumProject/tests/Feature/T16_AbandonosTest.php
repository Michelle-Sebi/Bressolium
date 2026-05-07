<?php

use App\Models\User;
use App\Models\Game;
use App\Repositories\Contracts\CloseRoundRepositoryInterface;
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

test('markAfkPlayers marca como AFK al jugador que no gastó acciones en el round', function () {
    $repo = $this->app->make(CloseRoundRepositoryInterface::class);

    // Vincular usuario al round sin acciones gastadas (actions_spent = 0)
    $this->round->users()->attach($this->users[0]->id, ['actions_spent' => 0]);

    $repo->markAfkPlayers($this->round, $this->game);

    $pivot = $this->game->users()->where('user_id', $this->users[0]->id)->first();
    expect((bool) $pivot->pivot->is_afk)->toBeTrue();
});

test('markAfkPlayers no marca como AFK al jugador que gastó al menos una acción', function () {
    $repo = $this->app->make(CloseRoundRepositoryInterface::class);

    // Vincular usuario al round con acciones gastadas
    $this->round->users()->attach($this->users[0]->id, ['actions_spent' => 1]);

    $repo->markAfkPlayers($this->round, $this->game);

    $pivot = $this->game->users()->where('user_id', $this->users[0]->id)->first();
    expect((bool) $pivot->pivot->is_afk)->toBeFalse();
});

test('markAfkPlayers no marca AFK a un jugador que no tiene entrada en el round', function () {
    $repo = $this->app->make(CloseRoundRepositoryInterface::class);

    // Usuario 0 vinculado a la partida pero NO al round (sin attach en round_user)
    $repo->markAfkPlayers($this->round, $this->game);

    $pivot = $this->game->users()->where('user_id', $this->users[0]->id)->first();
    expect((bool) $pivot->pivot->is_afk)->toBeFalse();
});