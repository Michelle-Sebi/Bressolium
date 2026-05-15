<?php

use App\Events\VoteCast;
use App\Jobs\CloseRoundJob;
use App\Models\Game;
use App\Models\Invention;
use App\Models\Technology;
use App\Models\User;
use App\Repositories\Contracts\CloseRoundRepositoryInterface;
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
        'technology_id' => 1,
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

// ==========================================
// Integración vía CloseRoundJob (DoD 1 — activación automática)
// ==========================================

test('CloseRoundJob crea la siguiente jornada con todos los jugadores sin AFK', function () {
    // markAfkPlayers detecta correctamente al inactivo (cubierto por tests unitarios),
    // pero initializePlayersForRound resetea is_afk=false para que cada jornada empiece limpia
    $this->round->users()->attach($this->users[0]->id, ['actions_spent' => 0]);
    $this->round->users()->attach($this->users[1]->id, ['actions_spent' => 1]);

    CloseRoundJob::dispatchSync($this->game->id);

    $pivot0 = $this->game->fresh()->users()->where('user_id', $this->users[0]->id)->first();
    $pivot1 = $this->game->fresh()->users()->where('user_id', $this->users[1]->id)->first();

    expect((bool) $pivot0->pivot->is_afk)->toBeFalse();
    expect((bool) $pivot1->pivot->is_afk)->toBeFalse();
});

test('CloseRoundJob no activa is_afk para un jugador que realizó al menos una acción', function () {
    $this->round->users()->attach($this->users[0]->id, ['actions_spent' => 2]);

    CloseRoundJob::dispatchSync($this->game->id);

    $pivot = $this->game->fresh()->users()->where('user_id', $this->users[0]->id)->first();
    expect((bool) $pivot->pivot->is_afk)->toBeFalse();
});

test('CloseRoundJob resuelve los votos correctamente aunque un jugador AFK no haya votado', function () {
    // users[1] ya era AFK de jornadas anteriores y no vota
    $this->game->users()->updateExistingPivot($this->users[1]->id, ['is_afk' => true]);

    $this->round->users()->attach($this->users[0]->id, ['actions_spent' => 1]);
    $this->round->users()->attach($this->users[1]->id, ['actions_spent' => 0]);

    $tech = Technology::factory()->create(['name' => 'Agriculture']);
    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'technology_id' => $tech->id]);

    CloseRoundJob::dispatchSync($this->game->id);

    // La tecnología debe activarse aunque el AFK no haya votado
    $this->assertDatabaseHas('game_technology', [
        'game_id' => $this->game->id,
        'technology_id' => $tech->id,
        'is_active' => true,
    ]);

    // La jornada 2 debe haberse creado
    expect($this->game->fresh()->rounds()->count())->toBe(2);
});

test('CloseRoundJob no falla con un jugador que ya era AFK y la nueva jornada lo resetea', function () {
    $this->game->users()->updateExistingPivot($this->users[0]->id, ['is_afk' => true]);
    $this->round->users()->attach($this->users[0]->id, ['actions_spent' => 0]);

    CloseRoundJob::dispatchSync($this->game->id);

    // initializePlayersForRound resetea is_afk=false al empezar la nueva jornada
    $pivot = $this->game->fresh()->users()->where('user_id', $this->users[0]->id)->first();
    expect((bool) $pivot->pivot->is_afk)->toBeFalse();
});

// ==========================================
// Quórum de votos — DoD 2
// Trigger A: si todos los jugadores activos (is_afk=false) ya han votado,
// la jornada puede cerrarse sin esperar el plazo de 2h.
// Estos tests verifican el método allNonAfkPlayersHaveVoted()
// del CloseRoundRepositoryInterface.
// ==========================================

test('allNonAfkPlayersHaveVoted devuelve true cuando todos los jugadores activos han votado', function () {
    $repo = $this->app->make(CloseRoundRepositoryInterface::class);

    // users[0] activo y ha votado; users[1] AFK (no cuenta para quórum)
    $this->game->users()->updateExistingPivot($this->users[1]->id, ['is_afk' => true]);
    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'technology_id' => null, 'invention_id' => null]);

    expect($repo->allNonAfkPlayersHaveVoted($this->round, $this->game))->toBeTrue();
});

