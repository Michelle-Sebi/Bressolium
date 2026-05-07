<?php

namespace Database\Factories;

use App\Models\Invention;
use App\Models\Technology;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventionFactory extends Factory
{
    protected $model = Invention::class;

    public function definition(): array
    {
        return [
            'name'          => $this->faker->unique()->words(2, true),
            'technology_id' => Technology::factory(),
            'is_final'      => false,
        ];
    }
}
