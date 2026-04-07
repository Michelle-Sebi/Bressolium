<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

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