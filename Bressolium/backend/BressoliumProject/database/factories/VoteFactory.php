<?php

namespace Database\Factories;

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
            'round_id'      => \App\Models\Round::factory(),
            'user_id'       => \App\Models\User::factory(),
            // Mocking a UUID for technology since the model and migration are scheduled for Task 14
            'technology_id' => $this->faker->uuid(),
        ];
    }
}
