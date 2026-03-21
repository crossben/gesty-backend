<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Student;
use App\Models\AcademicItem;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
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
            'student_id' => Student::factory(),
            'academic_item_id' => AcademicItem::factory(),
            'score' => $this->faker->randomFloat(2, 0, 20),
            'max_score' => 20,
            'comments' => $this->faker->sentence(),
            'graded_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
