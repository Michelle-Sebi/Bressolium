<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\TileType;
use Illuminate\Database\Seeder;

class TileLevelResourcesSeeder extends Seeder
{
    public function run(): void
    {
        // Nombres especializados de Nivel 5
        $nv5Names = [
            'bosque' => 'Pozo de Goma y Resina',
            'cantera' => 'Cantera de Sílice',
            'rio' => 'Extractor de Gases',
            'prado' => 'Granja Organizada',
            'mina' => 'Mina de Minerales',
        ];

        // Crear los 25 tile_types (5 tipos × 5 niveles)
        $tileTypes = [];
        foreach (['bosque', 'cantera', 'rio', 'prado', 'mina'] as $base) {
            for ($level = 1; $level <= 5; $level++) {
                $name = $level === 5 ? $nv5Names[$base] : ucfirst($base).' Nv'.$level;
                $tileTypes[$base][$level] = TileType::firstOrCreate(
                    ['base_type' => $base, 'level' => $level],
                    ['name' => $name]
                );
            }
        }

        // Casilla especial pueblo (sin evolución)
        TileType::firstOrCreate(
            ['base_type' => 'pueblo'],
            ['name' => 'Pueblo', 'level' => 1]
        );

        // Helper para adjuntar material a un tile_type (solo producción, sin requisitos de tecnología)
        $attach = function (TileType $tile, string $matName, int $qty) {
            $material = Material::where('name', $matName)->first();
            if (! $material) {
                return;
            }
            $tile->materials()->syncWithoutDetaching([
                $material->id => ['quantity' => $qty],
            ]);
        };

        // ── BOSQUE ──────────────────────────────────────────────────
        $attach($tileTypes['bosque'][1], 'roble', 5);
        $attach($tileTypes['bosque'][2], 'roble', 8);
        $attach($tileTypes['bosque'][2], 'pino', 8);
        $attach($tileTypes['bosque'][3], 'roble', 8);
        $attach($tileTypes['bosque'][3], 'pino', 8);
        $attach($tileTypes['bosque'][3], 'carbon-natural', 8);
        $attach($tileTypes['bosque'][4], 'roble', 9);
        $attach($tileTypes['bosque'][4], 'pino', 9);
        $attach($tileTypes['bosque'][4], 'carbon-natural', 9);
        $attach($tileTypes['bosque'][4], 'pieles', 9);
        $attach($tileTypes['bosque'][5], 'latex', 8);
        $attach($tileTypes['bosque'][5], 'resinas-inflamables', 8);
        $attach($tileTypes['bosque'][5], 'mat-aisl-nat', 4);

        // ── CANTERA ──────────────────────────────────────────────────
        $attach($tileTypes['cantera'][1], 'silex', 5);
        $attach($tileTypes['cantera'][2], 'silex', 8);
        $attach($tileTypes['cantera'][2], 'granito', 8);
        $attach($tileTypes['cantera'][3], 'silex', 8);
        $attach($tileTypes['cantera'][3], 'granito', 8);
        $attach($tileTypes['cantera'][3], 'obsidiana', 8);
        $attach($tileTypes['cantera'][4], 'silex', 9);
        $attach($tileTypes['cantera'][4], 'granito', 9);
        $attach($tileTypes['cantera'][4], 'obsidiana', 9);
        $attach($tileTypes['cantera'][5], 'arena-de-silice', 8);
        $attach($tileTypes['cantera'][5], 'arena-de-cuarzo', 8);
        $attach($tileTypes['cantera'][5], 'cristales-nat', 8);
        $attach($tileTypes['cantera'][5], 'silicio', 10);
        $attach($tileTypes['cantera'][5], 'min-semi', 8);

        // ── RÍO ──────────────────────────────────────────────────────
        $attach($tileTypes['rio'][1], 'agua', 5);
        $attach($tileTypes['rio'][2], 'agua', 8);
        $attach($tileTypes['rio'][2], 'cana-comun', 8);
        $attach($tileTypes['rio'][3], 'agua', 8);
        $attach($tileTypes['rio'][3], 'cana-comun', 8);
        $attach($tileTypes['rio'][3], 'tierras-fertiles', 8);
        $attach($tileTypes['rio'][4], 'agua', 9);
        $attach($tileTypes['rio'][4], 'cana-comun', 9);
        $attach($tileTypes['rio'][4], 'tierras-fertiles', 9);
        $attach($tileTypes['rio'][5], 'hidrogeno', 10);
        $attach($tileTypes['rio'][5], 'gases-naturales', 8);

        // ── PRADO ──────────────────────────────────────────────────
        $attach($tileTypes['prado'][1], 'lino', 5);
        $attach($tileTypes['prado'][2], 'lino', 8);
        $attach($tileTypes['prado'][2], 'yute', 8);
        $attach($tileTypes['prado'][3], 'lino', 8);
        $attach($tileTypes['prado'][3], 'yute', 8);
        $attach($tileTypes['prado'][3], 'canamo', 8);
        $attach($tileTypes['prado'][4], 'lino', 9);
        $attach($tileTypes['prado'][4], 'yute', 9);
        $attach($tileTypes['prado'][4], 'canamo', 9);
        $attach($tileTypes['prado'][4], 'lana', 9);
        $attach($tileTypes['prado'][5], 'tierras-fertiles', 8);

        // ── MINA ──────────────────────────────────────────────────
        $attach($tileTypes['mina'][1], 'cobre', 5);
        $attach($tileTypes['mina'][2], 'cobre', 8);
        $attach($tileTypes['mina'][2], 'hierro', 8);
        $attach($tileTypes['mina'][3], 'cobre', 10);
        $attach($tileTypes['mina'][3], 'hierro', 10);
        $attach($tileTypes['mina'][3], 'estano', 10);
        $attach($tileTypes['mina'][4], 'cobre', 12);
        $attach($tileTypes['mina'][4], 'hierro', 12);
        $attach($tileTypes['mina'][4], 'estano', 12);
        $attach($tileTypes['mina'][4], 'grafito', 12);
        $attach($tileTypes['mina'][5], 'oro', 8);
        $attach($tileTypes['mina'][5], 'mat-mag-nat', 8);
    }
}
