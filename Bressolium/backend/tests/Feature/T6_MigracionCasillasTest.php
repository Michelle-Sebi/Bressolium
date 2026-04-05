<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST PARA: TAREA 6 (Raw_Tareas)
// Título: Migraciones de Casillas y Diccionario Base
// ==========================================

test('la tabla casillas tiene estructura para grid XY y estado', function () {
    expect(Schema::hasTable('casillas'))->toBeTrue()
        ->and(Schema::hasColumns('casillas', ['id', 'partida_id', 'coord_x', 'coord_y', 'tipo_casilla_id', 'nivel', 'explorada']))->toBeTrue();
});

test('el seeder de Diccionario Base inserta los 5 tipos genéricos', function () {
    // Al ejecutar Artisan Seed
    Artisan::call('db:seed', ['--class' => 'RecursosBaseSeeder']);

    // Asumiendo que se creará una tabla o enums para Recursos. Se asume tabla 'recursos' en el diseño.
    $this->assertDatabaseHas('recursos', ['nombre' => 'Bosque']);
    $this->assertDatabaseHas('recursos', ['nombre' => 'Cantera']);
    $this->assertDatabaseHas('recursos', ['nombre' => 'Río']);
    $this->assertDatabaseHas('recursos', ['nombre' => 'Prado']);
    $this->assertDatabaseHas('recursos', ['nombre' => 'Mina']);
});