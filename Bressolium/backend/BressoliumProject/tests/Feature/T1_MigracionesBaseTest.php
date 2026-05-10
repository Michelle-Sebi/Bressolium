<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

test('la tabla games contiene status WAITING por defecto', function () {
    $game = Game::factory()->create();
    expect($game->status)->toBe('WAITING');
});

test('la tabla rounds contiene las columnas de tiempo y numero', function () {
    expect(Schema::hasTable('rounds'))->toBeTrue()
        ->and(Schema::hasColumns('rounds', ['id', 'game_id', 'number', 'start_date', 'ended_at']))->toBeTrue();
});

test('la tabla round_user gestiona las acciones gastadas', function () {
    expect(Schema::hasTable('round_user'))->toBeTrue()
        ->and(Schema::hasColumns('round_user', ['round_id', 'user_id', 'actions_spent']))->toBeTrue();
});

test('la tabla votes permite votar por tecnologia o invento', function () {
    expect(Schema::hasTable('votes'))->toBeTrue()
        ->and(Schema::hasColumns('votes', ['id', 'round_id', 'user_id', 'technology_id', 'invention_id']))->toBeTrue();
});

test('un usuario puede estar marcado como AFK en una partida', function () {
    expect(Schema::hasTable('game_user'))->toBeTrue()
        ->and(Schema::hasColumn('game_user', 'is_afk'))->toBeTrue();
});

test('todas las claves primarias son UUID', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    expect(Str::isUuid($user->id))->toBeTrue()
        ->and(Str::isUuid($game->id))->toBeTrue();
});
