<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
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
            'school_id'     => School::factory(),
            'class_id'      => SchoolClass::factory(),
            'first_name'    => $this->faker->firstName(),
            'last_name'     => $this->faker->lastName(),
            'email'         => $this->faker->unique()->safeEmail(),
            'matricule'     => strtoupper($this->faker->unique()->bothify('??###??')),
            'gender'        => $this->faker->randomElement(['MASCULIN', 'FEMININ']),
            'date_of_birth' => $this->faker->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d'),
        ];
    }
}
