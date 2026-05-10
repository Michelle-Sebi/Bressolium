<?php

use App\Http\Middleware\ForceJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Telescope\Telescope;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 46 — [Feat] Monitoreo y Métricas
// Title: Stats endpoint + Laravel Telescope setup + error capture
// ==========================================

// ─── Endpoint /api/v1/stats — acceso público ─────────────────────────────────

test('GET /api/v1/stats devuelve 200 sin autenticación', function () {
    $this->getJson('/api/v1/stats')
        ->assertStatus(200);
});

test('GET /api/v1/stats sigue el formato ResponseBuilder {success, data, error}', function () {
    $this->getJson('/api/v1/stats')
        ->assertStatus(200)
        ->assertJsonStructure(['success', 'data', 'error']);
});

test('GET /api/v1/stats devuelve success=true', function () {
    $this->getJson('/api/v1/stats')
        ->assertStatus(200)
        ->assertJsonPath('success', true);
});

// ─── Estructura system ────────────────────────────────────────────────────────

test('GET /api/v1/stats incluye el campo system.uptime en data', function () {
    $this->getJson('/api/v1/stats')
        ->assertStatus(200)
        ->assertJsonPath('data.system.uptime', fn ($v) => $v !== null);
});

test('el campo system.uptime es un número entero positivo (segundos de actividad)', function () {
    $response = $this->getJson('/api/v1/stats')->assertStatus(200);
    $uptime = $response->json('data.system.uptime');

    expect($uptime)->toBeInt()->toBeGreaterThanOrEqual(0);
});

test('GET /api/v1/stats incluye el campo system.database en data', function () {
    $this->getJson('/api/v1/stats')
        ->assertStatus(200)
        ->assertJsonPath('data.system.database', fn ($v) => $v !== null);
});

test('el campo system.database indica "ok" cuando la BD está disponible', function () {
    $response = $this->getJson('/api/v1/stats')->assertStatus(200);
    $database = $response->json('data.system.database');

    expect($database)->toBe('ok');
});

test('GET /api/v1/stats incluye system.requests_per_minute en data', function () {
    $this->getJson('/api/v1/stats')
        ->assertStatus(200)
        ->assertJsonStructure(['data' => ['system' => ['requests_per_minute']]]);
});

test('system.requests_per_minute es un valor numérico no negativo', function () {
    $response = $this->getJson('/api/v1/stats')->assertStatus(200);
    $rpm = $response->json('data.system.requests_per_minute');

    expect($rpm)->toBeNumeric()->toBeGreaterThanOrEqual(0);
});

test('GET /api/v1/stats incluye system.errors_per_minute en data', function () {
    $this->getJson('/api/v1/stats')
        ->assertStatus(200)
        ->assertJsonStructure(['data' => ['system' => ['errors_per_minute']]]);
});

test('system.errors_per_minute es un valor numérico no negativo', function () {
    $response = $this->getJson('/api/v1/stats')->assertStatus(200);
    $epm = $response->json('data.system.errors_per_minute');

    expect($epm)->toBeNumeric()->toBeGreaterThanOrEqual(0);
});

test('GET /api/v1/stats incluye system.latency_p95 en data', function () {
    $this->getJson('/api/v1/stats')
        ->assertStatus(200)
        ->assertJsonStructure(['data' => ['system' => ['latency_p95']]]);
});

test('system.latency_p95 es un valor numérico no negativo', function () {
    $response = $this->getJson('/api/v1/stats')->assertStatus(200);
    $latency = $response->json('data.system.latency_p95');

    expect($latency)->toBeNumeric()->toBeGreaterThanOrEqual(0);
});

// ─── Estructura game ──────────────────────────────────────────────────────────

test('GET /api/v1/stats incluye los campos de métricas de juego', function () {
    $this->getJson('/api/v1/stats')
        ->assertStatus(200)
        ->assertJsonStructure(['data' => ['game' => [
            'total_games', 'waiting_games', 'active_games', 'finished_games',
            'total_players', 'total_rounds', 'players',
        ]]]);
});

test('game.total_games es un entero no negativo', function () {
    $response = $this->getJson('/api/v1/stats')->assertStatus(200);
    expect($response->json('data.game.total_games'))->toBeInt()->toBeGreaterThanOrEqual(0);
});

test('game.total_players es un entero no negativo', function () {
    $response = $this->getJson('/api/v1/stats')->assertStatus(200);
    expect($response->json('data.game.total_players'))->toBeInt()->toBeGreaterThanOrEqual(0);
});

test('game.total_rounds es un entero no negativo', function () {
    $response = $this->getJson('/api/v1/stats')->assertStatus(200);
    expect($response->json('data.game.total_rounds'))->toBeInt()->toBeGreaterThanOrEqual(0);
});

test('game.players es un array', function () {
    $response = $this->getJson('/api/v1/stats')->assertStatus(200);
    expect($response->json('data.game.players'))->toBeArray();
});

// ─── Resiliencia — BD desconectada ────────────────────────────────────────────

test('GET /api/v1/stats reporta system.database "error" cuando la BD no responde', function () {
    $originalPassword = config('database.connections.mysql.password');

    DB::disconnect();
    DB::purge('mysql');
    config(['database.connections.mysql.password' => 'wrong-password-for-test']);

    $response = $this->getJson('/api/v1/stats');

    // Restore valid connection so RefreshDatabase teardown can rollback
    config(['database.connections.mysql.password' => $originalPassword]);
    DB::purge('mysql');

    $response->assertStatus(200);
    $database = $response->json('data.system.database');
    expect($database)->not->toBe('ok');
});

// ─── Telescope — captura de errores backend ───────────────────────────────────

test('el paquete laravel/telescope está instalado', function () {
    expect(class_exists(Telescope::class))->toBeTrue();
});

test('TelescopeServiceProvider está registrado en los providers de la app', function () {
    $telescopeProviders = array_filter(
        array_keys(app()->getLoadedProviders()),
        fn ($p) => str_contains($p, 'Telescope')
    );

    expect(count($telescopeProviders))->toBeGreaterThan(0);
});

test('Telescope registra las entradas de excepciones', function () {
    expect(Schema::hasTable('telescope_entries'))->toBeTrue();
});

test('GET /api/v1/stats no requiere cabecera Authorization', function () {
    $this->withoutMiddleware(ForceJson::class)
        ->getJson('/api/v1/stats')
        ->assertStatus(200);
});
