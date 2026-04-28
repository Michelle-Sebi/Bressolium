<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use App\Models\Material;
use App\Models\Technology;
use App\Models\Invention;
use App\Models\InventionPrerequisite;
use App\Models\TechnologyPrerequisite;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 38
// Title: [Feat] Actualización de Seeders (Nuevos Items + Quantities)
// ==========================================

// ─── 1. ResourcesSeeder — sin caolinita ni peces (ya eliminados en T23) ───────

test('ResourcesSeeder no incluye caolinita ni peces tras la actualización', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);

    $this->assertDatabaseMissing('materials', ['name' => 'caolinita']);
    $this->assertDatabaseMissing('materials', ['name' => 'peces']);
});

// ─── 2. ResourcesSeeder — total de recursos cargados ─────────────────────────

test('ResourcesSeeder carga al menos los recursos definidos en casillas.md', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);

    // Bosque (7 recursos: 4 tier-0 + 3 tier-2)
    foreach (['roble', 'pino', 'carbon-natural', 'pieles'] as $name) {
        $this->assertDatabaseHas('materials', ['name' => $name, 'group' => 'bosque', 'tier' => 0]);
    }
    foreach (['latex', 'resinas-inflamables', 'mat-aisl-nat'] as $name) {
        $this->assertDatabaseHas('materials', ['name' => $name, 'group' => 'bosque', 'tier' => 2]);
    }

    // Cantera (8 recursos: 3 tier-0 + 5 tier-2)
    foreach (['silex', 'granito', 'obsidiana'] as $name) {
        $this->assertDatabaseHas('materials', ['name' => $name, 'group' => 'cantera', 'tier' => 0]);
    }
    foreach (['arena-de-silice', 'arena-de-cuarzo', 'cristales-nat', 'silicio', 'min-semi'] as $name) {
        $this->assertDatabaseHas('materials', ['name' => $name, 'group' => 'cantera', 'tier' => 2]);
    }

    // Río (5 recursos: 3 tier-0 + 2 tier-2)
    foreach (['agua', 'cana-comun', 'tierras-fertiles'] as $name) {
        $this->assertDatabaseHas('materials', ['name' => $name, 'group' => 'rio', 'tier' => 0]);
    }
    foreach (['hidrogeno', 'gases-naturales'] as $name) {
        $this->assertDatabaseHas('materials', ['name' => $name, 'group' => 'rio', 'tier' => 2]);
    }

    // Prado (4 recursos tier-0)
    foreach (['lino', 'yute', 'canamo', 'lana'] as $name) {
        $this->assertDatabaseHas('materials', ['name' => $name, 'group' => 'prado', 'tier' => 0]);
    }

    // Mina (6 recursos: 4 tier-0 + 2 tier-2)
    foreach (['cobre', 'hierro', 'estano', 'grafito'] as $name) {
        $this->assertDatabaseHas('materials', ['name' => $name, 'group' => 'mina', 'tier' => 0]);
    }
    foreach (['oro', 'mat-mag-nat'] as $name) {
        $this->assertDatabaseHas('materials', ['name' => $name, 'group' => 'mina', 'tier' => 2]);
    }
});

// ─── 3. TechnologiesSeeder — 31 tecnologías ──────────────────────────────────

test('TechnologiesSeeder carga exactamente 26 tecnologías', function () {
    Artisan::call('db:seed', ['--class' => 'TechnologiesSeeder']);

    expect(Technology::count())->toBe(26);
});

test('TechnologiesSeeder incluye las 26 tecnologías del árbol según evolucion-tecnologias', function () {
    Artisan::call('db:seed', ['--class' => 'TechnologiesSeeder']);

    $expectedTechs = [
        'Herramientas de Piedra', 'Control del Fuego', 'Ganadería',
        'Cerámica y Alfarería', 'Tejido', 'Agricultura', 'Fermentación',
        'Metalurgia y Aleaciones', 'Conservación de Alimentos', 'Química',
        'Escritura', 'Fotografía', 'Electricidad', 'Computación',
        'Comunicaciones Inalámbricas', 'GPS', 'Internet',
        'Inteligencia Artificial', 'Energías Renovables', 'Robótica',
        'Nanotecnología', 'Edición Genética', 'Biotecnología',
        'Sistemas Autónomos', 'Tecnología Espacial', 'Terraformación',
    ];

    foreach ($expectedTechs as $name) {
        expect(Technology::where('name', $name)->exists())
            ->toBeTrue("Falta la tecnología: {$name}");
    }
});

