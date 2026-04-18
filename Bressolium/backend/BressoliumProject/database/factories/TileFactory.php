<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Tile;
use App\Models\TileType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TileFactory extends Factory
{
    protected $model = Tile::class;

    public function definition(): array
    {
        return [
            'game_id'      => Game::factory(),
            'tile_type_id' => TileType::factory(),
            'coord_x'      => $this->faker->numberBetween(0, 14),
            'coord_y'      => $this->faker->numberBetween(0, 14),
            'explored'     => false,
        ];
    }
}