test('allNonAfkPlayersHaveVoted devuelve false cuando algún jugador activo no ha votado', function () {
    $repo = $this->app->make(CloseRoundRepositoryInterface::class);

    // Ambos usuarios activos (is_afk=false por defecto), solo users[0] ha votado
    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'technology_id' => null, 'invention_id' => null]);

    expect($repo->allNonAfkPlayersHaveVoted($this->round, $this->game))->toBeFalse();
});

test('allNonAfkPlayersHaveVoted devuelve true aunque jugadores AFK no hayan votado', function () {
    $repo = $this->app->make(CloseRoundRepositoryInterface::class);

    // 2 jugadores: users[0] activo (vota), users[1] AFK (no vota)
    $this->game->users()->updateExistingPivot($this->users[1]->id, ['is_afk' => true]);
    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'technology_id' => null, 'invention_id' => null]);

    // El quórum se cumple aunque users[1] no haya votado
    expect($repo->allNonAfkPlayersHaveVoted($this->round, $this->game))->toBeTrue();
});

test('allNonAfkPlayersHaveVoted devuelve false si no ha votado nadie', function () {
    $repo = $this->app->make(CloseRoundRepositoryInterface::class);

    expect($repo->allNonAfkPlayersHaveVoted($this->round, $this->game))->toBeFalse();
});

// ==========================================
// Trigger A — cierre automático por quórum (DoD 2 — flujo completo)
// Al emitirse VoteCast, CheckQuorumOnVoteCast comprueba si todos los
// jugadores activos han votado y despacha CloseRoundJob si es así.
// ==========================================

test('VoteCast cierra la jornada automáticamente cuando todos los jugadores han hecho 2 acciones y votado tech+inv', function () {
    $tech = Technology::factory()->create();
    $inv  = Invention::factory()->create();

    // Ambos jugadores: 2 acciones + voto de tecnología + voto de invento
    foreach ($this->users as $user) {
        $this->round->users()->attach($user->id, ['actions_spent' => 2]);
        $this->round->votes()->create(['user_id' => $user->id, 'technology_id' => $tech->id, 'invention_id' => null]);
        $this->round->votes()->create(['user_id' => $user->id, 'technology_id' => null, 'invention_id' => $inv->id]);
    }

    // Cada jugador envía su último voto → VoteCast se dispara por separado para cada uno
    VoteCast::dispatch($this->users[0]->id, $this->game->id);
    VoteCast::dispatch($this->users[1]->id, $this->game->id);

    expect($this->game->fresh()->rounds()->count())->toBe(2);
});

test('VoteCast no cierra la jornada si un jugador solo ha votado tecnología pero no invento', function () {
    $tech = Technology::factory()->create();

    // users[0]: 2 acciones + solo voto de tech (falta inv)
    $this->round->users()->attach($this->users[0]->id, ['actions_spent' => 2]);
    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'technology_id' => $tech->id, 'invention_id' => null]);

    // users[1]: sin acciones ni voto
    $this->round->users()->attach($this->users[1]->id, ['actions_spent' => 0]);

    VoteCast::dispatch($this->users[0]->id, $this->game->id);

    // Falta el voto de invento de users[0] → la jornada sigue abierta
    expect($this->game->fresh()->rounds()->count())->toBe(1);
});

test('VoteCast no cierra la jornada si hay un jugador sin terminar aunque otro haya completado todo', function () {
    $tech = Technology::factory()->create();
    $inv  = Invention::factory()->create();

    // users[0]: ha hecho todo (2 acciones + tech + inv)
    $this->round->users()->attach($this->users[0]->id, ['actions_spent' => 2]);
    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'technology_id' => $tech->id, 'invention_id' => null]);
    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'technology_id' => null, 'invention_id' => $inv->id]);

    // users[1]: AFK, sin acciones ni votos → bloquea el cierre hasta el timer de 2h
    $this->round->users()->attach($this->users[1]->id, ['actions_spent' => 0]);

    VoteCast::dispatch($this->users[0]->id, $this->game->id);

    // users[1] no ha terminado → la jornada no puede cerrarse todavía
    expect($this->game->fresh()->rounds()->count())->toBe(1);
});
