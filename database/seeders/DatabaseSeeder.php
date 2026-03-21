<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\AcademicItem;
use App\Models\Grade;
use App\Models\AIReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a Primary School
        $school = School::factory()->create([
            'name' => 'Université Cheikh Anta Diop',
            'slug' => 'ucad',
        ]);

        // 2. Create an Admin User for this school
        $admin = User::factory()->create([
            'name' => 'Admin Gesty',
            'email' => 'admin@gesty.sn',
            'password' => Hash::make('password'),
            'school_id' => $school->id,
        ]);

        // 3. Create Classes
        $classes = SchoolClass::factory()->count(5)->create([
            'school_id' => $school->id,
        ]);

        foreach ($classes as $class) {
            // 4. Create Students for each class
            $students = Student::factory()->count(20)->create([
                'school_id' => $school->id,
                'class_id' => $class->id,
            ]);

            // 5. Create Academic Items (Evaluations) for each class
            $items = AcademicItem::factory()->count(4)->create([
                'school_id' => $school->id,
                'class_id' => $class->id,
            ]);

            foreach ($items as $item) {
                // 6. Create Grades for each student in each academic item
                foreach ($students as $student) {
                    Grade::factory()->create([
                        'school_id' => $school->id,
                        'student_id' => $student->id,
                        'academic_item_id' => $item->id,
                    ]);
                }
            }

            // 7. Create some AI reports
            AIReport::factory()->count(2)->create([
                'school_id' => $school->id,
                'class_id' => $class->id,
            ]);
        }

        // 8. Create another school to test multi-tenancy isolation
        $otherSchool = School::factory()->create(['name' => 'Other University']);
        User::factory()->create([
            'email' => 'other@gesty.sn',
            'school_id' => $otherSchool->id,
        ]);
        SchoolClass::factory()->count(2)->create(['school_id' => $otherSchool->id]);
    }
}
