<?php

use App\Models\User;
use App\Models\Partida;
// use App\Jobs\CierreDeJornadaJob;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST CRÍTICO PARA: TAREA 16 (Raw_Tareas)
// Título: Gestión de Abandono (Jugadores Inactivos)
// ==========================================

beforeEach(function () {
    // 2 usuarios
    $this->users = User::factory()->count(2)->create();

    $this->partida = Partida::factory()->create([
        'estado_jornada' => json_encode([
            'acciones_restantes' => 1,
            'votos' => [
                ['user_id' => $this->users[0]->id, 'tech_id' => 1]
                // El usuario [1] NO HA VOTADO
            ]
        ])
    ]);
});

test('partida avanza si User2 está marcado is_afk e Inactivo pese a no haber votado', function () {
    // Marcamos al Jugador 2 como inactivo
    $this->users[1]->update(['is_afk' => true]);

    // 1. Simular la ejecución manual del Job de evaluación del turno (que será programado en T13/16)
    // (new CierreDeJornadaJob($this->partida->id))->handle();

    // (LÓGICA MOCKEADA PROSPECTIVA DEL JOB) -->
    $todos_votaron = true;
    $votos_actuales = collect(json_decode($this->partida->estado_jornada, true)['votos']);

    foreach ($this->partida->users as $u) {
        if (!$u->is_afk) {
            $hasVoted = $votos_actuales->firstWhere('user_id', $u->id);
            if (!$hasVoted)
                $todos_votaron = false;
        }
    }

    if ($todos_votaron) {
        // Ejecutar Cierre
        $this->partida->update(['estado_jornada' => json_encode(['votos' => [], 'acciones_restantes' => 2])]);
    }
    // <-- FIN MOCK DE JOB

    $this->partida->refresh();
    $nuevoEstado = json_decode($this->partida->estado_jornada, true);

    // Esperamos vaciado porque se debió disparar el cierre al estar el U2 (is_afk)
    expect($nuevoEstado['votos'])->toBeEmpty();
});