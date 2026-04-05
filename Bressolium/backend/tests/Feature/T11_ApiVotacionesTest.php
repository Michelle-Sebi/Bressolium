<?php

use App\Models\User;
use App\Models\Partida;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST PARA: TAREA 11 (Raw_Tareas)
// Título: API Votaciones de Progreso
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();

    // Partida con JSON limpio
    $this->partida = Partida::factory()->create([
        'estado_jornada' => json_encode([
            'acciones_restantes' => 2,
            'votos' => []
        ])
    ]);
    $this->user->partidas()->attach($this->partida->id);
    $this->actingAs($this->user);
});

test('almacena el voto inyectandolo de forma recurrente al JSON avoiding overrides', function () {
    $tech_id = 99; // ID Tecnología de prueba

    $response = $this->postJson("/api/partida/{$this->partida->id}/votar", [
        'tech_id' => $tech_id
    ]);

    $response->assertStatus(200);

    $this->partida->refresh();
    $estado_nuevo = json_decode($this->partida->estado_jornada, true);

    expect(count($estado_nuevo['votos']))->toBe(1)
        ->and($estado_nuevo['votos'][0]['user_id'])->toBe($this->user->id)
        ->and($estado_nuevo['votos'][0]['tech_id'])->toBe($tech_id);
});