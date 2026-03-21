<?php

namespace Database\Factories;

use App\Models\AcademicItem;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicItem>
 */
class AcademicItemFactory extends Factory
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
            'class_id' => SchoolClass::factory(),
            'type' => $this->faker->randomElement(['DEVOIR', 'EXAMEN', 'PROJET']),
            'subject' => $this->faker->randomElement(['Math', 'Physique', 'Informatique', 'Anglais']),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'max_score' => 20,
        ];
    }
}
