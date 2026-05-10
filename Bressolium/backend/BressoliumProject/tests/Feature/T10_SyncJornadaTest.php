<?php

use App\DTOs\SyncResponseDTO;
use App\Http\Requests\SyncRequest;
use App\Http\Resources\SyncResource;
use App\Models\Game;
use App\Models\Invention;
use App\Models\Material;
use App\Models\Technology;
use App\Models\User;
use App\Repositories\Contracts\SyncRepositoryInterface;
use App\Repositories\Eloquent\SyncRepository;
use App\Services\SyncService;
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
        'number' => 1,
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
                'user_actions' => ['actions_spent'],
                'inventory' => [
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
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);
    $invento = Invention::create(['name' => 'Hacha', 'technology_id' => $tech->id]);
    $this->game->inventions()->attach($invento->id, ['is_active' => true, 'quantity' => 3]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
        ->assertStatus(200);

    $inventions = collect($response->json('data.progress.inventions'));
    $found = $inventions->firstWhere('name', 'Hacha');

    expect($found)->not->toBeNull()
        ->and($found['quantity'])->toBe(3);
});

test('sync devuelve quantity 0 para inventos sin construir', function () {
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);
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

// ─── missing: prerrequisitos y costes que faltan ─────────────────────────────

test('sync incluye campo missing en cada tecnología (vacío cuando no hay prerrequisitos)', function () {
    $tech = Technology::create(['name' => 'Control del Fuego']);
    $this->game->technologies()->attach($tech->id, ['is_active' => false]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
        ->assertStatus(200);

    $technologies = collect($response->json('data.progress.technologies'));
    $found = $technologies->firstWhere('name', 'Control del Fuego');

    expect($found)->toHaveKey('missing')
        ->and($found['missing'])->toBeArray()->toBeEmpty();
});

test('sync indica missing cuando una tecnología tiene prerrequisito no activo', function () {
    $prereqTech = Technology::create(['name' => 'Fuego Controlado']);
    $mainTech = Technology::create(['name' => 'Metalurgia']);
    $mainTech->technologyPrerequisites()->create([
        'prereq_type' => 'technology',
        'prereq_id' => $prereqTech->id,
        'quantity' => 1,
    ]);

    $this->game->technologies()->attach($prereqTech->id, ['is_active' => false]);
    $this->game->technologies()->attach($mainTech->id, ['is_active' => false]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
        ->assertStatus(200);

    $technologies = collect($response->json('data.progress.technologies'));
    $found = $technologies->firstWhere('name', 'Metalurgia');

    expect($found['missing'])->toHaveCount(1)
        ->and($found['missing'][0]['type'])->toBe('technology')
        ->and($found['missing'][0]['name'])->toBe('Fuego Controlado');
});

test('sync devuelve missing vacío cuando el prerrequisito ya está activo', function () {
    $prereqTech = Technology::create(['name' => 'Fuego Controlado']);
    $mainTech = Technology::create(['name' => 'Metalurgia']);
    $mainTech->technologyPrerequisites()->create([
        'prereq_type' => 'technology',
        'prereq_id' => $prereqTech->id,
        'quantity' => 1,
    ]);

    $this->game->technologies()->attach($prereqTech->id, ['is_active' => true]);
    $this->game->technologies()->attach($mainTech->id, ['is_active' => false]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
        ->assertStatus(200);

    $technologies = collect($response->json('data.progress.technologies'));
    $found = $technologies->firstWhere('name', 'Metalurgia');

    expect($found['missing'])->toBeArray()->toBeEmpty();
});

test('sync incluye campo missing en cada invento (vacío sin costes ni prerrequisitos)', function () {
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);
    $invento = Invention::create(['name' => 'Cuchillo', 'technology_id' => $tech->id]);
    $this->game->inventions()->attach($invento->id, ['is_active' => false, 'quantity' => 0]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
        ->assertStatus(200);

    $inventions = collect($response->json('data.progress.inventions'));
    $found = $inventions->firstWhere('name', 'Cuchillo');

    expect($found)->toHaveKey('missing')
        ->and($found['missing'])->toBeArray()->toBeEmpty();
});

test('sync indica missing cuando faltan recursos para construir un invento', function () {
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);
    $material = Material::create(['name' => 'Silex', 'tier' => 0, 'group' => 'cantera']);
    $invento = Invention::create(['name' => 'Hacha', 'technology_id' => $tech->id]);
    $invento->inventionCosts()->create(['resource_id' => $material->id, 'quantity' => 5]);

    $this->game->inventions()->attach($invento->id, ['is_active' => false, 'quantity' => 0]);
    // El equipo tiene solo 2 de Silex (necesita 5)
    $this->game->materials()->attach($material->id, ['quantity' => 2]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
        ->assertStatus(200);

    $inventions = collect($response->json('data.progress.inventions'));
    $found = $inventions->firstWhere('name', 'Hacha');

    expect($found['missing'])->toHaveCount(1)
        ->and($found['missing'][0]['type'])->toBe('resource')
        ->and($found['missing'][0]['name'])->toBe('Silex')
        ->and($found['missing'][0]['required'])->toBe(5)
        ->and($found['missing'][0]['have'])->toBe(2);
});

test('sync devuelve missing vacío cuando el equipo tiene recursos suficientes', function () {
    $tech = Technology::create(['name' => 'Herramientas de Piedra']);
    $material = Material::create(['name' => 'Silex', 'tier' => 0, 'group' => 'cantera']);
    $invento = Invention::create(['name' => 'Hacha', 'technology_id' => $tech->id]);
    $invento->inventionCosts()->create(['resource_id' => $material->id, 'quantity' => 5]);

    $this->game->inventions()->attach($invento->id, ['is_active' => false, 'quantity' => 0]);
    $this->game->materials()->attach($material->id, ['quantity' => 5]);

    $response = $this->getJson("/api/v1/game/{$this->game->id}/sync")
        ->assertStatus(200);

    $inventions = collect($response->json('data.progress.inventions'));
    $found = $inventions->firstWhere('name', 'Hacha');

    expect($found['missing'])->toBeArray()->toBeEmpty();
});

// ─── Arquitectura ────────────────────────────────────────────────────────────

test('existe SyncRequest en Http/Requests', function () {
    expect(class_exists(SyncRequest::class))->toBeTrue();
});

test('existe SyncDTO en DTOs', function () {
    expect(class_exists(SyncResponseDTO::class))->toBeTrue();
});

test('existe SyncResource en Http/Resources', function () {
    expect(class_exists(SyncResource::class))->toBeTrue();
});

test('existe contrato SyncRepositoryInterface', function () {
    expect(interface_exists(SyncRepositoryInterface::class))->toBeTrue();
});

test('existe implementación SyncRepository en Eloquent', function () {
    expect(class_exists(SyncRepository::class))->toBeTrue();
});

test('existe SyncService en Services', function () {
    expect(class_exists(SyncService::class))->toBeTrue();
});
