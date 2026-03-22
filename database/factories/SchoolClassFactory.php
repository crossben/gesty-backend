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
    private static int $classCounter = 0;

    private static array $classData = [
        ['name' => 'Licence 1 Informatique', 'level' => 'L1', 'code' => 'L1-INFO', 'capacity' => 45],
        ['name' => 'Licence 2 Informatique', 'level' => 'L2', 'code' => 'L2-INFO', 'capacity' => 40],
        ['name' => 'Licence 3 Informatique', 'level' => 'L3', 'code' => 'L3-INFO', 'capacity' => 35],
        ['name' => 'Master 1 Génie Logiciel', 'level' => 'M1', 'code' => 'M1-GL',   'capacity' => 25],
        ['name' => 'Master 2 Intelligence Artificielle', 'level' => 'M2', 'code' => 'M2-IA', 'capacity' => 20],
        ['name' => 'Licence 1 Mathématiques', 'level' => 'L1', 'code' => 'L1-MATH', 'capacity' => 50],
        ['name' => 'Licence 2 Mathématiques', 'level' => 'L2', 'code' => 'L2-MATH', 'capacity' => 42],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $idx = self::$classCounter % count(self::$classData);
        self::$classCounter++;
        $preset = self::$classData[$idx];

        return [
            'school_id'     => School::factory(),
            'name'          => $preset['name'],
            'level'         => $preset['level'],
            'code'          => $preset['code'],
            'capacity'      => $preset['capacity'],
            'academic_year' => '2024-2025',
            'description'   => 'Classe de ' . $preset['name'],
            'is_active'     => true,
        ];
    }
}
