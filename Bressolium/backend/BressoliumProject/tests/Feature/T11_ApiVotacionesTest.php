<?php

use App\Models\Game;
use App\Models\Invention;
use App\Models\InventionCost;
use App\Models\Material;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 11 (nuevas_tareas.md)
// Title: Progress Voting API (Relational)
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->game = Game::factory()->create();
    $this->round = $this->game->rounds()->create(['number' => 1, 'start_date' => now()]);

    $this->user->games()->attach($this->game->id);
    $this->actingAs($this->user);
});

// ─── Autenticación ────────────────────────────────────────────────────────────

test('POST /api/v1/game/{id}/vote devuelve 401 sin sesión activa', function () {
    $this->app['auth']->forgetGuards();
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);

    $this->postJson("/api/v1/game/{$this->game->id}/vote", ['technology_id' => $tech->id])
         ->assertUnauthorized();
});

// ─── Autorización ─────────────────────────────────────────────────────────────

test('POST /api/v1/game/{id}/vote devuelve 403 si el usuario no pertenece a la partida', function () {
    $otherGame = Game::factory()->create();
    $otherGame->rounds()->create(['number' => 1, 'start_date' => now()]);
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);

    $this->postJson("/api/v1/game/{$otherGame->id}/vote", ['technology_id' => $tech->id])
         ->assertForbidden();
});

// ─── Casos felices ────────────────────────────────────────────────────────────

test('almacena el voto en la tabla votes vinculandolo a la jornada actual', function () {
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);

    $this->postJson("/api/v1/game/{$this->game->id}/vote", [
        'technology_id' => $tech->id,
    ])->assertStatus(200);

    $this->assertDatabaseHas('votes', [
        'round_id'      => $this->round->id,
        'user_id'       => $this->user->id,
        'technology_id' => $tech->id,
    ]);
});

test('permite votar por un invento en lugar de una tecnologia', function () {
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);
    $inv  = Invention::create(['name' => 'Hacha', 'technology_id' => $tech->id]);

    $material = Material::create(['name' => 'Silex', 'tier' => 0, 'group' => 'cantera']);
    InventionCost::create(['invention_id' => $inv->id, 'resource_id' => $material->id, 'quantity' => 5]);
    $this->game->materials()->attach($material->id, ['quantity' => 10]);

    $this->postJson("/api/v1/game/{$this->game->id}/vote", [
        'invention_id' => $inv->id,
    ])->assertStatus(200);

    $this->assertDatabaseHas('votes', [
        'user_id'      => $this->user->id,
        'invention_id' => $inv->id,
    ]);
});

test('devuelve respuesta con estructura {success, data, error}', function () {
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);

    $this->postJson("/api/v1/game/{$this->game->id}/vote", [
        'technology_id' => $tech->id,
    ])->assertStatus(200)
      ->assertJsonStructure(['success', 'data', 'error'])
      ->assertJsonPath('success', true)
      ->assertJsonPath('error', null);
});

// ─── Validación: usuario ya votó ──────────────────────────────────────────────

test('devuelve 422 si el usuario ya ha votado en la jornada actual', function () {
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);

    $this->round->votes()->create([
        'user_id'       => $this->user->id,
        'technology_id' => $tech->id,
    ]);

    $this->postJson("/api/v1/game/{$this->game->id}/vote", [
        'technology_id' => $tech->id,
    ])->assertStatus(422);
});

// ─── Validación: tecnología ya completada ─────────────────────────────────────

test('devuelve 422 si se vota por una tecnología ya investigada (is_active=true)', function () {
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);
    $this->game->technologies()->attach($tech->id, ['is_active' => true]);

    $this->postJson("/api/v1/game/{$this->game->id}/vote", [
        'technology_id' => $tech->id,
    ])->assertStatus(422);
});

test('permite votar por una tecnología no investigada (is_active=false)', function () {
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);
    $this->game->technologies()->attach($tech->id, ['is_active' => false]);

    $this->postJson("/api/v1/game/{$this->game->id}/vote", [
        'technology_id' => $tech->id,
    ])->assertStatus(200);
});

// ─── Validación: invento sin materiales suficientes ───────────────────────────

test('devuelve 422 si el equipo no tiene materiales suficientes para construir el invento', function () {
    $tech     = Technology::create(['name' => 'Herramientas de Piedra']);
    $inv      = Invention::create(['name' => 'Hacha', 'technology_id' => $tech->id]);
    $material = Material::create(['name' => 'Silex', 'tier' => 0, 'group' => 'cantera']);

    InventionCost::create(['invention_id' => $inv->id, 'resource_id' => $material->id, 'quantity' => 10]);
    $this->game->materials()->attach($material->id, ['quantity' => 3]); // insuficiente

    $this->postJson("/api/v1/game/{$this->game->id}/vote", [
        'invention_id' => $inv->id,
    ])->assertStatus(422);
});

test('permite votar por un invento cuando los materiales son exactamente suficientes', function () {
    $tech     = Technology::create(['name' => 'Herramientas de Piedra']);
    $inv      = Invention::create(['name' => 'Hacha', 'technology_id' => $tech->id]);
    $material = Material::create(['name' => 'Silex', 'tier' => 0, 'group' => 'cantera']);

    InventionCost::create(['invention_id' => $inv->id, 'resource_id' => $material->id, 'quantity' => 10]);
    $this->game->materials()->attach($material->id, ['quantity' => 10]); // exacto

    $this->postJson("/api/v1/game/{$this->game->id}/vote", [
        'invention_id' => $inv->id,
    ])->assertStatus(200);
});

test('permite votar por un invento sin coste de materiales definido', function () {
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);
    $inv  = Invention::create(['name' => 'Trampa', 'technology_id' => $tech->id]);
    // sin InventionCost → coste cero

    $this->postJson("/api/v1/game/{$this->game->id}/vote", [
        'invention_id' => $inv->id,
    ])->assertStatus(200);
});

// ─── Arquitectura ────────────────────────────────────────────────────────────

test('existe VoteDTO en DTOs', function () {
    expect(class_exists(\App\DTOs\VoteDTO::class))->toBeTrue();
});

test('existe contrato VoteRepositoryInterface', function () {
    expect(interface_exists(\App\Repositories\Contracts\VoteRepositoryInterface::class))->toBeTrue();
});

test('existe implementación VoteRepository en Eloquent', function () {
    expect(class_exists(\App\Repositories\Eloquent\VoteRepository::class))->toBeTrue();
});

test('existe VoteService en Services', function () {
    expect(class_exists(\App\Services\VoteService::class))->toBeTrue();
});
