<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use App\Models\Material;
use App\Models\TileType;
use App\Models\Technology;
use App\Models\Invention;

uses(RefreshDatabase::class);

// ==========================================
// TEST FOR: TASK 23 (Raw_Tareas)
// Title: Catalog Seeders: Complete Game Data
// ==========================================

// --- ResourcesSeeder ---

test('ResourcesSeeder carga los recursos base con tier y group correctos', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);

    // Recursos Bosque
    $this->assertDatabaseHas('materials', ['name' => 'roble',          'group' => 'bosque', 'tier' => 0]);
    $this->assertDatabaseHas('materials', ['name' => 'pino',           'group' => 'bosque', 'tier' => 0]);
    $this->assertDatabaseHas('materials', ['name' => 'carbon-natural', 'group' => 'bosque', 'tier' => 0]);

    // Recursos Cantera
    $this->assertDatabaseHas('materials', ['name' => 'silex',   'group' => 'cantera', 'tier' => 0]);
    $this->assertDatabaseHas('materials', ['name' => 'granito', 'group' => 'cantera', 'tier' => 0]);

    // Recursos Río
    $this->assertDatabaseHas('materials', ['name' => 'agua',           'group' => 'rio', 'tier' => 0]);
    $this->assertDatabaseHas('materials', ['name' => 'tierras-fertiles','group' => 'rio', 'tier' => 0]);

    // Recursos Prado
    $this->assertDatabaseHas('materials', ['name' => 'lino', 'group' => 'prado', 'tier' => 0]);
    $this->assertDatabaseHas('materials', ['name' => 'lana', 'group' => 'prado', 'tier' => 0]);

    // Recursos Mina
    $this->assertDatabaseHas('materials', ['name' => 'hierro', 'group' => 'mina', 'tier' => 0]);
    $this->assertDatabaseHas('materials', ['name' => 'cobre',  'group' => 'mina', 'tier' => 0]);
});

test('ResourcesSeeder carga los recursos avanzados de nivel 5 con tier correcto', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);

    // Bosque Nv5
    $this->assertDatabaseHas('materials', ['name' => 'latex',               'group' => 'bosque', 'tier' => 2]);
    $this->assertDatabaseHas('materials', ['name' => 'resinas-inflamables', 'group' => 'bosque', 'tier' => 2]);
    $this->assertDatabaseHas('materials', ['name' => 'mat-aisl-nat',        'group' => 'bosque', 'tier' => 2]);

    // Cantera Nv5
    $this->assertDatabaseHas('materials', ['name' => 'silicio',         'group' => 'cantera', 'tier' => 2]);
    $this->assertDatabaseHas('materials', ['name' => 'arena-de-silice', 'group' => 'cantera', 'tier' => 2]);

    // Río Nv5
    $this->assertDatabaseHas('materials', ['name' => 'hidrogeno',      'group' => 'rio', 'tier' => 2]);
    $this->assertDatabaseHas('materials', ['name' => 'gases-naturales', 'group' => 'rio', 'tier' => 2]);

    // Mina Nv5
    $this->assertDatabaseHas('materials', ['name' => 'oro',         'group' => 'mina', 'tier' => 2]);
    $this->assertDatabaseHas('materials', ['name' => 'mat-mag-nat', 'group' => 'mina', 'tier' => 2]);
});

test('ResourcesSeeder no incluye recursos eliminados del diseño', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);

    $this->assertDatabaseMissing('materials', ['name' => 'peces']);
    $this->assertDatabaseMissing('materials', ['name' => 'ambar']);
    $this->assertDatabaseMissing('materials', ['name' => 'caolinita']);
});

// --- TileLevelResourcesSeeder ---

test('TileLevelResourcesSeeder genera los 5 tipos de casilla base en nivel 1', function () {
    Artisan::call('db:seed', ['--class' => 'TileLevelResourcesSeeder']);

    $this->assertDatabaseHas('tile_types', ['base_type' => 'bosque',  'level' => 1]);
    $this->assertDatabaseHas('tile_types', ['base_type' => 'cantera', 'level' => 1]);
    $this->assertDatabaseHas('tile_types', ['base_type' => 'rio',     'level' => 1]);
    $this->assertDatabaseHas('tile_types', ['base_type' => 'prado',   'level' => 1]);
    $this->assertDatabaseHas('tile_types', ['base_type' => 'mina',    'level' => 1]);
});

test('TileLevelResourcesSeeder genera los 5 niveles para cada tipo de casilla', function () {
    Artisan::call('db:seed', ['--class' => 'TileLevelResourcesSeeder']);

    foreach (['bosque', 'cantera', 'rio', 'prado', 'mina'] as $tipo) {
        for ($level = 1; $level <= 5; $level++) {
            $this->assertDatabaseHas('tile_types', ['base_type' => $tipo, 'level' => $level]);
        }
    }
});

test('TileLevelResourcesSeeder asigna el nombre especializado a las casillas de nivel 5', function () {
    Artisan::call('db:seed', ['--class' => 'TileLevelResourcesSeeder']);

    $this->assertDatabaseHas('tile_types', ['base_type' => 'bosque',  'level' => 5, 'name' => 'Pozo de Goma y Resina']);
    $this->assertDatabaseHas('tile_types', ['base_type' => 'cantera', 'level' => 5, 'name' => 'Cantera de Sílice']);
    $this->assertDatabaseHas('tile_types', ['base_type' => 'rio',     'level' => 5, 'name' => 'Extractor de Gases']);
    $this->assertDatabaseHas('tile_types', ['base_type' => 'prado',   'level' => 5, 'name' => 'Granja Organizada']);
    $this->assertDatabaseHas('tile_types', ['base_type' => 'mina',    'level' => 5, 'name' => 'Mina de Minerales']);
});

