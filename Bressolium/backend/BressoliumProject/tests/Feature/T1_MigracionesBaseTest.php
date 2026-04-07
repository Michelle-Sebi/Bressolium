<?php

use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Game;
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

test('la tabla games contiene name y status', function () {
    expect(Schema::hasTable('games'))->toBeTrue()
        ->and(Schema::hasColumns('games', ['id', 'name', 'status', 'created_at']))->toBeTrue();
});

test('un usuario puede pertenecer a muchas partidas (Relación N:M/1:N Pivote)', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    // Asumiendo tabla pivote game_user
    $user->games()->attach($game->id);

    expect($user->games->count())->toBe(1)
        ->and($user->games->first()->id)->toBe($game->id);
});