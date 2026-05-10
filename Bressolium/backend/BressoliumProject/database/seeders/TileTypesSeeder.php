<?php

namespace Database\Seeders;

use App\Models\TileType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TileTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TileType::create([
            'id' => Str::uuid(),
            'name' => 'Forest',
            'level' => 1,
        ]);

        TileType::create([
            'id' => Str::uuid(),
            'name' => 'Quarry',
            'level' => 1,
        ]);
    }
}
