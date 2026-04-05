<?php

use App\Models\User;
use App\Models\Partida;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST PARA: TAREA 10 (Raw_Tareas)
// Título: Estado JSON en DB y Endpoint de Sincronización
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->partida = Partida::factory()->create([
        'estado_jornada' => json_encode([
            'acciones_restantes' => 2,
            'votos' => []
        ])
    ]);

    $this->user->partidas()->attach($this->partida->id);
    $this->actingAs($this->user);
});

test('endpoint GET /api/partida/sync devuelve el JSON de la jornada limpio', function () {
    $response = $this->getJson("/api/partida/{$this->partida->id}/sync");

    $response->assertStatus(200)
        ->assertJsonStructure([
        'success',
        'data' => [
            'estado_jornada' => [
                'acciones_restantes',
                'votos'
            ]
        ]
    ]);

    expect($response->json('data.estado_jornada.acciones_restantes'))->toBe(2);
});