// ─── 4. TechnologiesSeeder — quantity en technology_prerequisites (T48) ───────

test('TechnologiesSeeder persiste quantity en technology_prerequisites', function () {
    Artisan::call('db:seed', ['--class' => 'TechnologiesSeeder']);

    $prereqs = TechnologyPrerequisite::all();
    expect($prereqs->count())->toBeGreaterThan(0);

    foreach ($prereqs as $prereq) {
        expect($prereq->quantity)
            ->toBeGreaterThanOrEqual(1, "technology_prerequisites.quantity debe ser >= 1");
    }
});

// ─── 5. InventionsSeeder — quantity en invention_prerequisites (T48) ──────────

test('InventionsSeeder persiste quantity en invention_prerequisites para todos los prerequisitos', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);
    Artisan::call('db:seed', ['--class' => 'TechnologiesSeeder']);
    Artisan::call('db:seed', ['--class' => 'InventionsSeeder']);

    $prereqs = InventionPrerequisite::where('prereq_type', 'invention')->get();
    expect($prereqs->count())->toBeGreaterThan(0);

    foreach ($prereqs as $prereq) {
        expect($prereq->quantity)
            ->toBeGreaterThanOrEqual(1, "invention_prerequisites.quantity debe ser >= 1");
    }
});

test('InventionsSeeder — nave-asentamiento exige quantity=2 para acero', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);
    Artisan::call('db:seed', ['--class' => 'TechnologiesSeeder']);
    Artisan::call('db:seed', ['--class' => 'InventionsSeeder']);

    $nave  = Invention::where('name', 'Nave de Asentamiento Interestelar')->first();
    $acero = Invention::where('name', 'Acero')->first();

    expect($nave)->not->toBeNull('Falta el invento Nave de Asentamiento Interestelar');
    expect($acero)->not->toBeNull('Falta el invento Acero');

    $prereq = InventionPrerequisite::where('invention_id', $nave->id)
        ->where('prereq_type', 'invention')
        ->where('prereq_id', $acero->id)
        ->first();

    expect($prereq)->not->toBeNull('nave-asentamiento debe tener acero como prerequisito');
    expect($prereq->quantity)->toBe(2);
});

test('InventionsSeeder — nave-asentamiento exige quantity=2 para vidrio', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);
    Artisan::call('db:seed', ['--class' => 'TechnologiesSeeder']);
    Artisan::call('db:seed', ['--class' => 'InventionsSeeder']);

    $nave   = Invention::where('name', 'Nave de Asentamiento Interestelar')->first();
    $vidrio = Invention::where('name', 'Vidrio')->first();

    expect($nave)->not->toBeNull();
    expect($vidrio)->not->toBeNull('Falta el invento Vidrio');

    $prereq = InventionPrerequisite::where('invention_id', $nave->id)
        ->where('prereq_type', 'invention')
        ->where('prereq_id', $vidrio->id)
        ->first();

    expect($prereq)->not->toBeNull('nave-asentamiento debe tener vidrio como prerequisito');
    expect($prereq->quantity)->toBe(2);
});

test('InventionsSeeder — prerequisitos estándar tienen quantity=1', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);
    Artisan::call('db:seed', ['--class' => 'TechnologiesSeeder']);
    Artisan::call('db:seed', ['--class' => 'InventionsSeeder']);

    // trampa → cuchillo (quantity=1)
    $trampa   = Invention::where('name', 'Trampa')->first();
    $cuchillo = Invention::where('name', 'Cuchillo')->first();

    $prereq = InventionPrerequisite::where('invention_id', $trampa->id)
        ->where('prereq_type', 'invention')
        ->where('prereq_id', $cuchillo->id)
        ->first();

    expect($prereq)->not->toBeNull('trampa debe tener cuchillo como prerequisito');
    expect($prereq->quantity)->toBe(1);
});

// ─── 6. InventionsSeeder — 34 inventos ───────────────────────────────────────

test('InventionsSeeder carga exactamente 34 inventos', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);
    Artisan::call('db:seed', ['--class' => 'TechnologiesSeeder']);
    Artisan::call('db:seed', ['--class' => 'InventionsSeeder']);

    expect(Invention::count())->toBe(34);
});
