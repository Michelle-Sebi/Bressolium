<?php

namespace Database\Seeders;

use App\Models\Invention;
use App\Models\InventionBonus;
use App\Models\InventionCost;
use App\Models\InventionPrerequisite;
use App\Models\InventionUnlock;
use App\Models\Material;
use App\Models\Technology;
use App\Models\TechnologyPrerequisite;
use App\Models\TechnologyUnlock;
use Illuminate\Database\Seeder;

class InventionsSeeder extends Seeder
{
    public function run(): void
    {
        $invs = [];
        $defs = $this->definitions();

        // Paso 1: crear todos los inventos
        foreach ($defs as $slug => $data) {
            $invs[$slug] = Invention::firstOrCreate(
                ['name' => $data['name']],
                ['name' => $data['name']]
            );
        }

        // Helper: obtener UUID de material por nombre (null si no existe)
        $mat = fn (string $n) => Material::where('name', $n)->value('id');
        // Helper: obtener UUID de tecnología por nombre (null si no existe)
        $tech = fn (string $n) => Technology::where('name', $n)->value('id');

        // Paso 2: prerequisitos, costes, bonuses, unlocks
        foreach ($defs as $slug => $data) {
            $inv = $invs[$slug];

            // Prerequisitos inv → invento
            // Cada entrada puede ser un slug (string) o ['slug' => ..., 'qty' => N]
            foreach ($data['prereqs_inv'] as $prereqEntry) {
                $prereqSlug = is_array($prereqEntry) ? $prereqEntry['slug'] : $prereqEntry;
                $prereqQty = is_array($prereqEntry) ? ($prereqEntry['qty'] ?? 1) : 1;

                if (isset($invs[$prereqSlug])) {
                    InventionPrerequisite::firstOrCreate(
                        [
                            'invention_id' => $inv->id,
                            'prereq_type' => 'invention',
                            'prereq_id' => $invs[$prereqSlug]->id,
                        ],
                        ['quantity' => $prereqQty]
                    );
                }
            }

            // Prerequisitos inv → tecnología (solo si la tech existe)
            foreach ($data['prereqs_tech'] as $techName) {
                $techId = $tech($techName);
                if ($techId) {
                    InventionPrerequisite::firstOrCreate([
                        'invention_id' => $inv->id,
                        'prereq_type' => 'technology',
                        'prereq_id' => $techId,
                    ]);
                }
            }

            // Costes de recursos (solo materiales, nunca inventos)
            foreach ($data['costs'] as $matName => $qty) {
                $matId = $mat($matName);
                if ($matId) {
                    InventionCost::firstOrCreate([
                        'invention_id' => $inv->id,
                        'resource_id' => $matId,
                    ], [
                        'quantity' => $qty,
                    ]);
                }
            }

            // Bonificadores
            foreach ($data['bonuses'] as $bonus) {
                InventionBonus::firstOrCreate([
                    'invention_id' => $inv->id,
                    'bonus_type' => $bonus['type'],
                    'bonus_target' => $bonus['target'],
                ], [
                    'bonus_value' => $bonus['value'],
                ]);
            }

            // Desbloqueos inv → invento
            foreach ($data['unlocks_inv'] as $unlockedSlug) {
                if (isset($invs[$unlockedSlug])) {
                    InventionUnlock::firstOrCreate([
                        'invention_id' => $inv->id,
                        'unlock_type' => 'invention',
                        'unlock_id' => $invs[$unlockedSlug]->id,
                    ]);
                }
            }

            // Desbloqueos inv → tecnología (solo si existe)
            foreach ($data['unlocks_tech'] as $techName) {
                $techId = $tech($techName);
                if ($techId) {
                    InventionUnlock::firstOrCreate([
                        'invention_id' => $inv->id,
                        'unlock_type' => 'technology',
                        'unlock_id' => $techId,
                    ]);
                }
            }

            // Desbloqueos inv → tile_level
            foreach ($data['unlocks_tile'] as $tileDesc) {
                InventionUnlock::firstOrCreate([
                    'invention_id' => $inv->id,
                    'unlock_type' => 'tile_level',
                    'unlock_id' => null,
                ]);
            }
        }

        // Paso 3: completar technology_unlocks con UUIDs reales de inventos
        // (TechnologiesSeeder dejó placeholders con unlock_id=null)
        $techUnlockMap = $this->techInventionUnlocks();
        foreach ($techUnlockMap as $techName => $invSlugs) {
            $techId = $tech($techName);
            if (! $techId) {
                continue;
            }
            foreach ($invSlugs as $invSlug) {
                if (! isset($invs[$invSlug])) {
                    continue;
                }
                TechnologyUnlock::firstOrCreate([
                    'technology_id' => $techId,
                    'unlock_type' => 'invention',
                    'unlock_id' => $invs[$invSlug]->id,
                ]);
            }
        }

        // Paso 4: prerequisitos de tecnología que requieren inventos
        $techInvPrereqs = $this->techInventionPrerequisites();
        foreach ($techInvPrereqs as $techName => $invSlugs) {
            $techId = $tech($techName);
            if (! $techId) {
                continue;
            }
            foreach ($invSlugs as $invSlug) {
                if (! isset($invs[$invSlug])) {
                    continue;
                }
                TechnologyPrerequisite::firstOrCreate([
                    'technology_id' => $techId,
                    'prereq_type' => 'invention',
                    'prereq_id' => $invs[$invSlug]->id,
                ]);
            }
        }
    }

