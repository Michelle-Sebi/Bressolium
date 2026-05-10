<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

// ==========================================
// TEST FOR: TASK 36
// Title: [Feat] Rate Limiting y Versionado de API
// ==========================================

// ─── Rutas registradas bajo /api/v1/ ─────────────────────────────────────────

test('ruta POST /api/v1/register está registrada', function () {
    $match = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($r) => $r->uri() === 'api/v1/register' && in_array('POST', $r->methods()));

    expect($match)->not->toBeNull();
});

test('ruta POST /api/v1/login está registrada', function () {
    $match = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($r) => $r->uri() === 'api/v1/login' && in_array('POST', $r->methods()));

    expect($match)->not->toBeNull();
});

test('ruta POST /api/v1/game/create está registrada', function () {
    $match = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($r) => $r->uri() === 'api/v1/game/create' && in_array('POST', $r->methods()));

    expect($match)->not->toBeNull();
});

test('ruta POST /api/v1/game/join está registrada', function () {
    $match = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($r) => $r->uri() === 'api/v1/game/join' && in_array('POST', $r->methods()));

    expect($match)->not->toBeNull();
});

test('ruta GET /api/v1/board/{gameId} está registrada', function () {
    $match = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($r) => $r->uri() === 'api/v1/board/{gameId}' && in_array('GET', $r->methods()));

    expect($match)->not->toBeNull();
});

test('ruta POST /api/v1/tiles/{id}/explore está registrada', function () {
    $match = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($r) => $r->uri() === 'api/v1/tiles/{id}/explore' && in_array('POST', $r->methods()));

    expect($match)->not->toBeNull();
});

test('ruta POST /api/v1/tiles/{id}/upgrade está registrada', function () {
    $match = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($r) => $r->uri() === 'api/v1/tiles/{id}/upgrade' && in_array('POST', $r->methods()));

    expect($match)->not->toBeNull();
});

// ─── Old /api/ (sin v1) ya no existe ─────────────────────────────────────────

test('POST /api/register devuelve 404 tras el versionado', function () {
    $this->postJson('/api/register', [])->assertStatus(404);
});

test('POST /api/login devuelve 404 tras el versionado', function () {
    $this->postJson('/api/login', [])->assertStatus(404);
});

test('POST /api/game/create devuelve 404 tras el versionado', function () {
    $this->postJson('/api/game/create', [])->assertStatus(404);
});

// ─── Throttle middleware aplicado a rutas autenticadas ───────────────────────

test('las rutas autenticadas llevan middleware throttle', function () {
    $authenticatedRoutes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => str_starts_with($r->uri(), 'api/v1/') && ! in_array($r->uri(), ['api/v1/register', 'api/v1/login']));

    expect($authenticatedRoutes)->not->toBeEmpty();

    foreach ($authenticatedRoutes as $route) {
        $middleware = $route->middleware();
        $hasThrottle = collect($middleware)->contains(fn ($m) => str_starts_with($m, 'throttle:'));
        expect($hasThrottle)->toBeTrue("La ruta {$route->uri()} no tiene throttle middleware");
    }
});

test('las rutas públicas /api/v1/register y /api/v1/login también tienen throttle', function () {
    $publicRoutes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => in_array($r->uri(), ['api/v1/register', 'api/v1/login']));

    foreach ($publicRoutes as $route) {
        $hasThrottle = collect($route->middleware())->contains(fn ($m) => str_starts_with($m, 'throttle:'));
        expect($hasThrottle)->toBeTrue("La ruta {$route->uri()} no tiene throttle middleware");
    }
});

// ─── Comportamiento: rate limit devuelve 429 al superar el límite ─────────────

describe('rate limiting devuelve 429 (requiere DB)', function () {
    uses(RefreshDatabase::class);

    test('POST /api/v1/login devuelve 429 al superar el límite de peticiones', function () {
        // Simula superar el throttle configurando un límite muy bajo en el test
        // El límite real es 60/min; aquí usamos withoutExceptionHandling para ver el 429
        $response = null;

        // Ejecuta 61 peticiones — la última debe ser 429
        for ($i = 0; $i <= 60; $i++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => 'noexiste@test.com',
                'password' => 'cualquier',
            ]);
        }

        $response->assertStatus(429);
    });
});

// ─── Frontend: VITE_API_URL apunta a /api/v1 ─────────────────────────────────

test('el archivo .env del frontend declara VITE_API_URL con /api/v1', function () {
    $envPath = base_path('../../../../frontend/bressolium-front/.env');

    if (! file_exists($envPath)) {
        $this->markTestSkipped('Archivo .env del frontend no encontrado.');
    }

    $content = file_get_contents($envPath);
    expect($content)->toContain('VITE_API_URL=')
        ->and($content)->toContain('/api/v1');
});
