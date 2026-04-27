<?php

use App\Models\Game;
use App\Models\Invention;
use App\Models\InventionPrerequisite;
use App\Models\Technology;
use App\Models\TechnologyPrerequisite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 48
// Title: [Refactor] DB Migration V6: Quantities in Inventions & Prerequisites
// ==========================================

// ─── Schema: nuevas columnas quantity ─────────────────────────────────────────

test('invention_prerequisites tiene la columna quantity de tipo entero', function () {
    expect(Schema::hasColumn('invention_prerequisites', 'quantity'))->toBeTrue()
        ->and(Schema::getColumnType('invention_prerequisites', 'quantity'))->toBe('int');
});

test('technology_prerequisites tiene la columna quantity de tipo entero', function () {
    expect(Schema::hasColumn('technology_prerequisites', 'quantity'))->toBeTrue()
        ->and(Schema::getColumnType('technology_prerequisites', 'quantity'))->toBe('int');
});

test('game_invention tiene la columna quantity de tipo entero', function () {
    expect(Schema::hasColumn('game_invention', 'quantity'))->toBeTrue()
        ->and(Schema::getColumnType('game_invention', 'quantity'))->toBe('int');
});

// ─── game_invention sigue manteniendo is_active (cambios aditivos) ────────────

test('game_invention mantiene la columna is_active (no se rompen tests existentes)', function () {
    expect(Schema::hasColumn('game_invention', 'is_active'))->toBeTrue();
});

test('game_invention conserva todas sus columnas originales y suma quantity', function () {
    expect(Schema::hasColumns('game_invention', [
        'game_id',
        'invention_id',
        'is_active',
        'quantity',
    ]))->toBeTrue();
});

// ─── Defaults: insert sin quantity aplica el default ──────────────────────────

test('invention_prerequisites.quantity tiene default 1 cuando no se especifica', function () {
    $invention = Invention::create(['name' => 'Cuerda']);
    $other     = Invention::create(['name' => 'Hacha']);

    $prereq = InventionPrerequisite::create([
        'invention_id' => $invention->id,
        'prereq_type'  => 'invention',
        'prereq_id'    => $other->id,
    ]);

    expect($prereq->fresh()->quantity)->toBe(1);
});

test('technology_prerequisites.quantity tiene default 1 cuando no se especifica', function () {
    $tech  = Technology::create(['name' => 'Agricultura']);
    $other = Technology::create(['name' => 'Fuego']);

    $prereq = TechnologyPrerequisite::create([
        'technology_id' => $tech->id,
        'prereq_type'   => 'technology',
        'prereq_id'     => $other->id,
    ]);

    expect($prereq->fresh()->quantity)->toBe(1);
});

test('game_invention.quantity tiene default 0 cuando no se especifica (análogo a game_material)', function () {
    $game      = Game::factory()->create();
    $invention = Invention::create(['name' => 'Cuerda']);

    DB::table('game_invention')->insert([
        'game_id'       => $game->id,
        'invention_id'  => $invention->id,
        'is_active'     => false,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    $row = DB::table('game_invention')
        ->where('game_id', $game->id)
        ->where('invention_id', $invention->id)
        ->first();

    expect((int) $row->quantity)->toBe(0);
});

// ─── Persistencia: el quantity especificado se guarda y se lee ────────────────

test('invention_prerequisites persiste un quantity custom (>1)', function () {
    $invention = Invention::create(['name' => 'Cuerda']);
    $other     = Invention::create(['name' => 'Hacha']);

    $prereq = InventionPrerequisite::create([
        'invention_id' => $invention->id,
        'prereq_type'  => 'invention',
        'prereq_id'    => $other->id,
        'quantity'     => 3,
    ]);

    expect($prereq->fresh()->quantity)->toBe(3);
});

test('technology_prerequisites persiste un quantity custom (>1)', function () {
    $tech  = Technology::create(['name' => 'Metalurgia']);
    $other = Technology::create(['name' => 'Rueda']);

    $prereq = TechnologyPrerequisite::create([
        'technology_id' => $tech->id,
        'prereq_type'   => 'technology',
        'prereq_id'     => $other->id,
        'quantity'      => 5,
    ]);

    expect($prereq->fresh()->quantity)->toBe(5);
});

// ─── Eloquent: $fillable incluye quantity ─────────────────────────────────────

test('InventionPrerequisite tiene quantity en su $fillable', function () {
    expect((new InventionPrerequisite())->getFillable())->toContain('quantity');
});

test('TechnologyPrerequisite tiene quantity en su $fillable', function () {
    expect((new TechnologyPrerequisite())->getFillable())->toContain('quantity');
});

// ─── Eloquent: Game::inventions() expone quantity en el pivot ─────────────────

test('Game::inventions() expone quantity en el pivot junto a is_active', function () {
    $game      = Game::factory()->create();
    $invention = Invention::create(['name' => 'Carreta']);

    $game->inventions()->attach($invention->id, [
        'is_active' => true,
        'quantity'  => 4,
    ]);

    $loaded = $game->fresh()->inventions->first();

    expect($loaded->pivot->quantity)->toBe(4)
        ->and((int) $loaded->pivot->is_active)->toBe(1);
});

test('Game::inventions() permite leer un equipo con quantity 0 (sin construir)', function () {
    $game      = Game::factory()->create();
    $invention = Invention::create(['name' => 'Trampa']);

    $game->inventions()->attach($invention->id, [
        'is_active' => false,
        'quantity'  => 0,
    ]);

    $loaded = $game->fresh()->inventions->first();

    expect($loaded->pivot->quantity)->toBe(0)
        ->and((int) $loaded->pivot->is_active)->toBe(0);
});

// ─── Cambios aditivos: assertion explícita de retrocompatibilidad ─────────────

test('invention_prerequisites mantiene sus columnas originales (cambios aditivos)', function () {
    expect(Schema::hasColumns('invention_prerequisites', [
        'invention_id',
        'prereq_type',
        'prereq_id',
        'quantity',
    ]))->toBeTrue();
});

test('technology_prerequisites mantiene sus columnas originales (cambios aditivos)', function () {
    expect(Schema::hasColumns('technology_prerequisites', [
        'technology_id',
        'prereq_type',
        'prereq_id',
        'quantity',
    ]))->toBeTrue();
});
