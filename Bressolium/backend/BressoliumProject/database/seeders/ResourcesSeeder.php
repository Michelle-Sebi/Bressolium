<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class ResourcesSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            // Bosque
            ['name' => 'roble',               'tier' => 0, 'group' => 'bosque'], // Nv1
            ['name' => 'pino',                'tier' => 0, 'group' => 'bosque'], // Nv2
            ['name' => 'carbon-natural',      'tier' => 0, 'group' => 'bosque'], // Nv3
            ['name' => 'pieles',              'tier' => 0, 'group' => 'bosque'], // Nv4
            ['name' => 'latex',               'tier' => 2, 'group' => 'bosque'], // Nv5
            ['name' => 'resinas-inflamables', 'tier' => 2, 'group' => 'bosque'], // Nv5
            ['name' => 'materiales-aislantes',        'tier' => 2, 'group' => 'bosque'], // Nv5

            // Cantera
            ['name' => 'silex',           'tier' => 0, 'group' => 'cantera'], // Nv1
            ['name' => 'granito',         'tier' => 0, 'group' => 'cantera'], // Nv2
            ['name' => 'obsidiana',       'tier' => 0, 'group' => 'cantera'], // Nv3
            ['name' => 'arena-de-silice', 'tier' => 2, 'group' => 'cantera'], // Nv5
            ['name' => 'arena-de-cuarzo', 'tier' => 2, 'group' => 'cantera'], // Nv5
            ['name' => 'cristales-naturales',   'tier' => 2, 'group' => 'cantera'], // Nv5
            ['name' => 'silicio',         'tier' => 2, 'group' => 'cantera'], // Nv5
            ['name' => 'minerales-semiconductores',        'tier' => 2, 'group' => 'cantera'], // Nv5

            // Río
            ['name' => 'agua',             'tier' => 0, 'group' => 'rio'], // Nv1
            ['name' => 'cana-comun',       'tier' => 0, 'group' => 'rio'], // Nv2
            ['name' => 'tierras-fertiles', 'tier' => 0, 'group' => 'rio'], // Nv3
            ['name' => 'hidrogeno',        'tier' => 2, 'group' => 'rio'], // Nv5
            ['name' => 'gases-naturales',  'tier' => 2, 'group' => 'rio'], // Nv5

            // Prado
            ['name' => 'lino',   'tier' => 0, 'group' => 'prado'], // Nv1
            ['name' => 'yute',   'tier' => 0, 'group' => 'prado'], // Nv2
            ['name' => 'canamo', 'tier' => 0, 'group' => 'prado'], // Nv3
            ['name' => 'lana',   'tier' => 0, 'group' => 'prado'], // Nv4

            // Mina
            ['name' => 'cobre',       'tier' => 0, 'group' => 'mina'], // Nv1
            ['name' => 'hierro',      'tier' => 0, 'group' => 'mina'], // Nv2
            ['name' => 'estano',      'tier' => 0, 'group' => 'mina'], // Nv3
            ['name' => 'grafito',     'tier' => 0, 'group' => 'mina'], // Nv4
            ['name' => 'oro',         'tier' => 2, 'group' => 'mina'], // Nv5
            ['name' => 'materiales-magneticos', 'tier' => 2, 'group' => 'mina'], // Nv5
        ];

        foreach ($materials as $data) {
            Material::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
