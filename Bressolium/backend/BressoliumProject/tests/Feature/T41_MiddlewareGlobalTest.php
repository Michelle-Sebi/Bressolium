<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\User;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 41
// Title: [Feat] Middleware Global (Force JSON + Request Logging)
// ==========================================

// ─── 1. Clases de middleware existen ─────────────────────────────────────────

test('ForceJsonMiddleware existe en App\Http\Middleware', function () {
    expect(class_exists(\App\Http\Middleware\ForceJsonMiddleware::class))->toBeTrue();
});

test('RequestLoggingMiddleware existe en App\Http\Middleware', function () {
    expect(class_exists(\App\Http\Middleware\RequestLoggingMiddleware::class))->toBeTrue();
});

// ─── 2. ForceJson — añade Accept: application/json ───────────────────────────

test('ForceJsonMiddleware fuerza Accept: application/json en peticiones API', function () {
    $response = $this->getJson('/api/v1/login');

    // Si la petición llega a la app, el header Accept fue procesado como JSON
    // El endpoint responde con JSON (validation error o similar), no HTML
    expect($response->headers->get('Content-Type'))->toContain('application/json');
});

test('petición sin header Accept recibe respuesta JSON gracias al middleware', function () {
    // Petición explícita sin Accept header — el middleware debe forzarlo
    $response = $this->call('POST', '/api/v1/login', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], json_encode(['email' => 'x@x.com', 'password' => 'wrong']));

    expect($response->headers->get('Content-Type'))->toContain('application/json');
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveKey('success');
});

test('petición a ruta no existente devuelve JSON cuando el middleware fuerza Accept', function () {
    $response = $this->call('GET', '/api/v1/ruta-que-no-existe', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ]);

    expect($response->headers->get('Content-Type'))->toContain('application/json');
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveKey('success');
    expect($data['success'])->toBeFalse();
});

// ─── 3. ForceJson — actúa solo en rutas API, no en web ───────────────────────

test('ForceJsonMiddleware está registrado en el pipeline de API, no solo en rutas individuales', function () {
    // El middleware debe estar en la configuración global de bootstrap/app.php
    // lo comprobamos verificando que su clase existe y que el comportamiento
    // de forzar JSON se aplica sin necesidad de añadirlo ruta a ruta
    $app = app();
    $kernel = $app->make(\Illuminate\Foundation\Http\Kernel::class);

    $middlewareGroups = $kernel->getMiddlewareGroups();

    $forceJsonPresent = false;
    foreach ($middlewareGroups as $group => $middlewares) {
        foreach ($middlewares as $middleware) {
            if (str_contains($middleware, 'ForceJson')) {
                $forceJsonPresent = true;
                break 2;
            }
        }
    }

    // Si no está en grupos, puede estar en el pipeline global del app
    if (! $forceJsonPresent) {
        $reflection = new ReflectionClass($kernel);
        $property   = $reflection->getProperty('middleware');
        $property->setAccessible(true);
        $globalMiddleware = $property->getValue($kernel);

        foreach ($globalMiddleware as $middleware) {
            if (str_contains($middleware, 'ForceJson')) {
                $forceJsonPresent = true;
                break;
            }
        }
    }

    expect($forceJsonPresent)->toBeTrue('ForceJsonMiddleware debe estar registrado en el pipeline global o en el grupo api');
});

// ─── 4. RequestLogging — registra en el log ───────────────────────────────────

test('RequestLoggingMiddleware registra método y ruta de cada petición', function () {
    Log::spy();

    $this->postJson('/api/v1/login', ['email' => 'test@test.com', 'password' => 'wrong']);

    Log::shouldHaveReceived('info')->withArgs(function ($message, $context) {
        return str_contains($message, 'API Request') || isset($context['method'], $context['path']);
    })->atLeast()->once();
});

test('RequestLoggingMiddleware registra el status de la respuesta', function () {
    Log::spy();

    $this->postJson('/api/v1/login', ['email' => 'test@test.com', 'password' => 'wrong']);

    Log::shouldHaveReceived('info')->withArgs(function ($message, $context) {
        return isset($context['status']) || str_contains((string) json_encode($context), 'status');
    })->atLeast()->once();
});

test('RequestLoggingMiddleware registra el tiempo de respuesta', function () {
    Log::spy();

    $this->postJson('/api/v1/login', ['email' => 'test@test.com', 'password' => 'wrong']);

    Log::shouldHaveReceived('info')->withArgs(function ($message, $context) {
        return isset($context['duration_ms']) || isset($context['time']) || isset($context['duration']);
    })->atLeast()->once();
});

test('RequestLoggingMiddleware registra el usuario autenticado cuando existe', function () {
    Log::spy();

    $user = User::factory()->create();

    $this->actingAs($user)->getJson('/api/v1/game/my');

    Log::shouldHaveReceived('info')->withArgs(function ($message, $context) use ($user) {
        return isset($context['user_id']) && $context['user_id'] === $user->id;
    })->atLeast()->once();
});

test('RequestLoggingMiddleware registra null como usuario en peticiones sin autenticar', function () {
    Log::spy();

    $this->postJson('/api/v1/login', ['email' => 'noexiste@test.com', 'password' => 'wrong']);

    Log::shouldHaveReceived('info')->withArgs(function ($message, $context) {
        return array_key_exists('user_id', $context) && $context['user_id'] === null;
    })->atLeast()->once();
});

// ─── 5. Los middlewares son independientes de Sanctum y throttle ──────────────

test('ForceJsonMiddleware y RequestLoggingMiddleware son clases distintas a Sanctum y throttle', function () {
    expect(\App\Http\Middleware\ForceJsonMiddleware::class)
        ->not->toBe(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);

    expect(\App\Http\Middleware\RequestLoggingMiddleware::class)
        ->not->toBe(\Illuminate\Routing\Middleware\ThrottleRequests::class);
});
