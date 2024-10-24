<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
          return [
            'name' => fake()->name(),
            'student_id' => fake()->randomDigit(),
            'address_1' => fake()->country(),
            'address_2' => fake()->streetAddress(),

        ];
    }
}
