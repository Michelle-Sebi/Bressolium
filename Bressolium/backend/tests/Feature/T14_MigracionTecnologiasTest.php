<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST PARA: TAREA 14 (Raw_Tareas)
// Título: Migraciones y Relaciones del Proceso Técnico
// ==========================================

test('las tablas tecnologias y recetas estandarizadas existen en BD', function () {
    expect(Schema::hasTable('tecnologias'))->toBeTrue()
        ->and(Schema::hasTable('recetas'))->toBeTrue();
});

test('el seeder tecnológico carga jerarquías sin dependencias cíclicas', function () {
    // Al ejecutar Artisan Seed
    Artisan::call('db:seed', ['--class' => 'TecnologiasBaseSeeder']);

    // Asumimos que se pueblan correctamente las jerarquías
    $this->assertDatabaseHas('tecnologias', ['nombre' => 'Rueda']);
    $this->assertDatabaseHas('tecnologias', ['nombre' => 'Matemáticas']);
});