    private function definitions(): array
    {
        return [
            // ── Edad de Piedra ──────────────────────────────────────
            'cuchillo' => [
                'name' => 'Cuchillo',
                'prereqs_inv' => [],
                'prereqs_tech' => [],
                'costs' => ['obsidiana' => 8, 'roble' => 3],
                'bonuses' => [],
                'unlocks_inv' => ['trampa'],
                'unlocks_tech' => [],
                'unlocks_tile' => [],
            ],
            'cuerda' => [
                'name' => 'Cuerda',
                'prereqs_inv' => [],
                'prereqs_tech' => [],
                'costs' => ['lino' => 8, 'canamo' => 5],
                'bonuses' => [],
                'unlocks_inv' => ['barco'],
                'unlocks_tech' => [],
                'unlocks_tile' => [],
            ],
            'lanza' => [
                'name' => 'Lanza',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Herramientas de Piedra'],
                'costs' => ['silex' => 8, 'roble' => 5],
                'bonuses' => [],
                'unlocks_inv' => ['arcos'],
                'unlocks_tech' => [],
                'unlocks_tile' => [],
            ],
            'hacha' => [
                'name' => 'Hacha',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Herramientas de Piedra'],
                'costs' => ['silex' => 10, 'roble' => 8],
                'bonuses' => [['type' => 'production_tile', 'value' => 25, 'target' => 'bosque']],
                'unlocks_inv' => [],
                'unlocks_tech' => [],
                'unlocks_tile' => ['Nv5 Cantera'],
            ],
            'arcos' => [
                'name' => 'Arcos',
                'prereqs_inv' => ['lanza'],
                'prereqs_tech' => ['Herramientas de Piedra'],
                'costs' => ['roble' => 8],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Ganadería'],
                'unlocks_tile' => [],
            ],
            'trampa' => [
                'name' => 'Trampa',
                'prereqs_inv' => ['cuchillo'],
                'prereqs_tech' => [],
                'costs' => ['roble' => 5],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Ganadería'],
                'unlocks_tile' => [],
            ],
            'refugios' => [
                'name' => 'Refugios',
                'prereqs_inv' => [],
                'prereqs_tech' => [],
                'costs' => ['roble' => 15, 'cana-comun' => 10],
                'bonuses' => [['type' => 'event_mitigation', 'value' => -10, 'target' => '']],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Ganadería'],
                'unlocks_tile' => [],
            ],
            'rueda' => [
                'name' => 'Rueda',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Herramientas de Piedra'],
                'costs' => ['roble' => 12, 'silex' => 8],
                'bonuses' => [],
                'unlocks_inv' => ['carro'],
                'unlocks_tech' => [],
                'unlocks_tile' => [],
            ],
            'carro' => [
                'name' => 'Carro',
                'prereqs_inv' => ['rueda'],
                'prereqs_tech' => ['Herramientas de Piedra'],
                'costs' => ['roble' => 15, 'pieles' => 8],
                'bonuses' => [],
                'unlocks_inv' => ['molino'],
                'unlocks_tech' => [],
                'unlocks_tile' => [],
            ],
            // ── Edad Media ──────────────────────────────────────────
            'tela' => [
                'name' => 'Tela',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Ganadería'],
                'costs' => ['lino' => 8, 'yute' => 6, 'lana' => 8],
                'bonuses' => [['type' => 'production_tile', 'value' => 15, 'target' => 'prado']],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Tejido'],
                'unlocks_tile' => [],
            ],
            'ceramica-inv' => [
                'name' => 'Cerámica',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Control del Fuego'],
                'costs' => ['granito' => 10, 'agua' => 5],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Cerámica y Alfarería'],
                'unlocks_tile' => [],
            ],
            'barco' => [
                'name' => 'Barco',
                'prereqs_inv' => ['cuerda'],
                'prereqs_tech' => ['Agricultura'],
                'costs' => ['roble' => 20, 'pino' => 15, 'canamo' => 8],
                'bonuses' => [],
                'unlocks_inv' => ['brujula'],
                'unlocks_tech' => [],
                'unlocks_tile' => [],
            ],
            'molino' => [
                'name' => 'Molino',
                'prereqs_inv' => ['carro'],
                'prereqs_tech' => ['Agricultura'],
                'costs' => ['granito' => 15, 'roble' => 10],
                'bonuses' => [['type' => 'production_tile', 'value' => 20, 'target' => 'prado']],
                'unlocks_inv' => [],
                'unlocks_tech' => [],
                'unlocks_tile' => [],
            ],
            'acueducto' => [
                'name' => 'Acueducto',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Agricultura'],
                'costs' => ['granito' => 20, 'agua' => 10],
                'bonuses' => [['type' => 'production_tile', 'value' => 30, 'target' => 'rio']],
                'unlocks_inv' => [],
                'unlocks_tech' => [],
                'unlocks_tile' => [],
            ],
            'arado' => [
                'name' => 'Arado',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Agricultura'],
                'costs' => ['roble' => 10, 'hierro' => 5],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => [],
                'unlocks_tile' => ['Nv5 Prado'],
            ],
            // ── Edad de Bronce ──────────────────────────────────────
            'vidrio' => [
                'name' => 'Vidrio',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Cerámica y Alfarería'],
                'costs' => ['arena-de-silice' => 15, 'arena-de-cuarzo' => 10],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Química'],
                'unlocks_tile' => [],
            ],
            'acero' => [
                'name' => 'Acero',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Metalurgia y Aleaciones'],
                'costs' => ['hierro' => 20, 'carbon-natural' => 15, 'grafito' => 5],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Escritura', 'Química'],
                'unlocks_tile' => [],
            ],
            'moneda' => [
                'name' => 'Moneda',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Metalurgia y Aleaciones'],
                'costs' => ['cobre' => 15, 'oro' => 5],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Escritura'],
                'unlocks_tile' => [],
            ],
            'brujula' => [
                'name' => 'Brújula',
                'prereqs_inv' => ['barco'],
                'prereqs_tech' => ['Metalurgia y Aleaciones'],
                'costs' => ['hierro' => 15, 'cobre' => 8, 'carbon-natural' => 10],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => [],
                'unlocks_tile' => ['Nv5 Mina'],
            ],
            'reloj' => [
                'name' => 'Reloj',
                'prereqs_inv' => ['acero'],
                'prereqs_tech' => ['Metalurgia y Aleaciones'],
                'costs' => ['cobre' => 10],
                'bonuses' => [['type' => 'production_speed', 'value' => 10, 'target' => '']],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Computación'],
                'unlocks_tile' => [],
            ],
            // ── Edad Moderna ────────────────────────────────────────
            'papel' => [
                'name' => 'Papel',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Química'],
                'costs' => ['cana-comun' => 15, 'agua' => 10],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Escritura'],
                'unlocks_tile' => [],
            ],
            'imprenta' => [
                'name' => 'Imprenta',
                'prereqs_inv' => ['papel', 'acero'],
                'prereqs_tech' => ['Escritura'],
                'costs' => [],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Fotografía'],
                'unlocks_tile' => [],
            ],
            'microscopio' => [
                'name' => 'Microscopio',
                'prereqs_inv' => ['vidrio', 'acero'],
                'prereqs_tech' => ['Química'],
                'costs' => ['arena-de-silice' => 10, 'hierro' => 8],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Fotografía', 'Nanotecnología', 'Edición Genética'],
                'unlocks_tile' => [],
            ],
            'penicilina' => [
                'name' => 'Penicilina',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Química'],
                'costs' => ['agua' => 10, 'tierras-fertiles' => 15, 'latex' => 8],
                'bonuses' => [['type' => 'event_mitigation', 'value' => -50, 'target' => '']],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Biotecnología'],
                'unlocks_tile' => [],
            ],
            'bombilla' => [
                'name' => 'Bombilla',
                'prereqs_inv' => ['vidrio'],
                'prereqs_tech' => ['Química'],
                'costs' => ['cobre' => 10, 'carbon-natural' => 8],
                'bonuses' => [['type' => 'production_global', 'value' => 20, 'target' => '']],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Electricidad'],
                'unlocks_tile' => [],
            ],
            // ── Era Industrial ──────────────────────────────────────
            'bateria' => [
                'name' => 'Batería',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Química'],
                'costs' => ['cobre' => 15, 'estano' => 10, 'resinas-inflamables' => 10],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Electricidad'],
                'unlocks_tile' => [],
            ],
            'laser' => [
                'name' => 'Láser',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Electricidad'],
                'costs' => ['silicio' => 15, 'cristales-nat' => 10],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Nanotecnología'],
                'unlocks_tile' => [],
            ],
            'fibra-optica' => [
                'name' => 'Fibra Óptica',
                'prereqs_inv' => ['vidrio'],
                'prereqs_tech' => ['Electricidad'],
                'costs' => ['silicio' => 20, 'mat-aisl-nat' => 10],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Comunicaciones Inalámbricas'],
                'unlocks_tile' => [],
            ],
            // ── Era de Información ──────────────────────────────────
            'telefono-movil' => [
                'name' => 'Teléfono Móvil',
                'prereqs_inv' => [],
                'prereqs_tech' => ['Computación'],
                'costs' => ['silicio' => 20, 'mat-mag-nat' => 10, 'min-semi' => 10],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Comunicaciones Inalámbricas'],
                'unlocks_tile' => [],
            ],
            'telescopio' => [
                'name' => 'Telescopio',
                'prereqs_inv' => ['vidrio', 'acero'],
                'prereqs_tech' => ['Fotografía'],
                'costs' => ['arena-de-cuarzo' => 15, 'hierro' => 8],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Tecnología Espacial'],
                'unlocks_tile' => [],
            ],
            // ── Era Moderna Tardía ──────────────────────────────────
            'avion' => [
                'name' => 'Avión',
                'prereqs_inv' => ['cuerda', 'acero'],
                'prereqs_tech' => ['Energías Renovables'],
                'costs' => ['carbon-natural' => 20, 'hierro' => 15],
                'bonuses' => [['type' => 'production_tile', 'value' => 0, 'target' => 'exploracion']],
                'unlocks_inv' => [],
                'unlocks_tech' => [],
                'unlocks_tile' => [],
            ],
            'satelite' => [
                'name' => 'Satélite',
                'prereqs_inv' => ['acero'],
                'prereqs_tech' => ['Energías Renovables', 'GPS'],
                'costs' => ['silicio' => 15, 'gases-naturales' => 10],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Tecnología Espacial'],
                'unlocks_tile' => [],
            ],
            'estacion-espacial' => [
                'name' => 'Estación Espacial',
                'prereqs_inv' => ['acero'],
                'prereqs_tech' => ['Energías Renovables'],
                'costs' => ['silicio' => 20, 'hidrogeno' => 15],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => ['Tecnología Espacial'],
                'unlocks_tile' => [],
            ],
            // ── Era Espacial ────────────────────────────────────────
            'nave-asentamiento' => [
                'name' => 'Nave de Asentamiento Interestelar',
                'prereqs_inv' => [
                    'estacion-espacial',
                    ['slug' => 'acero',  'qty' => 2],
                    ['slug' => 'vidrio', 'qty' => 2],
                ],
                'prereqs_tech' => ['Terraformación'],
                'costs' => ['silicio' => 400, 'hidrogeno' => 600, 'agua' => 300, 'mat-aisl-nat' => 200],
                'bonuses' => [],
                'unlocks_inv' => [],
                'unlocks_tech' => [],
                'unlocks_tile' => [],
            ],
        ];
    }

