<?php

use App\Models\Game;
use App\Models\Invention;
use App\Models\Material;
use App\Models\Technology;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 14 (Raw_Tareas)
// Title: Migrations and Relations for the Tech Process
// ==========================================

test('las tablas de tecnologia, inventos y materiales existen en la BD', function () {
    expect(Schema::hasTable('technologies'))->toBeTrue()
        ->and(Schema::hasTable('inventions'))->toBeTrue()
        ->and(Schema::hasTable('materials'))->toBeTrue()
        ->and(Schema::hasTable('recipes'))->toBeTrue();
});

test('existen las tablas de progreso por partida (inventario y logros)', function () {
    expect(Schema::hasTable('game_material'))->toBeTrue()
        ->and(Schema::hasTable('game_technology'))->toBeTrue()
        ->and(Schema::hasTable('game_invention'))->toBeTrue();
});

test('el seeder tecnologico carga jerarquias y materiales base', function () {
    Artisan::call('db:seed', ['--class' => 'TechInventionsSeeder']);

    $this->assertDatabaseHas('technologies', ['name' => 'Wheel']);
    $this->assertDatabaseHas('inventions', ['name' => 'Cart']);
    $this->assertDatabaseHas('materials', ['name' => 'Wood']);
});

test('los modelos Technology e Invention implementan sus relaciones derivadas de ER_v4', function () {
    // Crear una tech base
    $techBase = Technology::create(['name' => 'Tech Base']);

    // Crear una tech que requiere la base
    $techAvanzada = Technology::create([
        'name' => 'Tech Avanzada',
        'prerequisite_id' => $techBase->id,
    ]);

    // Relación Technology -> Prerequisite
    expect($techAvanzada->prerequisite->id)->toBe($techBase->id);

    // Crear un invento hijo de la tech avanzada
    $invento = Invention::create([
        'name' => 'Invento Práctico',
        'technology_id' => $techAvanzada->id,
    ]);

    // Relación Technology -> Inventions y Invention -> Technology
    expect($techAvanzada->inventions->first()->id)->toBe($invento->id)
        ->and($invento->technology->id)->toBe($techAvanzada->id);
});

test('el modelo Recipe implementa la relacion polimorfica para Invention y Technology segun ER_v4', function () {
    $material = Material::create(['name' => 'Stone']);

    $tech = Technology::create(['name' => 'Stone Working']);
    $invento = Invention::create(['name' => 'Stone Axe']);

    // Asignar receta a Technology
    $tech->recipes()->create([
        'material_id' => $material->id,
        'quantity' => 10,
    ]);

    // Asignar receta a Invention
    $invento->recipes()->create([
        'material_id' => $material->id,
        'quantity' => 5,
    ]);

    expect($tech->recipes)->toHaveCount(1)
        ->and($tech->recipes->first()->material->name)->toBe('Stone')
        ->and($tech->recipes->first()->quantity)->toBe(10)
        ->and($invento->recipes)->toHaveCount(1)
        ->and($invento->recipes->first()->material->name)->toBe('Stone')
        ->and($invento->recipes->first()->quantity)->toBe(5);
});

test('el modelo Game gestiona inventario material y progreso de tech e inventos como estipula ER_v4', function () {
    $game = Game::create(['name' => 'Test Game']);

    $material = Material::create(['name' => 'Iron']);
    $tech = Technology::create(['name' => 'Iron Smelting']);
    $invento = Invention::create(['name' => 'Iron Sword']);

    // Attachments a los pivotes
    $game->materials()->attach($material->id, ['quantity' => 100]);
    $game->technologies()->attach($tech->id, ['is_active' => true]);
    $game->inventions()->attach($invento->id, ['is_active' => false]);

    // Rehidratar game y validar pivotes
    $game = $game->fresh();

    expect($game->materials)->toHaveCount(1)
        ->and($game->materials->first()->pivot->quantity)->toBe(100)
        ->and($game->technologies)->toHaveCount(1)
        ->and($game->technologies->first()->pivot->is_active)->toBe(1) // El true/1 de sqlite o mysql
        ->and($game->inventions)->toHaveCount(1)
        ->and($game->inventions->first()->pivot->is_active)->toBe(0);
});
