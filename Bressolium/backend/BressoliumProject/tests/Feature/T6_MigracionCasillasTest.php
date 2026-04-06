<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 6 (Raw_Tareas)
// Title: Tile Migrations and Base Dictionary
// ==========================================

test('the tiles table has structure for XY grid and state', function () {
    expect(Schema::hasTable('tiles'))->toBeTrue()
        ->and(Schema::hasColumns('tiles', ['id', 'game_id', 'coord_x', 'coord_y', 'tile_type_id', 'level', 'explored']))->toBeTrue();
});

test('the Base Dictionary seeder inserts the 5 generic types', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesBaseSeeder']);

    $this->assertDatabaseHas('resources', ['name' => 'Forest']);
    $this->assertDatabaseHas('resources', ['name' => 'Quarry']);
    $this->assertDatabaseHas('resources', ['name' => 'River']);
    $this->assertDatabaseHas('resources', ['name' => 'Meadow']);
    $this->assertDatabaseHas('resources', ['name' => 'Mine']);
});