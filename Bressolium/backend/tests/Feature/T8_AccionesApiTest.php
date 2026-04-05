<?php

use App\Models\User;
use App\Models\Partida;
use App\Models\Casilla;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST PARA: TAREA 8 (Raw_Tareas)
// Título: Acciones Individuales API (Explorar / Mejorar)
// Notas CRÍTICAS TDD: Aquí se prueban bloqueos transaccionales
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();

    // Inyectamos JSON en BD. Empieza con 2 acciones.
    $this->partida = Partida::factory()->create([
        'estado_jornada' => json_encode([
            'acciones_restantes' => 2,
            'votos' => []
        ])
    ]);

    $this->user->partidas()->attach($this->partida->id);
    $this->casilla = Casilla::factory()->create([
        'partida_id' => $this->partida->id,
        'x' => 1, 'y' => 1,
        'descubierta' => false
    ]);

    $this->actingAs($this->user);
});

test('explorar descuenta 1 acción de estado_jornada JSON y descubre casilla', function () {
    $response = $this->postJson("/api/casillas/{$this->casilla->id}/explorar");

    $response->assertStatus(200);

    // Refrescar y Comprobar BD
    $this->partida->refresh();
    $this->casilla->refresh();

    $estado_nuevo = json_decode($this->partida->estado_jornada, true);

    expect($estado_nuevo['acciones_restantes'])->toBe(1)
        ->and($this->casilla->descubierta)->toBe(1); // Casilla desvelada
});

test('rechaza accion de explorar y devuelve 403 si las acciones son cero', function () {
    // Forzamos el JSON a 0 acciones
    $this->partida->update([
        'estado_jornada' => json_encode(['acciones_restantes' => 0])
    ]);

    $response = $this->postJson("/api/casillas/{$this->casilla->id}/explorar");

    // Fallo de backend esperado por regla de negocio
    $response->assertStatus(403)
        ->assertJson(['error' => 'No te quedan acciones este turno']);
});

test('rechaza peticion de evolucionar (HTTP 400) si el JSON no tiene suficientes materiales', function () {
    // Asumiendo que el equipo tiene 5 de Madera, pero evolucionar a Nivel 1 pide 10.
    $this->partida->update([
        'estado_jornada' => json_encode([
            'acciones_restantes' => 2,
            'inventario_materiales' => ['Madera' => 5]
        ])
    ]);

    // Simulamos endpoint de evolucionar (Mejorar)
    $response = $this->postJson("/api/casillas/{$this->casilla->id}/evolucionar");

    $response->assertStatus(400)
        ->assertJson(['error' => 'Materiales insuficientes para esta mejora']);
});