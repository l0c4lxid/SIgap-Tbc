<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\UserDetail>
 */
class UserDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nik' => fake()->numerify('################'),

            'address' => fake()->address(),
            'organization' => fake()->company(),
            'notes' => fake()->sentence(),
            'family_card_number' => fake()->numerify('################'),
            'supervisor_id' => null,
        ];
    }
}
