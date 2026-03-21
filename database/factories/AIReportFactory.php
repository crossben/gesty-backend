<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\AIReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AIReport>
 */
class AIReportFactory extends Factory
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
            'type' => $this->faker->randomElement(['CLASS_ANALYSIS', 'GENERATE_ITEM']),
            'report' => ['summary' => $this->faker->paragraph()],
            'recommendations' => [$this->faker->sentence(), $this->faker->sentence()],
        ];
    }
}
