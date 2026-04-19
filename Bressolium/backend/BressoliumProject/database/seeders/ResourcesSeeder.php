<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;

class ResourcesSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            // Bosque — tier 0 (Nv1-4)
            ['name' => 'roble',          'tier' => 0, 'group' => 'bosque'],
            ['name' => 'pino',           'tier' => 0, 'group' => 'bosque'],
            ['name' => 'carbon-natural', 'tier' => 0, 'group' => 'bosque'],
            ['name' => 'pieles',         'tier' => 0, 'group' => 'bosque'],
            // Bosque — tier 2 (Nv5 Pozo de Goma y Resina)
            ['name' => 'latex',               'tier' => 2, 'group' => 'bosque'],
            ['name' => 'resinas-inflamables', 'tier' => 2, 'group' => 'bosque'],
            ['name' => 'mat-aisl-nat',        'tier' => 2, 'group' => 'bosque'],

            // Cantera — tier 0 (Nv1-4)
            ['name' => 'silex',    'tier' => 0, 'group' => 'cantera'],
            ['name' => 'granito',  'tier' => 0, 'group' => 'cantera'],
            ['name' => 'obsidiana','tier' => 0, 'group' => 'cantera'],
            // Cantera — tier 2 (Nv5 Cantera de Sílice)
            ['name' => 'arena-de-silice', 'tier' => 2, 'group' => 'cantera'],
            ['name' => 'arena-de-cuarzo', 'tier' => 2, 'group' => 'cantera'],
            ['name' => 'cristales-nat',   'tier' => 2, 'group' => 'cantera'],
            ['name' => 'silicio',         'tier' => 2, 'group' => 'cantera'],
            ['name' => 'min-semi',        'tier' => 2, 'group' => 'cantera'],

            // Río — tier 0 (Nv1-4)
            ['name' => 'agua',            'tier' => 0, 'group' => 'rio'],
            ['name' => 'cana-comun',      'tier' => 0, 'group' => 'rio'],
            ['name' => 'tierras-fertiles','tier' => 0, 'group' => 'rio'],
            // Río — tier 2 (Nv5 Extractor de Gases)
            ['name' => 'hidrogeno',      'tier' => 2, 'group' => 'rio'],
            ['name' => 'gases-naturales','tier' => 2, 'group' => 'rio'],

            // Prado — tier 0 (Nv1-4)
            ['name' => 'lino',  'tier' => 0, 'group' => 'prado'],
            ['name' => 'yute',  'tier' => 0, 'group' => 'prado'],
            ['name' => 'canamo','tier' => 0, 'group' => 'prado'],
            ['name' => 'lana',  'tier' => 0, 'group' => 'prado'],

            // Mina — tier 0 (Nv1-4)
            ['name' => 'cobre',  'tier' => 0, 'group' => 'mina'],
            ['name' => 'hierro', 'tier' => 0, 'group' => 'mina'],
            ['name' => 'estano', 'tier' => 0, 'group' => 'mina'],
            ['name' => 'grafito','tier' => 0, 'group' => 'mina'],
            // Mina — tier 2 (Nv5 Mina de Minerales)
            ['name' => 'oro',        'tier' => 2, 'group' => 'mina'],
            ['name' => 'mat-mag-nat','tier' => 2, 'group' => 'mina'],
        ];

        foreach ($materials as $data) {
            Material::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
