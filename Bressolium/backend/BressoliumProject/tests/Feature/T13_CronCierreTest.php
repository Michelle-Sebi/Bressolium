<?php

use App\Jobs\CloseRoundJob;
use App\Models\Game;
use App\Models\Invention;
use App\Models\InventionCost;
use App\Models\InventionPrerequisite;
use App\Models\Material;
use App\Models\Technology;
use App\Models\Tile;
use App\Models\TileType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// CRITICAL TEST FOR: TASK 13 (Raw_Tareas)
// Title: Schedule / Cron Round Close Backend
// ==========================================

beforeEach(function () {
    $this->users = User::factory()->count(3)->create();
    $this->game = Game::factory()->create();
    $this->round = $this->game->rounds()->create(['number' => 1]);

    foreach ($this->users as $user) {
        $this->game->users()->attach($user->id);
        $this->round->users()->attach($user->id, ['actions_spent' => 2]);
    }
});

// ---
// DoD: La clase CloseRoundJob existe
// ---

test('la clase CloseRoundJob existe en el namespace App\\Jobs', function () {
    expect(class_exists(CloseRoundJob::class))->toBeTrue();
});

// ---
// DoD: Resolución del ganador de votos de tecnología
// ---

test('la tecnología más votada se activa en game_technology al cerrar jornada', function () {
    $winner = Technology::factory()->create(['name' => 'Printing Press']);
    $loser = Technology::factory()->create(['name' => 'Windmill']);

    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'technology_id' => $winner->id]);
    $this->round->votes()->create(['user_id' => $this->users[1]->id, 'technology_id' => $winner->id]);
    $this->round->votes()->create(['user_id' => $this->users[2]->id, 'technology_id' => $loser->id]);

    CloseRoundJob::dispatchSync($this->game->id);

    $this->assertDatabaseHas('game_technology', [
        'game_id' => $this->game->id,
        'technology_id' => $winner->id,
        'is_active' => true,
    ]);

    $this->assertDatabaseMissing('game_technology', [
        'game_id' => $this->game->id,
        'technology_id' => $loser->id,
    ]);
});

// ---
// DoD: Resolución del ganador de votos de invento + incremento de quantity
// ---

test('el invento más votado incrementa su quantity en game_invention cuando hay recursos suficientes', function () {
    $wood = Material::factory()->create(['name' => 'Wood']);
    $invention = Invention::factory()->create(['name' => 'Wheel']);

    InventionCost::create(['invention_id' => $invention->id, 'resource_id' => $wood->id, 'quantity' => 3]);

    $this->game->materials()->attach($wood->id, ['quantity' => 5]);
    $this->game->inventions()->attach($invention->id, ['is_active' => false, 'quantity' => 0]);

    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'invention_id' => $invention->id]);
    $this->round->votes()->create(['user_id' => $this->users[1]->id, 'invention_id' => $invention->id]);
    $this->round->votes()->create(['user_id' => $this->users[2]->id, 'invention_id' => null]);

    CloseRoundJob::dispatchSync($this->game->id);

    $this->assertDatabaseHas('game_invention', [
        'game_id' => $this->game->id,
        'invention_id' => $invention->id,
        'quantity' => 1,
    ]);
});

test('construir el invento por segunda vez incrementa quantity a 2', function () {
    $wood = Material::factory()->create(['name' => 'Wood']);
    $invention = Invention::factory()->create(['name' => 'Wheel']);

    InventionCost::create(['invention_id' => $invention->id, 'resource_id' => $wood->id, 'quantity' => 2]);

    $this->game->materials()->attach($wood->id, ['quantity' => 10]);
    // El equipo ya tiene 1 unidad construida
    $this->game->inventions()->attach($invention->id, ['is_active' => true, 'quantity' => 1]);

    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'invention_id' => $invention->id]);
    $this->round->votes()->create(['user_id' => $this->users[1]->id, 'invention_id' => $invention->id]);
    $this->round->votes()->create(['user_id' => $this->users[2]->id, 'invention_id' => null]);

    CloseRoundJob::dispatchSync($this->game->id);

    $this->assertDatabaseHas('game_invention', [
        'game_id' => $this->game->id,
        'invention_id' => $invention->id,
        'quantity' => 2,
    ]);
});

// ---
// DoD: Aplica costes de materiales al construir un invento
// ---

