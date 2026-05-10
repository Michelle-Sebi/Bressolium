<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TechnologiesBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wheelId = Str::uuid()->toString();
        DB::table('technologies')->insert([
            'id' => $wheelId,
            'name' => 'Wheel',
            'prerequisite_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mathId = Str::uuid()->toString();
        DB::table('technologies')->insert([
            'id' => $mathId,
            'name' => 'Mathematics',
            'prerequisite_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Also add Cart so old stuff doesn't completely break, even though not explicitly asserted here.
        $cartId = Str::uuid()->toString();
        DB::table('inventions')->insert([
            'id' => $cartId,
            'name' => 'Cart',
            'technology_id' => $wheelId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
