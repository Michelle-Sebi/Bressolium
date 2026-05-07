<?php

namespace Database\Factories;

use App\Models\Technology;
use Illuminate\Database\Eloquent\Factories\Factory;

class TechnologyFactory extends Factory
{
    protected $model = Technology::class;

    public function definition(): array
    {
        return [
            'name'            => $this->faker->unique()->words(2, true),
            'prerequisite_id' => null,
        ];
    }
}