    /** Qué inventos desbloquea cada tecnología (para completar technology_unlocks con UUIDs reales) */
    private function techInventionUnlocks(): array
    {
        return [
            'Herramientas de Piedra' => ['lanza', 'hacha', 'rueda', 'carro'],
            'Control del Fuego' => ['ceramica-inv'],
            'Cerámica y Alfarería' => ['vidrio'],
            'Agricultura' => ['barco', 'molino', 'acueducto', 'arado'],
            'Metalurgia y Aleaciones' => ['acero', 'moneda', 'brujula', 'reloj'],
            'Química' => ['papel', 'microscopio', 'penicilina', 'bombilla'],
            'Escritura' => ['imprenta'],
            'Fotografía' => ['telescopio'],
            'Electricidad' => ['laser', 'fibra-optica'],
            'Computación' => ['telefono-movil'],
            'Energías Renovables' => ['avion', 'satelite', 'estacion-espacial'],
            'Tecnología Espacial' => ['nave-asentamiento'],
            'Terraformación' => ['nave-asentamiento'],
        ];
    }

    /** Qué inventos requiere cada tecnología como prerequisito (completar technology_prerequisites) */
    private function techInventionPrerequisites(): array
    {
        return [
            'Ganadería' => ['trampa', 'refugios', 'arcos'],
            'Cerámica y Alfarería' => ['ceramica-inv'],
            'Tejido' => ['cuerda', 'tela'],
            'Agricultura' => ['cuerda', 'hacha'],
            'Metalurgia y Aleaciones' => ['hacha', 'molino'],
            'Conservación de Alimentos' => ['acueducto'],
            'Química' => ['vidrio', 'acero'],
            'Escritura' => ['papel', 'moneda'],
            'Fotografía' => ['microscopio', 'papel'],
            'Electricidad' => ['bateria', 'bombilla'],
            'Computación' => ['reloj'],
            'Comunicaciones Inalámbricas' => ['telefono-movil', 'fibra-optica'],
            'Nanotecnología' => ['laser'],
            'Edición Genética' => ['microscopio'],
            'Biotecnología' => ['penicilina'],
            'Robótica' => ['acero'],
            'Energías Renovables' => ['acero'],
            'Tecnología Espacial' => ['telescopio', 'estacion-espacial'],
        ];
    }
}
