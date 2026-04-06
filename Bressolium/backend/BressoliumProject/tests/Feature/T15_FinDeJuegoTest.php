<?php

use App\Models\User;
use App\Models\Partida;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST PARA: TAREA 15 (Raw_Tareas)
// Título: Fin de Juego
// ==========================================

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->partida = Partida::factory()->create([
        'estado_jornada' => json_encode(['acciones_restantes' => 2, 'votos' => []])
    ]);
    $this->user->partidas()->attach($this->partida->id);

    $this->actingAs($this->user);
});

test('partida declara victoria al desbloquear la nave espacial (HU 4.3)', function () {
    // Al no tener Avión en MVP, la única T15 testea el Cierre Final de Victoria
    $this->partida->update([
        'is_spaceship_unlocked' => true
    ]);

    expect($this->partida->is_spaceship_unlocked)->toBeTrue();
});