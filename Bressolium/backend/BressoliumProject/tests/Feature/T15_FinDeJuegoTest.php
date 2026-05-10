<?php

use App\Events\GameFinished;
use App\Jobs\CloseRoundJob;
use App\Models\Game;
use App\Models\Invention;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

// ============================================================
// T15 — End of Game (Terraforming)
// ============================================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->game = Game::factory()->create(['status' => 'ACTIVE']);
    $this->round = $this->game->rounds()->create(['number' => 1]);
    $this->game->users()->attach($this->user->id, ['is_afk' => false]);
    $this->round->users()->attach($this->user->id, ['actions_spent' => 0]);
});

// ---
// DoD: el campo is_final existe en la tabla inventions
// ---

test('la tabla inventions tiene el campo is_final', function () {
    expect(Schema::hasColumn('inventions', 'is_final'))->toBeTrue();
});

test('el campo is_final es false por defecto en nuevos inventos', function () {
    $invention = Invention::factory()->create();
    expect($invention->fresh()->is_final)->toBeFalse();
});

test('el modelo Invention expone is_final como booleano', function () {
    $final = Invention::factory()->create(['is_final' => true]);
    $normal = Invention::factory()->create(['is_final' => false]);

    expect($final->is_final)->toBeTrue();
    expect($normal->is_final)->toBeFalse();
});

// ---
// DoD: construir "Nave de Asentamiento Interestelar" (is_final=true) cambia el estado a FINISHED
// ---

test('construir el invento final cambia el estado de la partida a FINISHED', function () {
    Event::fake();

    $finalInvention = Invention::factory()->create(['is_final' => true]);
    $this->game->inventions()->attach($finalInvention->id, ['quantity' => 0]);
    $this->round->votes()->create([
        'user_id' => $this->user->id,
        'invention_id' => $finalInvention->id,
    ]);

    CloseRoundJob::dispatchSync($this->game->id);

    expect($this->game->fresh()->status)->toBe('FINISHED');
});

test('construir un invento no final no cambia el estado de la partida a FINISHED', function () {
    Event::fake();

    $regularInvention = Invention::factory()->create(['is_final' => false]);
    $this->game->inventions()->attach($regularInvention->id, ['quantity' => 0]);
    $this->round->votes()->create([
        'user_id' => $this->user->id,
        'invention_id' => $regularInvention->id,
    ]);

    CloseRoundJob::dispatchSync($this->game->id);

    expect($this->game->fresh()->status)->not->toBe('FINISHED');
});

test('sin votos de inventos la partida no cambia a FINISHED', function () {
    CloseRoundJob::dispatchSync($this->game->id);

    expect($this->game->fresh()->status)->toBe('ACTIVE');
});

test('si el invento final no gana la votación la partida no termina', function () {
    Event::fake();

    $user2 = User::factory()->create();
    $this->game->users()->attach($user2->id, ['is_afk' => false]);
    $this->round->users()->attach($user2->id, ['actions_spent' => 0]);

    $finalInvention = Invention::factory()->create(['is_final' => true]);
    $regularInvention = Invention::factory()->create(['is_final' => false]);
    $this->game->inventions()->attach($finalInvention->id, ['quantity' => 0]);
    $this->game->inventions()->attach($regularInvention->id, ['quantity' => 0]);

    // Regular gana con 2 votos vs 1 del final
    $this->round->votes()->create(['user_id' => $this->user->id, 'invention_id' => $finalInvention->id]);
    $this->round->votes()->create(['user_id' => $user2->id,      'invention_id' => $regularInvention->id]);
    $user3 = User::factory()->create();
    $this->game->users()->attach($user3->id, ['is_afk' => false]);
    $this->round->users()->attach($user3->id, ['actions_spent' => 0]);
    $this->round->votes()->create(['user_id' => $user3->id, 'invention_id' => $regularInvention->id]);

    CloseRoundJob::dispatchSync($this->game->id);

    expect($this->game->fresh()->status)->not->toBe('FINISHED');
});

// ---
// DoD: se notifica a todos los jugadores → evento GameFinished
// ---

test('construir el invento final emite el evento GameFinished con la partida correcta', function () {
    Event::fake();

    $finalInvention = Invention::factory()->create(['is_final' => true]);
    $this->game->inventions()->attach($finalInvention->id, ['quantity' => 0]);
    $this->round->votes()->create([
        'user_id' => $this->user->id,
        'invention_id' => $finalInvention->id,
    ]);

    CloseRoundJob::dispatchSync($this->game->id);

    Event::assertDispatched(GameFinished::class, fn ($e) => $e->game->id === $this->game->id);
});

test('construir un invento no final no emite GameFinished', function () {
    Event::fake();

    $regularInvention = Invention::factory()->create(['is_final' => false]);
    $this->game->inventions()->attach($regularInvention->id, ['quantity' => 0]);
    $this->round->votes()->create([
        'user_id' => $this->user->id,
        'invention_id' => $regularInvention->id,
    ]);

    CloseRoundJob::dispatchSync($this->game->id);

    Event::assertNotDispatched(GameFinished::class);
});

test('sin votos de inventos no se emite GameFinished', function () {
    Event::fake();

    CloseRoundJob::dispatchSync($this->game->id);

    Event::assertNotDispatched(GameFinished::class);
});

// ---
// DoD implícito: una partida FINISHED no crea una nueva ronda
// ---

test('una partida que termina por el invento final no crea una nueva ronda', function () {
    Event::fake();

    $finalInvention = Invention::factory()->create(['is_final' => true]);
    $this->game->inventions()->attach($finalInvention->id, ['quantity' => 0]);
    $this->round->votes()->create([
        'user_id' => $this->user->id,
        'invention_id' => $finalInvention->id,
    ]);

    $roundsBefore = $this->game->rounds()->count();
    CloseRoundJob::dispatchSync($this->game->id);

    expect($this->game->rounds()->count())->toBe($roundsBefore);
});
