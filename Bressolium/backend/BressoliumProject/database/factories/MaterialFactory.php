<?php

namespace Database\Factories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return [
            'name'  => $this->faker->unique()->word(),
            'tier'  => $this->faker->numberBetween(0, 5),
            'group' => $this->faker->randomElement(['Bosque', 'Cantera', 'Río', 'Prado', 'Mina']),
        ];
    }
}
