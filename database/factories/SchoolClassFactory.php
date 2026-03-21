<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'name' => $this->faker->word() . ' ' . $this->faker->randomDigitNotNull(),
            'level' => $this->faker->randomElement(['L1', 'L2', 'L3', 'M1', 'M2']),
            'description' => $this->faker->sentence(),
        ];
    }
}
