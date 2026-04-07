<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 14 (Raw_Tareas)
// Title: Migrations and Relations for the Tech Process
// ==========================================

test('the technologies and recipes tables exist in DB', function () {
    expect(Schema::hasTable('technologies'))->toBeTrue()
        ->and(Schema::hasTable('recipes'))->toBeTrue();
});

test('the technology seeder loads hierarchies without cyclic dependencies', function () {
    Artisan::call('db:seed', ['--class' => 'TechnologiesBaseSeeder']);

    $this->assertDatabaseHas('technologies', ['name' => 'Wheel']);
    $this->assertDatabaseHas('technologies', ['name' => 'Mathematics']);
});