test('TileLevelResourcesSeeder incluye la casilla especial pueblo', function () {
    Artisan::call('db:seed', ['--class' => 'TileLevelResourcesSeeder']);

    $this->assertDatabaseHas('tile_types', ['base_type' => 'pueblo']);
});

test('TileLevelResourcesSeeder vincula recursos a tile_types con cantidades', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);
    Artisan::call('db:seed', ['--class' => 'TileLevelResourcesSeeder']);

    $bosqueNv1 = TileType::where('base_type', 'bosque')->where('level', 1)->first();
    expect($bosqueNv1)->not->toBeNull()
        ->and($bosqueNv1->materials)->not->toBeEmpty();
});

test('TileLevelResourcesSeeder registra tech_required e invention_required donde aplica', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);
    Artisan::call('db:seed', ['--class' => 'TileLevelResourcesSeeder']);

    // Río Nv4 requiere Agricultura (tech), sin invento requisito
    $rioNv4 = TileType::where('base_type', 'rio')->where('level', 4)->first();
    $pivot  = $rioNv4->materials()->first()?->pivot;

    expect($pivot)->not->toBeNull()
        ->and($pivot->tech_required)->not->toBeNull()
        ->and($pivot->invention_required)->toBeNull();
});

// --- TechnologiesSeeder ---

test('TechnologiesSeeder carga las tecnologías del árbol completo', function () {
    Artisan::call('db:seed', ['--class' => 'TechnologiesSeeder']);

    $count = Technology::count();
    expect($count)->toBeGreaterThanOrEqual(31);
});

test('TechnologiesSeeder incluye las tecnologías clave del árbol', function () {
    Artisan::call('db:seed', ['--class' => 'TechnologiesSeeder']);

    $clave = [
        'Herramientas de Piedra',
        'Control del Fuego',
        'Agricultura',
        'Metalurgia y Aleaciones',
        'Química',
        'Electricidad',
        'Computación',
        'Energías Renovables',
        'Tecnología Espacial',
        'Terraformación',
    ];

    foreach ($clave as $nombre) {
        $this->assertDatabaseHas('technologies', ['name' => $nombre]);
    }
});

test('TechnologiesSeeder registra prerequisitos entre tecnologías', function () {
    Artisan::call('db:seed', ['--class' => 'TechnologiesSeeder']);

    // Metalurgia requiere Control del Fuego
    $metalurgia     = Technology::where('name', 'Metalurgia y Aleaciones')->first();
    $controlFuego   = Technology::where('name', 'Control del Fuego')->first();

    expect($metalurgia->technologyPrerequisites)->not->toBeEmpty();

    $prerequisiteIds = $metalurgia->technologyPrerequisites->pluck('prereq_id');
    expect($prerequisiteIds)->toContain($controlFuego->id);
});

// --- InventionsSeeder ---

test('InventionsSeeder carga los inventos del catálogo completo', function () {
    Artisan::call('db:seed', ['--class' => 'InventionsSeeder']);

    $count = Invention::count();
    expect($count)->toBeGreaterThanOrEqual(34);
});

test('InventionsSeeder incluye los inventos clave del árbol', function () {
    Artisan::call('db:seed', ['--class' => 'InventionsSeeder']);

    $clave = [
        'Cuerda',
        'Hacha',
        'Rueda',
        'Barco',
        'Vidrio',
        'Acero',
        'Láser',
        'Fibra Óptica',
        'Satélite',
        'Estación Espacial',
        'Nave de Asentamiento Interestelar',
    ];

    foreach ($clave as $nombre) {
        $this->assertDatabaseHas('inventions', ['name' => $nombre]);
    }
});

test('InventionsSeeder vincula los costes de recursos a los inventos', function () {
    Artisan::call('db:seed', ['--class' => 'ResourcesSeeder']);
    Artisan::call('db:seed', ['--class' => 'InventionsSeeder']);

    // Hacha consume roble y sílex
    $hacha = Invention::where('name', 'Hacha')->first();
    expect($hacha->inventionCosts)->not->toBeEmpty();
});

test('InventionsSeeder vincula prerequisitos de invento sin consumirlos', function () {
    Artisan::call('db:seed', ['--class' => 'InventionsSeeder']);

    // Trampa requiere Cuerda como prerequisito
    $trampa = Invention::where('name', 'Trampa')->first();
    expect($trampa->inventionPrerequisites)->not->toBeEmpty();

    $tipos = $trampa->inventionPrerequisites->pluck('prereq_type')->unique();
    expect($tipos)->toContain('invention');
});

test('InventionsSeeder no incluye costes que sean otros inventos (solo recursos de casilla)', function () {
    Artisan::call('db:seed', ['--class' => 'InventionsSeeder']);

    // invention_costs.resource_id debe apuntar a materials, nunca a inventions
    $inventionIds = Invention::pluck('id')->toArray();
    $costesConInventoComoRecurso = \DB::table('invention_costs')
        ->whereIn('resource_id', $inventionIds)
        ->count();

    expect($costesConInventoComoRecurso)->toBe(0);
});
