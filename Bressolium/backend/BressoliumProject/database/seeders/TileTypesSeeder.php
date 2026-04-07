<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TileTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\TileType::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Forest',
            'level' => 1
        ]);

        \App\Models\TileType::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Quarry',
            'level' => 1
        ]);
    }
}