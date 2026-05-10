<?php

namespace Database\Seeders;

use App\Models\Technology;
use App\Models\TechnologyBonus;
use App\Models\TechnologyPrerequisite;
use App\Models\TechnologyUnlock;
use Illuminate\Database\Seeder;

class TechnologiesSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = $this->definitions();
        $techs = [];

        // Paso 1: crear todas las tecnologías
        foreach ($definitions as $slug => $data) {
            $techs[$slug] = Technology::firstOrCreate(
                ['name' => $data['name']],
                ['name' => $data['name']]
            );
        }

        // Paso 2: prerequisitos, bonuses y unlocks
        foreach ($definitions as $slug => $data) {
            $tech = $techs[$slug];

            // Prerequisitos tech → tech
            foreach ($data['prereqs_tech'] as $prereqSlug) {
                TechnologyPrerequisite::firstOrCreate([
                    'technology_id' => $tech->id,
                    'prereq_type' => 'technology',
                    'prereq_id' => $techs[$prereqSlug]->id,
                ]);
            }
            // Prerequisitos tech → invento se completan en InventionsSeeder (necesitan el UUID del invento)

            // Bonificadores
            foreach ($data['bonuses'] as $bonus) {
                TechnologyBonus::firstOrCreate([
                    'technology_id' => $tech->id,
                    'bonus_type' => $bonus['type'],
                    'bonus_target' => $bonus['target'],
                ], [
                    'bonus_value' => $bonus['value'],
                ]);
            }

            // Desbloqueos tech → tech (UUIDs ya disponibles)
            foreach ($data['unlocks_tech'] as $unlockedSlug) {
                TechnologyUnlock::firstOrCreate([
                    'technology_id' => $tech->id,
                    'unlock_type' => 'technology',
                    'unlock_id' => $techs[$unlockedSlug]->id,
                ]);
            }

            // Desbloqueos tech → invento: un registro placeholder (unlock_id null)
            // InventionsSeeder añadirá registros con UUID reales
            if (! empty($data['unlocks_inv'])) {
                TechnologyUnlock::firstOrCreate([
                    'technology_id' => $tech->id,
                    'unlock_type' => 'invention',
                    'unlock_id' => null,
                ]);
            }

            // Desbloqueos tech → tile_level
            foreach ($data['unlocks_tile'] as $tileDesc) {
                TechnologyUnlock::firstOrCreate([
                    'technology_id' => $tech->id,
                    'unlock_type' => 'tile_level',
                    'unlock_id' => null,
                ]);
            }
        }
    }

    private function definitions(): array
    {
        return [
            'herr-piedra' => [
                'name' => 'Herramientas de Piedra',
                'prereqs_tech' => [],
                'bonuses' => [],
                'unlocks_tech' => [],
                'unlocks_inv' => ['Lanza', 'Hacha', 'Rueda', 'Carro'],
                'unlocks_tile' => [],
            ],
            'control-fuego' => [
                'name' => 'Control del Fuego',
                'prereqs_tech' => [],
                'bonuses' => [['type' => 'production_tile', 'value' => 20, 'target' => 'bosque']],
                'unlocks_tech' => ['fermentacion'],
                'unlocks_inv' => ['Cerámica'],
                'unlocks_tile' => [],
            ],
            'ganaderia' => [
                'name' => 'Ganadería',
                'prereqs_tech' => [],
                'bonuses' => [],
                'unlocks_tech' => [],
                'unlocks_inv' => [],
                'unlocks_tile' => ['Nv4 Bosque', 'Nv4 Prado'],
            ],
            'ceramica-alfareria' => [
                'name' => 'Cerámica y Alfarería',
                'prereqs_tech' => ['control-fuego'],
                'bonuses' => [],
                'unlocks_tech' => [],
                'unlocks_inv' => ['Vidrio'],
                'unlocks_tile' => ['Nv4 Cantera'],
            ],
            'tejido' => [
                'name' => 'Tejido',
                'prereqs_tech' => [],
                'bonuses' => [['type' => 'production_tile', 'value' => 20, 'target' => 'prado']],
                'unlocks_tech' => [],
                'unlocks_inv' => [],
                'unlocks_tile' => ['Nv5 Prado'],
            ],
            'agricultura' => [
                'name' => 'Agricultura',
                'prereqs_tech' => [],
                'bonuses' => [],
                'unlocks_tech' => [],
                'unlocks_inv' => ['Barco', 'Molino', 'Acueducto', 'Arado'],
                'unlocks_tile' => ['Nv4 Río'],
            ],
            'fermentacion' => [
                'name' => 'Fermentación',
                'prereqs_tech' => ['ceramica-alfareria'],
                'bonuses' => [['type' => 'production_global', 'value' => 10, 'target' => '']],
                'unlocks_tech' => ['conservacion'],
                'unlocks_inv' => [],
                'unlocks_tile' => [],
            ],
            'metalurgia' => [
                'name' => 'Metalurgia y Aleaciones',
                'prereqs_tech' => [],
                'bonuses' => [],
                'unlocks_tech' => [],
                'unlocks_inv' => ['Acero', 'Moneda', 'Brújula', 'Reloj'],
                'unlocks_tile' => ['Nv3 Mina', 'Nv4 Mina'],
            ],
            'conservacion' => [
                'name' => 'Conservación de Alimentos',
                'prereqs_tech' => ['fermentacion'],
                'bonuses' => [['type' => 'event_mitigation', 'value' => -25, 'target' => '']],
                'unlocks_tech' => [],
                'unlocks_inv' => [],
                'unlocks_tile' => ['Nv5 Prado'],
            ],
            'quimica' => [
                'name' => 'Química',
                'prereqs_tech' => [],
                'bonuses' => [],
                'unlocks_tech' => [],
                'unlocks_inv' => ['Papel', 'Microscopio', 'Penicilina', 'Bombilla'],
                'unlocks_tile' => ['Nv5 Bosque', 'Nv5 Río'],
            ],
            'escritura' => [
                'name' => 'Escritura',
                'prereqs_tech' => [],
                'bonuses' => [],
                'unlocks_tech' => [],
                'unlocks_inv' => ['Imprenta'],
                'unlocks_tile' => [],
            ],
            'fotografia' => [
                'name' => 'Fotografía',
                'prereqs_tech' => ['escritura'],
                'bonuses' => [['type' => 'production_tile', 'value' => 15, 'target' => 'cantera']],
                'unlocks_tech' => [],
                'unlocks_inv' => ['Telescopio'],
                'unlocks_tile' => [],
            ],
            'electricidad' => [
                'name' => 'Electricidad',
                'prereqs_tech' => [],
                'bonuses' => [],
                'unlocks_tech' => [],
                'unlocks_inv' => ['Láser', 'Fibra Óptica'],
                'unlocks_tile' => [],
            ],
            'computacion' => [
                'name' => 'Computación',
                // Reloj es un invento, no una tech; se registra en InventionsSeeder
                'prereqs_tech' => ['electricidad'],
                'bonuses' => [['type' => 'production_tile', 'value' => 20, 'target' => 'cantera']],
                'unlocks_tech' => ['comunicaciones', 'gps'],
                'unlocks_inv' => ['Teléfono Móvil'],
                'unlocks_tile' => [],
            ],
            'comunicaciones' => [
                'name' => 'Comunicaciones Inalámbricas',
                'prereqs_tech' => ['computacion'],
                'bonuses' => [],
                'unlocks_tech' => ['internet'],
                'unlocks_inv' => [],
                'unlocks_tile' => [],
            ],
            'gps' => [
                'name' => 'GPS',
                'prereqs_tech' => ['computacion'],
                'bonuses' => [],
                'unlocks_tech' => [],
                'unlocks_inv' => [],
                'unlocks_tile' => [],
            ],
            'internet' => [
                'name' => 'Internet',
                'prereqs_tech' => ['comunicaciones'],
                'bonuses' => [],
                'unlocks_tech' => ['ia'],
                'unlocks_inv' => [],
                'unlocks_tile' => [],
            ],
            'ia' => [
                'name' => 'Inteligencia Artificial',
                'prereqs_tech' => ['internet', 'computacion'],
                'bonuses' => [['type' => 'production_global', 'value' => 15, 'target' => '']],
                'unlocks_tech' => ['robotica', 'sistemas-autonomos'],
                'unlocks_inv' => [],
                'unlocks_tile' => [],
            ],
            'energias-renovables' => [
                'name' => 'Energías Renovables',
                'prereqs_tech' => ['electricidad'],
                'bonuses' => [['type' => 'production_global', 'value' => 30, 'target' => '']],
                'unlocks_tech' => [],
                'unlocks_inv' => ['Avión', 'Satélite', 'Estación Espacial'],
                'unlocks_tile' => [],
            ],
            'robotica' => [
                'name' => 'Robótica',
                'prereqs_tech' => ['ia'],
                'bonuses' => [],
                'unlocks_tech' => ['sistemas-autonomos'],
                'unlocks_inv' => [],
                'unlocks_tile' => [],
            ],
            'nanotecnologia' => [
                'name' => 'Nanotecnología',
                'prereqs_tech' => ['fotografia'],
                'bonuses' => [['type' => 'production_tile', 'value' => 25, 'target' => 'bosque']],
                'unlocks_tech' => ['edicion-genetica'],
                'unlocks_inv' => [],
                'unlocks_tile' => [],
            ],
            'edicion-genetica' => [
                'name' => 'Edición Genética',
                'prereqs_tech' => ['nanotecnologia'],
                'bonuses' => [['type' => 'production_tile', 'value' => 20, 'target' => 'prado']],
                'unlocks_tech' => ['biotecnologia'],
                'unlocks_inv' => [],
                'unlocks_tile' => [],
            ],
            'biotecnologia' => [
                'name' => 'Biotecnología',
                'prereqs_tech' => ['edicion-genetica'],
                'bonuses' => [['type' => 'event_mitigation', 'value' => -30, 'target' => '']],
                'unlocks_tech' => ['terraformacion'],
                'unlocks_inv' => [],
                'unlocks_tile' => [],
            ],
            'sistemas-autonomos' => [
                'name' => 'Sistemas Autónomos',
                'prereqs_tech' => ['robotica', 'ia'],
                'bonuses' => [['type' => 'invention_cost_reduction', 'value' => -15, 'target' => '']],
                'unlocks_tech' => ['terraformacion'],
                'unlocks_inv' => [],
                'unlocks_tile' => [],
            ],
            'tecnologia-espacial' => [
                'name' => 'Tecnología Espacial',
                'prereqs_tech' => ['gps'],
                'bonuses' => [],
                'unlocks_tech' => [],
                'unlocks_inv' => ['Nave de Asentamiento Interestelar'],
                'unlocks_tile' => [],
            ],
            'terraformacion' => [
                'name' => 'Terraformación',
                'prereqs_tech' => ['tecnologia-espacial', 'biotecnologia', 'sistemas-autonomos'],
                'bonuses' => [],
                'unlocks_tech' => [],
                'unlocks_inv' => ['Nave de Asentamiento Interestelar'],
                'unlocks_tile' => [],
            ],
        ];
    }
}
