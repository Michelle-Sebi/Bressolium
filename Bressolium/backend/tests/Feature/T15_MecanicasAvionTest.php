<?php

use App\Models\User;
use App\Models\Partida;
use App\Models\Casilla;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST PARA: TAREA 15 (Raw_Tareas)
// Título: Mecánicas del Avión y Fin de Juego
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->partida = Partida::factory()->create([
        'estado_jornada' => json_encode(['acciones_restantes' => 2, 'votos' => []])
    ]);
    $this->user->partidas()->attach($this->partida->id);

    // Casilla súper lejana
    $this->casillaTarget = Casilla::factory()->create([
        'partida_id' => $this->partida->id,
        'x' => 0, 'y' => 10,
        'descubierta' => false
    ]);

    $this->actingAs($this->user);
});

test('puede explorar casilla no-adyacente si la partida tiene desbloqueado el Avion', function () {
    // Simulamos que el Controlador buscaría un atributo "is_airplane_unlocked" en BD o JSON
    $this->partida->update([
        'is_airplane_unlocked' => true
    ]);

    // Intentamos explorar a pesar de estar a (0,10) de distancia
    $response = $this->postJson("/api/casillas/{$this->casillaTarget->id}/explorar");

    $response->assertStatus(200);
    expect($this->casillaTarget->fresh()->descubierta)->toBe(1);
});