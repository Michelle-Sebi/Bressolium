<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Technology;
use App\Models\Invention;
use App\Models\Material;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 22 (Raw_Tareas)
// Title: DB Migration V5b: Tech Tree Normalization
// ==========================================

test('existen las tablas de prerequisitos separadas para tecnologías e inventos', function () {
    expect(Schema::hasTable('technology_prerequisites'))->toBeTrue()
        ->and(Schema::hasTable('invention_prerequisites'))->toBeTrue();
});

test('technology_prerequisites tiene las columnas de relación polimórfica', function () {
    expect(Schema::hasColumns('technology_prerequisites', [
        'technology_id',
        'prereq_type',
        'prereq_id',
    ]))->toBeTrue();
});

test('invention_prerequisites tiene las columnas de relación polimórfica', function () {
    expect(Schema::hasColumns('invention_prerequisites', [
        'invention_id',
        'prereq_type',
        'prereq_id',
    ]))->toBeTrue();
});

test('existe la tabla invention_costs para separar costes de recursos de los prerequisitos', function () {
    expect(Schema::hasTable('invention_costs'))->toBeTrue()
        ->and(Schema::hasColumns('invention_costs', ['invention_id', 'resource_id', 'quantity']))->toBeTrue();
});

test('existen las tablas de bonificadores para tecnologías e inventos', function () {
    expect(Schema::hasTable('technology_bonuses'))->toBeTrue()
        ->and(Schema::hasTable('invention_bonuses'))->toBeTrue();
});

test('technology_bonuses tiene las columnas de tipo, valor y objetivo del bonificador', function () {
    expect(Schema::hasColumns('technology_bonuses', [
        'technology_id',
        'bonus_type',
        'bonus_value',
        'bonus_target',
    ]))->toBeTrue();
});

test('existen las tablas de desbloqueos para tecnologías e inventos', function () {
    expect(Schema::hasTable('technology_unlocks'))->toBeTrue()
        ->and(Schema::hasTable('invention_unlocks'))->toBeTrue();
});

test('technology_unlocks y invention_unlocks tienen el campo unlock_type', function () {
    expect(Schema::hasColumn('technology_unlocks', 'unlock_type'))->toBeTrue()
        ->and(Schema::hasColumn('invention_unlocks', 'unlock_type'))->toBeTrue();
});

test('un invento puede tener otro invento como prerequisito (no se consume)', function () {
    $cuerda  = Invention::create(['name' => 'Cuerda']);
    $trampa  = Invention::create(['name' => 'Trampa']);

    // Trampa requiere Cuerda como prerequisito (no se consume)
    $trampa->inventionPrerequisites()->create([
        'prereq_type' => 'invention',
        'prereq_id'   => $cuerda->id,
    ]);

    $prereqs = $trampa->inventionPrerequisites;
    expect($prereqs)->toHaveCount(1)
        ->and($prereqs->first()->prereq_type)->toBe('invention')
        ->and($prereqs->first()->prereq_id)->toBe($cuerda->id);
});

test('un invento puede tener una tecnología como prerequisito', function () {
    $agriculture = Technology::create(['name' => 'Agricultura']);
    $arado       = Invention::create(['name' => 'Arado']);

    $arado->inventionPrerequisites()->create([
        'prereq_type' => 'technology',
        'prereq_id'   => $agriculture->id,
    ]);

    $prereqs = $arado->inventionPrerequisites;
    expect($prereqs)->toHaveCount(1)
        ->and($prereqs->first()->prereq_type)->toBe('technology')
        ->and($prereqs->first()->prereq_id)->toBe($agriculture->id);
});

test('invention_costs almacena los recursos que se consumen al construir el invento', function () {
    $roble  = Material::create(['name' => 'Roble', 'tier' => 0, 'group' => 'Bosque']);
    $hierro = Material::create(['name' => 'Hierro', 'tier' => 0, 'group' => 'Mina']);
    $hacha  = Invention::create(['name' => 'Hacha']);

    $hacha->inventionCosts()->createMany([
        ['resource_id' => $roble->id,  'quantity' => 10],
        ['resource_id' => $hierro->id, 'quantity' => 5],
    ]);

    expect($hacha->inventionCosts)->toHaveCount(2);
    $this->assertDatabaseHas('invention_costs', ['invention_id' => $hacha->id, 'quantity' => 10]);
    $this->assertDatabaseHas('invention_costs', ['invention_id' => $hacha->id, 'quantity' => 5]);
});

test('prerequisito e invention_cost son independientes: el prerequisito no se consume', function () {
    $cuerda = Invention::create(['name' => 'Cuerda']);
    $roble  = Material::create(['name' => 'Roble', 'tier' => 0, 'group' => 'Bosque']);
    $barco  = Invention::create(['name' => 'Barco']);

    // Cuerda es prerequisito de Barco (debe existir, no se consume)
    $barco->inventionPrerequisites()->create([
        'prereq_type' => 'invention',
        'prereq_id'   => $cuerda->id,
    ]);

    // Roble es coste de Barco (se consume)
    $barco->inventionCosts()->create(['resource_id' => $roble->id, 'quantity' => 20]);

    expect($barco->inventionPrerequisites)->toHaveCount(1)
        ->and($barco->inventionCosts)->toHaveCount(1);
});

test('una tecnología puede registrar desbloqueos de tipo technology, invention o tile_level', function () {
    $tech = Technology::create(['name' => 'Metalurgia y Aleaciones']);

    $tech->technologyUnlocks()->createMany([
        ['unlock_type' => 'technology', 'unlock_id' => null],
        ['unlock_type' => 'invention',  'unlock_id' => null],
        ['unlock_type' => 'tile_level', 'unlock_id' => null],
    ]);

    expect($tech->technologyUnlocks)->toHaveCount(3);
});

test('los tests de T14 no se rompen: recipes sigue existiendo tras la migración V5b', function () {
    expect(Schema::hasTable('recipes'))->toBeTrue();
});
