<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TechInventionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $woodId = Str::uuid()->toString();
        DB::table('materials')->insert([
            'id' => $woodId,
            'name' => 'Wood',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $wheelId = Str::uuid()->toString();
        DB::table('technologies')->insert([
            'id' => $wheelId,
            'name' => 'Wheel',
            'prerequisite_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cartId = Str::uuid()->toString();
        DB::table('inventions')->insert([
            'id' => $cartId,
            'name' => 'Cart',
            'technology_id' => $wheelId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Stock inicial en game_material (is_active: false para no descubiertos) - Wait, game_material has no is_active column. The ticket says: "stock inicial en game_material (is_active: false para no descubiertos)". Ah, the `is_active` false is for game_technology and game_invention.
    }
}