test('los costes en materiales del invento ganador se descuentan del inventario del equipo', function () {
    $iron = Material::factory()->create(['name' => 'Iron']);
    $wood = Material::factory()->create(['name' => 'Wood']);
    $invention = Invention::factory()->create(['name' => 'Sword']);

    InventionCost::create(['invention_id' => $invention->id, 'resource_id' => $iron->id, 'quantity' => 2]);
    InventionCost::create(['invention_id' => $invention->id, 'resource_id' => $wood->id, 'quantity' => 1]);

    $this->game->materials()->attach($iron->id, ['quantity' => 5]);
    $this->game->materials()->attach($wood->id, ['quantity' => 3]);
    $this->game->inventions()->attach($invention->id, ['is_active' => false, 'quantity' => 0]);

    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'invention_id' => $invention->id]);
    $this->round->votes()->create(['user_id' => $this->users[1]->id, 'invention_id' => $invention->id]);
    $this->round->votes()->create(['user_id' => $this->users[2]->id, 'invention_id' => null]);

    CloseRoundJob::dispatchSync($this->game->id);

    // Hierro: 5 - 2 = 3
    $this->assertDatabaseHas('game_material', [
        'game_id' => $this->game->id,
        'material_id' => $iron->id,
        'quantity' => 3,
    ]);

    // Madera: 3 - 1 = 2
    $this->assertDatabaseHas('game_material', [
        'game_id' => $this->game->id,
        'material_id' => $wood->id,
        'quantity' => 2,
    ]);
});

// ---
// DoD: El invento NO se construye si recursos insuficientes
// ---

test('el invento no se construye si los materiales son insuficientes', function () {
    $iron = Material::factory()->create(['name' => 'Iron']);
    $invention = Invention::factory()->create(['name' => 'Cannon']);

    InventionCost::create(['invention_id' => $invention->id, 'resource_id' => $iron->id, 'quantity' => 10]);

    $this->game->materials()->attach($iron->id, ['quantity' => 3]);
    $this->game->inventions()->attach($invention->id, ['is_active' => false, 'quantity' => 0]);

    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'invention_id' => $invention->id]);
    $this->round->votes()->create(['user_id' => $this->users[1]->id, 'invention_id' => $invention->id]);
    $this->round->votes()->create(['user_id' => $this->users[2]->id, 'invention_id' => null]);

    CloseRoundJob::dispatchSync($this->game->id);

    $this->assertDatabaseHas('game_invention', [
        'game_id' => $this->game->id,
        'invention_id' => $invention->id,
        'quantity' => 0,
    ]);

    // Los materiales NO se descuentan
    $this->assertDatabaseHas('game_material', [
        'game_id' => $this->game->id,
        'material_id' => $iron->id,
        'quantity' => 3,
    ]);
});

// ---
// DoD: Suma producción de materiales según casillas exploradas
// ---

test('se suma la producción de materiales según las casillas exploradas del equipo', function () {
    $wood = Material::factory()->create(['name' => 'Wood']);
    $forest = TileType::factory()->create(['name' => 'Forest L1', 'level' => 1]);
    $forest->materials()->attach($wood->id, ['quantity' => 2]);

    Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $forest->id,
        'explored' => true,
    ]);

    $this->game->materials()->attach($wood->id, ['quantity' => 1]);

    CloseRoundJob::dispatchSync($this->game->id);

    // 1 (existente) + 2 (producción de casilla) = 3
    $this->assertDatabaseHas('game_material', [
        'game_id' => $this->game->id,
        'material_id' => $wood->id,
        'quantity' => 3,
    ]);
});

test('las casillas no exploradas no producen materiales', function () {
    $wood = Material::factory()->create(['name' => 'Wood']);
    $forest = TileType::factory()->create(['name' => 'Forest L1', 'level' => 1]);
    $forest->materials()->attach($wood->id, ['quantity' => 2]);

    Tile::factory()->create([
        'game_id' => $this->game->id,
        'tile_type_id' => $forest->id,
        'explored' => false,
    ]);

    $this->game->materials()->attach($wood->id, ['quantity' => 1]);

    CloseRoundJob::dispatchSync($this->game->id);

    // No producción: sigue en 1
    $this->assertDatabaseHas('game_material', [
        'game_id' => $this->game->id,
        'material_id' => $wood->id,
        'quantity' => 1,
    ]);
});

// ---
// DoD: Crear nueva jornada con número incrementado
// ---

test('se crea una nueva jornada con número incrementado al cerrar la ronda', function () {
    CloseRoundJob::dispatchSync($this->game->id);

    expect($this->game->rounds()->count())->toBe(2);

    $this->assertDatabaseHas('rounds', [
        'game_id' => $this->game->id,
        'number' => 2,
    ]);
});

// ---
// DoD: Resetear actions_spent en round_user para todos los jugadores
// ---

test('se crea la nueva jornada con actions_spent a 0 para todos los jugadores del equipo', function () {
    CloseRoundJob::dispatchSync($this->game->id);

    $newRound = $this->game->rounds()->where('number', 2)->first();

    foreach ($this->users as $user) {
        $this->assertDatabaseHas('round_user', [
            'round_id' => $newRound->id,
            'user_id' => $user->id,
            'actions_spent' => 0,
        ]);
    }
});

// ---
// DoD: Validación de prerrequisitos por cantidad acumulada, no solo presencia
// ---

