<?php

use App\Models\User;
use App\Models\Partida;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST PARA: TAREA 4 (Raw_Tareas)
// Título: Endpoints CRUD para Equipos (Partidas)
// Requisitos: HU 1.2 (Creación), HU 1.3 (Unirse) y HU 1.4 (Asignación Aleatoria)
// ==========================================

beforeEach(function () {
    // Autenticamos a un usuario para todos estos tests
    $this->user = User::factory()->create();
    $this->actingAs($this->user); // Simula el token Sanctum
});

test('crear equipo inicializa estado json vacío y responde JSON stand', function () {
    $response = $this->postJson('/api/partida/create', [
        'nombre_equipo' => 'Pioneros Digitales',
        'cultura_base' => 'Cyberpunk'
    ]);

    $response->assertStatus(200)
        ->assertJson([
        'success' => true,
    ]);

    // Aserción BD: Comprobar que en MySQL se almacenó JSON
    expect(Partida::count())->toBe(1);
    $partida = Partida::first();
    expect($partida->cultura_base)->toBe('Cyberpunk')
        ->and($partida->estado_jornada)->toBeJson()
        ->and($partida->users->contains($this->user))->toBeTrue();
});

test('unirse por nombre exacto asocia al jugador con el equipo (HU 1.3)', function () {
    $partida = Partida::factory()->create(['nombre_equipo' => 'Los Testeadores']);

    $response = $this->postJson('/api/partida/join', [
        'nombre_equipo' => 'Los Testeadores'
    ]);

    $response->assertStatus(200);
    expect($this->user->partidas->pluck('id'))->toContain($partida->id);
});

test('la asignación aleatoria busca equipos con menos de 5 miembros (HU 1.4)', function () {
    // Partida Llena (Simulada)
    $partidaLlena = Partida::factory()->hasUsers(5)->create();
    // Partida Con Hueco (Simulada)
    $partidaLibre = Partida::factory()->hasUsers(2)->create();

    $response = $this->postJson('/api/partida/join-random');

    $response->assertStatus(200);
    // El usuario tuvo que caer en la partida con hueco, no en la llena
    expect($partidaLibre->fresh()->users->contains($this->user))->toBeTrue()
        ->and($partidaLlena->fresh()->users->contains($this->user))->toBeFalse();
});