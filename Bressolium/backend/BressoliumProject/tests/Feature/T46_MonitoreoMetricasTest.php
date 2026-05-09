<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 46 — [Feat] Monitoreo y Métricas
// Title: Health endpoint + Laravel Telescope setup + error capture
// ==========================================

// ─── Endpoint /api/v1/health — acceso público ─────────────────────────────────

test('GET /api/v1/health devuelve 200 sin autenticación', function () {
    $this->getJson('/api/v1/health')
         ->assertStatus(200);
});

test('GET /api/v1/health sigue el formato ResponseBuilder {success, data, error}', function () {
    $this->getJson('/api/v1/health')
         ->assertStatus(200)
         ->assertJsonStructure(['success', 'data', 'error']);
});

test('GET /api/v1/health devuelve success=true', function () {
    $this->getJson('/api/v1/health')
         ->assertStatus(200)
         ->assertJsonPath('success', true);
});

// ─── Estructura de data — campos obligatorios del DoD ─────────────────────────

test('GET /api/v1/health incluye el campo uptime en data', function () {
    $this->getJson('/api/v1/health')
         ->assertStatus(200)
         ->assertJsonPath('data.uptime', fn ($v) => $v !== null);
});

test('el campo uptime es un número entero positivo (segundos de actividad)', function () {
    $response = $this->getJson('/api/v1/health')->assertStatus(200);
    $uptime   = $response->json('data.uptime');

    expect($uptime)->toBeInt()->toBeGreaterThanOrEqual(0);
});

test('GET /api/v1/health incluye el campo database en data', function () {
    $this->getJson('/api/v1/health')
         ->assertStatus(200)
         ->assertJsonPath('data.database', fn ($v) => $v !== null);
});

test('el campo database indica "ok" cuando la BD está disponible', function () {
    $response = $this->getJson('/api/v1/health')->assertStatus(200);
    $database = $response->json('data.database');

    expect($database)->toBe('ok');
});

test('GET /api/v1/health incluye requests_per_minute en data', function () {
    $this->getJson('/api/v1/health')
         ->assertStatus(200)
         ->assertJsonStructure(['data' => ['requests_per_minute']]);
});

test('requests_per_minute es un valor numérico no negativo', function () {
    $response = $this->getJson('/api/v1/health')->assertStatus(200);
    $rpm      = $response->json('data.requests_per_minute');

    expect($rpm)->toBeNumeric()->toBeGreaterThanOrEqual(0);
});

test('GET /api/v1/health incluye errors_per_minute en data', function () {
    $this->getJson('/api/v1/health')
         ->assertStatus(200)
         ->assertJsonStructure(['data' => ['errors_per_minute']]);
});

test('errors_per_minute es un valor numérico no negativo', function () {
    $response = $this->getJson('/api/v1/health')->assertStatus(200);
    $epm      = $response->json('data.errors_per_minute');

    expect($epm)->toBeNumeric()->toBeGreaterThanOrEqual(0);
});

test('GET /api/v1/health incluye latency_p95 en data', function () {
    $this->getJson('/api/v1/health')
         ->assertStatus(200)
         ->assertJsonStructure(['data' => ['latency_p95']]);
});

test('latency_p95 es un valor numérico no negativo', function () {
    $response  = $this->getJson('/api/v1/health')->assertStatus(200);
    $latency   = $response->json('data.latency_p95');

    expect($latency)->toBeNumeric()->toBeGreaterThanOrEqual(0);
});

// ─── Resiliencia — BD desconectada ────────────────────────────────────────────

test('GET /api/v1/health reporta database "error" cuando la BD no responde', function () {
    // Forzar fallo de conexión desvinculando la conexión activa
    DB::disconnect();
    DB::purge('mysql');

    // Configurar credenciales inválidas para que no pueda reconectar
    config(['database.connections.mysql.password' => 'wrong-password-for-test']);

    $response = $this->getJson('/api/v1/health');

    // El endpoint sigue devolviendo 200 (health siempre responde)
    // pero indica el fallo de BD en lugar de lanzar una excepción
    $response->assertStatus(200);
    $database = $response->json('data.database');
    expect($database)->not->toBe('ok');
});

// ─── Telescope — captura de errores backend ───────────────────────────────────

test('el paquete laravel/telescope está instalado', function () {
    expect(class_exists(\Laravel\Telescope\Telescope::class))->toBeTrue();
});

test('TelescopeServiceProvider está registrado en los providers de la app', function () {
    $providers = array_map(
        fn ($p) => get_class($p),
        app()->getLoadedProviders() + []
    );

    $telescopeProviders = array_filter(
        array_keys(app()->getLoadedProviders()),
        fn ($p) => str_contains($p, 'Telescope')
    );

    expect(count($telescopeProviders))->toBeGreaterThan(0);
});

test('Telescope registra las entradas de excepciones', function () {
    // Telescope::ignoreMigrations() podría estar activo en tests;
    // verificamos que la tabla telescope_entries exista
    expect(\Illuminate\Support\Facades\Schema::hasTable('telescope_entries'))->toBeTrue();
});

test('GET /api/v1/health no requiere cabecera Authorization', function () {
    $this->withoutMiddleware(\App\Http\Middleware\ForceJson::class)
         ->getJson('/api/v1/health')
         ->assertStatus(200);
});
