<?php

namespace Database\Factories;

use App\Models\Round;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vote>
 */
class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'round_id' => Round::factory(),
            'user_id' => User::factory(),
            'technology_id' => $this->faker->uuid(),
            'invention_id' => null,
        ];
    }
}
