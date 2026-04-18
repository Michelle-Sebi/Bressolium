<?php

namespace Database\Factories;

use App\Models\TileType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TileTypeFactory extends Factory
{
    protected $model = TileType::class;

    public function definition(): array
    {
        return [
            'name'      => $this->faker->word(),
            'level'     => $this->faker->numberBetween(1, 5),
            'base_type' => $this->faker->randomElement(['bosque', 'cantera', 'rio', 'prado', 'mina', 'pueblo']),
        ];
    }
}
