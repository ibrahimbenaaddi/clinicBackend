<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date_birth' => $this->faker->date('Y-m-d'),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'insurance_info' => $this->faker->company() . ' Insurance',
        ];
    }
}
