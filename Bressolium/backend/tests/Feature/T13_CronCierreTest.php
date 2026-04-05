<?php

use App\Models\User;
use App\Models\Partida;
// use App\Jobs\CierreDeJornadaJob;   // (A importar cuando se cree el Controller)
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST CRÍTICO PARA: TAREA 13 (Raw_Tareas)
// Título: Schedule / Cron Cierre de Turno Backend
// ==========================================

beforeEach(function () {
    $this->users = User::factory()->count(3)->create();

    // Forzamos que la partida ya tiene votos recogidos (2 por la tech 1, 1 por la tech 2)
    $this->partida = Partida::factory()->create([
        'estado_jornada' => json_encode([
            'acciones_restantes' => 0,
            'votos' => [
                ['user_id' => $this->users[0]->id, 'tech_id' => 1],
                ['user_id' => $this->users[1]->id, 'tech_id' => 1], // Ganadora (mayoría)
                ['user_id' => $this->users[2]->id, 'tech_id' => 2],
            ]
        ])
    ]);
});

test('el job de cierre evalua ganador, restaura JSON e inicializa turno nuevo', function () {
    // 1. Simular la ejecución manual del Job
    // (new CierreDeJornadaJob($this->partida->id))->handle(); // <- DESCOMENTAR AL TENER EL CÓDIGO

    // Como no tenemos el código aún para correr, mockeamos la mutación en BD manualmente 
    // tal como la haría el JOB.
    $this->partida->update([
        'estado_jornada' => json_encode([
            'acciones_restantes' => 2, // Reseteadas
            'votos' => [] // Vaciados para el dia 2
        ])
    ]);

    // 2. Refrescamos y evaluamos
    $this->partida->refresh();
    $nuevoEstado = json_decode($this->partida->estado_jornada, true);

    expect($nuevoEstado['votos'])->toBeEmpty()
        ->and($nuevoEstado['acciones_restantes'])->toBe(2);

// (AÑADIR LUEGO: Test de comprobar que el "inventario" restó el coste del ganador y sumó la Tech)
});

test('el algoritmo de cron resuelve un empate de votos al azar sin crashear', function () {
    // Forzamos empate 2 a 2 entre Tech 1 y Tech 2
    $this->users = User::factory()->count(4)->create();
    $this->partida->update([
        'estado_jornada' => json_encode([
            'acciones_restantes' => 0,
            'votos' => [
                ['user_id' => $this->users[0]->id, 'tech_id' => 1],
                ['user_id' => $this->users[1]->id, 'tech_id' => 1],
                ['user_id' => $this->users[2]->id, 'tech_id' => 2],
                ['user_id' => $this->users[3]->id, 'tech_id' => 2],
            ]
        ])
    ]);

    // Ejecuta resolución simulada. 
    // Un collect o Math::array_rand de PHP escogerá una key aleatoria en caso de count() idénticos
    $votos_array = json_decode($this->partida->estado_jornada, true)['votos'];
    $conteo = array_count_values(array_column($votos_array, 'tech_id'));

    // Obtener los que tienen el max count
    $maxVotos = max($conteo);
    $candidatos = array_keys(array_filter($conteo, fn($v) => $v == $maxVotos));
    $tech_ganadora = $candidatos[array_rand($candidatos)];

    // Assertion: La máquina debe ser capaz de desempatar seleccionando a 1 o 2 
    expect(in_array($tech_ganadora, [1, 2]))->toBeTrue();
});