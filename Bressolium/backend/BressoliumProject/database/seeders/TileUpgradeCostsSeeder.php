<?php

namespace Database\Seeders;

use App\Models\Invention;
use App\Models\Material;
use App\Models\Technology;
use App\Models\TileType;
use Illuminate\Database\Seeder;

/**
 * Costes para subir de nivel cada tipo de casilla.
 * tile_type_id referencia el nivel ACTUAL (origen), no el destino.
 * Los materiales usados son siempre producibles en ese nivel o inferior,
 * eliminando el problema chicken-and-egg del diseño anterior.
 */
class TileUpgradeCostsSeeder extends Seeder
{
    public function run(): void
    {
        $tech = fn (string $name) => Technology::firstOrCreate(['name' => $name])->id;
        $inv  = fn (string $name) => Invention::firstOrCreate(['name' => $name])->id;

        $attach = function (TileType $tile, string $matName, int $qty, ?string $techId = null, ?string $invId = null) {
            $material = Material::where('name', $matName)->first();
            if (! $material) {
                return;
            }
            $tile->upgradeCosts()->syncWithoutDetaching([
                $material->id => [
                    'quantity'             => $qty,
                    'tech_required'        => $techId,
                    'invention_required'   => $invId,
                ],
            ]);
        };

        $types = [];
        foreach (['bosque', 'cantera', 'rio', 'prado', 'mina'] as $base) {
            for ($level = 1; $level <= 4; $level++) {
                $types[$base][$level] = TileType::where('base_type', $base)
                    ->where('level', $level)
                    ->first();
            }
        }

        // ── BOSQUE (coste FROM nivel N para subir a N+1) ────────────
        $attach($types['bosque'][1], 'roble', 10);

        $attach($types['bosque'][2], 'roble', 8);
        $attach($types['bosque'][2], 'pino',  8);

        $attach($types['bosque'][3], 'roble',          8, $tech('Ganadería'));
        $attach($types['bosque'][3], 'pino',           8, $tech('Ganadería'));
        $attach($types['bosque'][3], 'carbon-natural', 8, $tech('Ganadería'));

        $attach($types['bosque'][4], 'roble',          9, $tech('Química'));
        $attach($types['bosque'][4], 'pino',           9, $tech('Química'));
        $attach($types['bosque'][4], 'carbon-natural', 9, $tech('Química'));
        $attach($types['bosque'][4], 'pieles',         9, $tech('Química'));

        // ── CANTERA ─────────────────────────────────────────────────
        $attach($types['cantera'][1], 'silex', 10);

        $attach($types['cantera'][2], 'silex',   8);
        $attach($types['cantera'][2], 'granito', 8);

        $attach($types['cantera'][3], 'silex',    8, $tech('Cerámica y Alfarería'));
        $attach($types['cantera'][3], 'granito',  8, $tech('Cerámica y Alfarería'));
        $attach($types['cantera'][3], 'obsidiana', 8, $tech('Cerámica y Alfarería'));

        $attach($types['cantera'][4], 'silex',    9, $tech('Herramientas de Piedra'), $inv('Hacha'));
        $attach($types['cantera'][4], 'granito',  9, $tech('Herramientas de Piedra'), $inv('Hacha'));
        $attach($types['cantera'][4], 'obsidiana', 9, $tech('Herramientas de Piedra'), $inv('Hacha'));

        // ── RÍO ─────────────────────────────────────────────────────
        $attach($types['rio'][1], 'agua', 10);

        $attach($types['rio'][2], 'agua',      8);
        $attach($types['rio'][2], 'cana-comun', 8);

        $attach($types['rio'][3], 'agua',             8, $tech('Agricultura'));
        $attach($types['rio'][3], 'cana-comun',       8, $tech('Agricultura'));
        $attach($types['rio'][3], 'tierras-fertiles', 8, $tech('Agricultura'));

        $attach($types['rio'][4], 'agua',             9, $tech('Química'));
        $attach($types['rio'][4], 'cana-comun',       9, $tech('Química'));
        $attach($types['rio'][4], 'tierras-fertiles', 9, $tech('Química'));

        // ── PRADO ────────────────────────────────────────────────────
        $attach($types['prado'][1], 'lino', 10);

        $attach($types['prado'][2], 'lino', 8);
        $attach($types['prado'][2], 'yute', 8);

        $attach($types['prado'][3], 'lino',  8, $tech('Ganadería'));
        $attach($types['prado'][3], 'yute',  8, $tech('Ganadería'));
        $attach($types['prado'][3], 'canamo', 8, $tech('Ganadería'));

        $attach($types['prado'][4], 'lino',  9, $tech('Conservación de Alimentos'), $inv('Arado'));
        $attach($types['prado'][4], 'yute',  9, $tech('Conservación de Alimentos'), $inv('Arado'));
        $attach($types['prado'][4], 'canamo', 9, $tech('Conservación de Alimentos'), $inv('Arado'));
        $attach($types['prado'][4], 'lana',  9, $tech('Conservación de Alimentos'), $inv('Arado'));

        // ── MINA ─────────────────────────────────────────────────────
        $attach($types['mina'][1], 'cobre', 10, $tech('Herramientas de Piedra'));

        $attach($types['mina'][2], 'cobre',  8, $tech('Metalurgia y Aleaciones'));
        $attach($types['mina'][2], 'hierro', 8, $tech('Metalurgia y Aleaciones'));

        $attach($types['mina'][3], 'cobre',  10, $tech('Metalurgia y Aleaciones'));
        $attach($types['mina'][3], 'hierro', 10, $tech('Metalurgia y Aleaciones'));
        $attach($types['mina'][3], 'estano', 10, $tech('Metalurgia y Aleaciones'));

        $attach($types['mina'][4], 'cobre',  12, $tech('Metalurgia y Aleaciones'), $inv('Brújula'));
        $attach($types['mina'][4], 'hierro', 12, $tech('Metalurgia y Aleaciones'), $inv('Brújula'));
        $attach($types['mina'][4], 'estano', 12, $tech('Metalurgia y Aleaciones'), $inv('Brújula'));
        $attach($types['mina'][4], 'grafito', 12, $tech('Metalurgia y Aleaciones'), $inv('Brújula'));
    }
}
