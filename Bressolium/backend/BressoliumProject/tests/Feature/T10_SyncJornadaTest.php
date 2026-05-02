<?php

use App\Models\Game;
use App\Models\Invention;
use App\Models\Material;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 10 (nuevas_tareas.md)
// Title: Relational Sync and Polling
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->game = Game::factory()->create();
    $this->round = $this->game->rounds()->create([
        'number'     => 1,
        'start_date' => now(),
    ]);

    $this->user->games()->attach($this->game->id);
    $this->round->users()->attach($this->user->id, ['actions_spent' => 1]);
    $this->actingAs($this->user);
});

// ─── Autenticación ────────────────────────────────────────────────────────────

test('GET /api/v1/game/{id}/sync devuelve 401 sin sesión activa', function () {
    $this->app['auth']->forgetGuards();

    $this->getJson("/api/v1/game/{$this->game->id}/sync")
         ->assertUnauthorized();
});

// ─── Autorización ─────────────────────────────────────────────────────────────

test('GET /api/v1/game/{id}/sync devuelve 403 si el usuario no pertenece a la partida', function () {
    $otherGame = Game::factory()->create();
    $otherGame->rounds()->create(['number' => 1, 'start_date' => now()]);

    $this->getJson("/api/v1/game/{$otherGame->id}/sync")
         ->assertForbidden();
});

// ─── Estructura de respuesta ──────────────────────────────────────────────────

test('GET /api/v1/game/{id}/sync devuelve estructura {success, data, error}', function () {
    $this->getJson("/api/v1/game/{$this->game->id}/sync")
         ->assertStatus(200)
         ->assertJsonStructure(['success', 'data', 'error'])
         ->assertJsonPath('success', true)
         ->assertJsonPath('error', null);
});

test('GET /api/v1/game/{id}/sync devuelve la estructura completa de data', function () {
    $this->getJson("/api/v1/game/{$this->game->id}/sync")
         ->assertStatus(200)
         ->assertJsonStructure([
             'success',
             'data' => [
                 'current_round' => ['number', 'start_date'],
                 'user_actions'  => ['actions_spent'],
                 'inventory'     => [
                     '*' => ['id', 'name', 'quantity'],
                 ],
                 'progress' => [
                     'technologies' => [
                         '*' => ['id', 'name', 'is_active'],
                     ],
                     'inventions' => [
                         '*' => ['id', 'name', 'quantity'],
                     ],
                 ],
             ],
             'error',
         ]);
});

// ─── Ronda actual ─────────────────────────────────────────────────────────────

test('sync devuelve el número y fecha de la ronda actual', function () {
    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
                     ->assertStatus(200);

    expect($response->json('data.current_round.number'))->toBe(1)
        ->and($response->json('data.current_round.start_date'))->not->toBeNull();
});

test('sync devuelve las acciones gastadas reales del usuario', function () {
    $this->round->users()->updateExistingPivot($this->user->id, ['actions_spent' => 2]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
                     ->assertStatus(200);

    expect($response->json('data.user_actions.actions_spent'))->toBe(2);
});

// ─── Inventario de materiales ─────────────────────────────────────────────────

test('sync devuelve los materiales del equipo con sus cantidades reales', function () {
    $material = Material::create(['name' => 'Roble', 'tier' => 0, 'group' => 'bosque']);
    $this->game->materials()->attach($material->id, ['quantity' => 7]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
                     ->assertStatus(200);

    $inventory = collect($response->json('data.inventory'));
    $roble = $inventory->firstWhere('name', 'Roble');

    expect($roble)->not->toBeNull()
        ->and($roble['quantity'])->toBe(7);
});

test('sync devuelve inventario vacío cuando el equipo no tiene materiales', function () {
    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
                     ->assertStatus(200);

    expect($response->json('data.inventory'))->toBeArray()->toBeEmpty();
});

// ─── Progreso: Tecnologías ────────────────────────────────────────────────────

test('sync devuelve las tecnologías del equipo con is_active correcto', function () {
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);
    $this->game->technologies()->attach($tech->id, ['is_active' => true]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
                     ->assertStatus(200);

    $technologies = collect($response->json('data.progress.technologies'));
    $found = $technologies->firstWhere('name', 'Herramientas de Piedra');

    expect($found)->not->toBeNull()
        ->and($found['is_active'])->toBeTrue();
});

test('sync devuelve is_active false para tecnologías no investigadas', function () {
    $tech = Technology::create(['name' => 'Control del Fuego']);
    $this->game->technologies()->attach($tech->id, ['is_active' => false]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
                     ->assertStatus(200);

    $technologies = collect($response->json('data.progress.technologies'));
    $found = $technologies->firstWhere('name', 'Control del Fuego');

    expect($found)->not->toBeNull()
        ->and($found['is_active'])->toBeFalse();
});

test('sync devuelve tecnologías vacías cuando el equipo no tiene ninguna', function () {
    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
                     ->assertStatus(200);

    expect($response->json('data.progress.technologies'))->toBeArray()->toBeEmpty();
});

// ─── Progreso: Inventos (con quantity de T48) ─────────────────────────────────

test('sync devuelve los inventos del equipo con su quantity acumulada', function () {
    $tech     = Technology::create(['name' => 'Herramientas de Piedra']);
    $invento  = Invention::create(['name' => 'Hacha', 'technology_id' => $tech->id]);
    $this->game->inventions()->attach($invento->id, ['is_active' => true, 'quantity' => 3]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
                     ->assertStatus(200);

    $inventions = collect($response->json('data.progress.inventions'));
    $found = $inventions->firstWhere('name', 'Hacha');

    expect($found)->not->toBeNull()
        ->and($found['quantity'])->toBe(3);
});

test('sync devuelve quantity 0 para inventos sin construir', function () {
    $tech    = Technology::create(['name' => 'Herramientas de Piedra']);
    $invento = Invention::create(['name' => 'Lanza', 'technology_id' => $tech->id]);
    $this->game->inventions()->attach($invento->id, ['is_active' => false, 'quantity' => 0]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
                     ->assertStatus(200);

    $inventions = collect($response->json('data.progress.inventions'));
    $found = $inventions->firstWhere('name', 'Lanza');

    expect($found)->not->toBeNull()
        ->and($found['quantity'])->toBe(0);
});

test('sync devuelve inventos vacíos cuando el equipo no tiene ninguno', function () {
    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
                     ->assertStatus(200);

    expect($response->json('data.progress.inventions'))->toBeArray()->toBeEmpty();
});

// ─── Arquitectura ────────────────────────────────────────────────────────────

test('existe SyncRequest en Http/Requests', function () {
    expect(class_exists(\App\Http\Requests\SyncRequest::class))->toBeTrue();
});

test('existe SyncDTO en DTOs', function () {
    expect(class_exists(\App\DTOs\SyncResponseDTO::class))->toBeTrue();
});

test('existe SyncResource en Http/Resources', function () {
    expect(class_exists(\App\Http\Resources\SyncResource::class))->toBeTrue();
});

test('existe contrato SyncRepositoryInterface', function () {
    expect(interface_exists(\App\Repositories\Contracts\SyncRepositoryInterface::class))->toBeTrue();
});

test('existe implementación SyncRepository en Eloquent', function () {
    expect(class_exists(\App\Repositories\Eloquent\SyncRepository::class))->toBeTrue();
});

test('existe SyncService en Services', function () {
    expect(class_exists(\App\Services\SyncService::class))->toBeTrue();
});
