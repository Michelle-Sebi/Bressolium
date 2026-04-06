<?php

use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Partida;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST PARA: TAREA 1 (Raw_Tareas)
// Título: Migraciones y Modelos base de Usuarios y Partidas
// Requisitos documentados en: epicas-e-historias-de-usuario.md (Épica 1)
// ==========================================

test('la tabla users contiene las columnas base', function () {
    expect(Schema::hasTable('users'))->toBeTrue()
        ->and(Schema::hasColumns('users', ['id', 'name', 'email', 'password']))->toBeTrue();
});

test('la tabla partidas contiene nombre y estado', function () {
    expect(Schema::hasTable('partidas'))->toBeTrue()
        ->and(Schema::hasColumns('partidas', ['id', 'nombre', 'estado', 'created_at']))->toBeTrue();
});

test('un usuario puede pertenecer a muchas partidas (Relación N:M/1:N Pivote)', function () {
    $user = User::factory()->create();
    $partida = Partida::factory()->create();

    // Asumiendo tabla pivote partida_user
    $user->partidas()->attach($partida->id);

    expect($user->partidas->count())->toBe(1)
        ->and($user->partidas->first()->id)->toBe($partida->id);
});