test('el invento no se construye si no se cumplen los prerrequisitos de cantidad', function () {
    $iron = Material::factory()->create(['name' => 'Iron']);
    $prereqInvention = Invention::factory()->create(['name' => 'Forge']);
    $mainInvention = Invention::factory()->create(['name' => 'Cannon']);

    // Cannon requiere 2 unidades de Forge como prerrequisito
    InventionPrerequisite::create([
        'invention_id' => $mainInvention->id,
        'prereq_type' => 'invention',
        'prereq_id' => $prereqInvention->id,
        'quantity' => 2,
    ]);

    InventionCost::create(['invention_id' => $mainInvention->id, 'resource_id' => $iron->id, 'quantity' => 1]);

    $this->game->materials()->attach($iron->id, ['quantity' => 10]);
    // El equipo tiene solo 1 Forge, pero necesita 2
    $this->game->inventions()->attach($prereqInvention->id, ['is_active' => true, 'quantity' => 1]);
    $this->game->inventions()->attach($mainInvention->id, ['is_active' => false, 'quantity' => 0]);

    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'invention_id' => $mainInvention->id]);
    $this->round->votes()->create(['user_id' => $this->users[1]->id, 'invention_id' => $mainInvention->id]);
    $this->round->votes()->create(['user_id' => $this->users[2]->id, 'invention_id' => null]);

    CloseRoundJob::dispatchSync($this->game->id);

    // No se construye porque falta cantidad de prerrequisito
    $this->assertDatabaseHas('game_invention', [
        'game_id' => $this->game->id,
        'invention_id' => $mainInvention->id,
        'quantity' => 0,
    ]);
});

test('el invento se construye cuando los prerrequisitos de cantidad se cumplen exactamente', function () {
    $iron = Material::factory()->create(['name' => 'Iron']);
    $prereqInvention = Invention::factory()->create(['name' => 'Forge']);
    $mainInvention = Invention::factory()->create(['name' => 'Cannon']);

    InventionPrerequisite::create([
        'invention_id' => $mainInvention->id,
        'prereq_type' => 'invention',
        'prereq_id' => $prereqInvention->id,
        'quantity' => 2,
    ]);

    InventionCost::create(['invention_id' => $mainInvention->id, 'resource_id' => $iron->id, 'quantity' => 1]);

    $this->game->materials()->attach($iron->id, ['quantity' => 10]);
    // El equipo tiene exactamente 2 Forge (cumple el prerrequisito)
    $this->game->inventions()->attach($prereqInvention->id, ['is_active' => true, 'quantity' => 2]);
    $this->game->inventions()->attach($mainInvention->id, ['is_active' => false, 'quantity' => 0]);

    $this->round->votes()->create(['user_id' => $this->users[0]->id, 'invention_id' => $mainInvention->id]);
    $this->round->votes()->create(['user_id' => $this->users[1]->id, 'invention_id' => $mainInvention->id]);
    $this->round->votes()->create(['user_id' => $this->users[2]->id, 'invention_id' => null]);

    CloseRoundJob::dispatchSync($this->game->id);

    $this->assertDatabaseHas('game_invention', [
        'game_id' => $this->game->id,
        'invention_id' => $mainInvention->id,
        'quantity' => 1,
    ]);
});

// ---
// DoD: Pueden ganar tecnología e invento en la misma jornada
// ---

test('pueden ganar una tecnología y un invento en la misma jornada si hay recursos suficientes', function () {
    $iron = Material::factory()->create(['name' => 'Iron']);
    $tech = Technology::factory()->create(['name' => 'Metallurgy']);
    $invention = Invention::factory()->create(['name' => 'Sword']);

    InventionCost::create(['invention_id' => $invention->id, 'resource_id' => $iron->id, 'quantity' => 2]);

    $this->game->materials()->attach($iron->id, ['quantity' => 5]);
    $this->game->inventions()->attach($invention->id, ['is_active' => false, 'quantity' => 0]);

    // Los mismos usuarios votan tanto tecnología como invento
    $this->round->votes()->create([
        'user_id' => $this->users[0]->id,
        'technology_id' => $tech->id,
        'invention_id' => $invention->id,
    ]);
    $this->round->votes()->create([
        'user_id' => $this->users[1]->id,
        'technology_id' => $tech->id,
        'invention_id' => $invention->id,
    ]);
    $this->round->votes()->create([
        'user_id' => $this->users[2]->id,
        'technology_id' => null,
        'invention_id' => null,
    ]);

    CloseRoundJob::dispatchSync($this->game->id);

    // La tecnología se activa
    $this->assertDatabaseHas('game_technology', [
        'game_id' => $this->game->id,
        'technology_id' => $tech->id,
        'is_active' => true,
    ]);

    // El invento se construye (quantity incrementado)
    $this->assertDatabaseHas('game_invention', [
        'game_id' => $this->game->id,
        'invention_id' => $invention->id,
        'quantity' => 1,
    ]);
});
