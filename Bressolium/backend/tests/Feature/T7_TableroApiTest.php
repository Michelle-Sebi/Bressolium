<?php

use App\Models\User;
use App\Models\Partida;
use App\Models\Casilla;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST PARA: TAREA 7 (Raw_Tareas)
// Título: Generador y Controlador de Tablero API
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->partida = Partida::factory()->create();
    $this->user->partidas()->attach($this->partida->id);
    $this->actingAs($this->user);
});

test('endpoint /api/tablero devuelve matriz json de casillas asociada a la partida activa', function () {
    // Simulamos casillas creadas via evento o seeder al iniciar la partida
    Casilla::factory()->count(10)->create(['partida_id' => $this->partida->id]);

    $response = $this->getJson("/api/tablero/{$this->partida->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
        'success',
        'data' => [
            '*' => ['id', 'x', 'y', 'recurso_id', 'descubierta']
        ],
        'error'
    ]);

    expect(count($response->json('data')))->toBe(10);
});