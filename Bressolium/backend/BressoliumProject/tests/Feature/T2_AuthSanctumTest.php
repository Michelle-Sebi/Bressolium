<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST PARA: TAREA 2 (Raw_Tareas)
// Título: Setup de autenticación API con Sanctum
// Requisitos: HU 1.1 - Registro y Login. Regla de API REST JSON estandarizada.
// ==========================================

test('post a /api/register devuelve token y usuario en standar JSON', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Jugador1',
        'email' => 'jugador1@bressolium.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
        'success',
        'data' => [
            'user' => ['id', 'name', 'email'],
            'token'
        ],
        'error'
    ]);

    expect($response->json('success'))->toBeTrue();
});

test('post a /api/login con datos correctos devuelve HTTP 200 y token', function () {
    // Preparación
    $user = User::factory()->create([
        'email' => 'test@auth.com',
        'password' => bcrypt('authpass')
    ]);

    // Ejecución
    $response = $this->postJson('/api/login', [
        'email' => 'test@auth.com',
        'password' => 'authpass'
    ]);

    // Aserción
    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['token']]);
});

test('rutas protegidas rechazan usuarios sin token Sanctum', function () {
    $response = $this->getJson('/api/user'); // Endpoint base de prueba de Sanctum
    $response->assertStatus(401);
});