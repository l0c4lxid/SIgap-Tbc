<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientScreeningFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kader_id' => User::factory()->state(['role' => 'kader']),
            'patient_is_wni' => true,
            'patient_name' => fake()->name(),
            'patient_nik' => fake()->numerify('16##############'),
            'patient_phone' => fake()->numerify('08##########'),
            'patient_address' => fake()->streetAddress(),
            'patient_gender' => fake()->randomElement(['L', 'P']),
            'patient_birth_place' => fake()->city(),
            'patient_birth_date' => fake()->date(),
            'patient_age' => fake()->numberBetween(1, 90),
            'patient_address_ktp' => fake()->address(),
            'patient_address_domisili' => fake()->address(),
            'patient_address_rt' => fake()->numerify('0##'),
            'patient_address_rw' => fake()->numerify('0##'),
            'patient_address_kelurahan' => 'Kelurahan ' . fake()->word(),
            'patient_weight' => fake()->randomFloat(1, 30, 100),
            'patient_height' => fake()->randomFloat(1, 100, 200),
            'latitude' => -7.5,
            'longitude' => 110.8,
            'answers' => [],
            'notes' => 'Test notes',
        ];
    }
}
