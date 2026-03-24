<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\AcademicItem;
use App\Models\Grade;
use App\Models\Announcement;
use App\Models\Schedule;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Create Roles
        Role::create(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
        Role::create(['name' => 'ADMIN',       'guard_name' => 'web']);
        Role::create(['name' => 'MANAGER',     'guard_name' => 'web']);

        // 1. Create the main school
        $school = School::create([
            'name' => 'Université de Technologie Avancée',
            'slug' => 'uta',
            'is_active' => true,
        ]);

        // 2. Create Admin and Manager users
        $admin = User::create([
            'name'      => 'Admin Principal',
            'email'     => 'admin@gesty.sn',
            'password'  => Hash::make('password'),
            'school_id' => $school->id,
        ]);
        $admin->assignRole('ADMIN');

        $manager = User::create([
            'name'      => 'Jean-Pierre Ndiaye',
            'email'     => 'manager@gesty.sn',
            'password'  => Hash::make('password'),
            'school_id' => $school->id,
        ]);
        $manager->assignRole('MANAGER');

        // 3. Create classes with rich data
        $schoolPrefix = 'UTA';   // Used for matricule generation
        $classesData = [
            ['name' => 'Licence 1 Informatique',                 'level' => 'L1', 'code' => 'L1-INFO', 'capacity' => 45, 'academic_year' => '2024-2025'],
            ['name' => 'Licence 2 Informatique',                 'level' => 'L2', 'code' => 'L2-INFO', 'capacity' => 40, 'academic_year' => '2024-2025'],
            ['name' => 'Licence 3 Informatique',                 'level' => 'L3', 'code' => 'L3-INFO', 'capacity' => 35, 'academic_year' => '2024-2025'],
            ['name' => 'Master 1 Génie Logiciel',                'level' => 'M1', 'code' => 'M1-GL',   'capacity' => 25, 'academic_year' => '2024-2025'],
            ['name' => 'Master 2 Intelligence Artificielle',     'level' => 'M2', 'code' => 'M2-IA',   'capacity' => 20, 'academic_year' => '2024-2025'],
            ['name' => 'Licence 1 Mathématiques',                'level' => 'L1', 'code' => 'L1-MATH', 'capacity' => 48, 'academic_year' => '2024-2025'],
            ['name' => 'Doctorat Systèmes Distribués',           'level' => 'DOCTORAT', 'code' => 'DOC-SD', 'capacity' => 10, 'academic_year' => '2024-2025'],
        ];

        $subjects = [
            'Algorithmique', 'Programmation Orientée Objet', 'Bases de données',
            'Réseaux', 'Systèmes d\'exploitation', 'Mathématiques Discrètes',
            'Architecture des Ordinateurs', 'Intelligence Artificielle',
        ];

        $firstNames = ['Amadou', 'Fatou', 'Ibrahima', 'Mariama', 'Moussa', 'Aissatou', 'Omar', 'Ndeye',
                       'Cheikh', 'Rokhaya', 'Alioune', 'Bineta', 'Seydou', 'Marième', 'Pape', 'Sokhna',
                       'Saliou', 'Khady', 'Modou', 'Coumba'];
        $lastNames  = ['Diallo', 'Ndiaye', 'Fall', 'Diouf', 'Sow', 'Ba', 'Sarr', 'Mbaye',
                       'Niang', 'Cissé', 'Diop', 'Thiaw', 'Faye', 'Guèye', 'Toure', 'Kane',
                       'Badji', 'Camara', 'Manga', 'Konaté'];

        $matriculeCounter = 1;

        $classes = [];
        foreach ($classesData as $classInfo) {
            $classes[] = SchoolClass::create([
                'school_id'     => $school->id,
                'name'          => $classInfo['name'],
                'level'         => $classInfo['level'],
                'code'          => $classInfo['code'],
                'capacity'      => $classInfo['capacity'],
                'academic_year' => $classInfo['academic_year'],
                'description'   => 'Classe de ' . $classInfo['name'] . ' - Année académique ' . $classInfo['academic_year'],
                'is_active'     => true,
            ]);
        }

        $allStudents = [];
        foreach ($classes as $class) {
            $studentCount = min($class->capacity - 5, 15);  // realistic fill

            // Create academic items for this class
            $itemTypes = ['DEVOIR', 'EXAMEN', 'PROJET'];
            $difficulties = ['EASY', 'MEDIUM', 'HARD'];
            $items = [];
            foreach ($subjects as $i => $subject) {
                if ($i >= 4) break;  // 4 items per class
                $type = $itemTypes[$i % 3];
                $items[] = AcademicItem::create([
                    'school_id'   => $school->id,
                    'class_id'    => $class->id,
                    'type'        => $type,
                    'subject'     => $subject,
                    'title'       => $type . ' - ' . $subject,
                    'description' => 'Évaluation de ' . $subject . ' pour la classe ' . $class->name,
                    'due_date'    => now()->addDays(rand(7, 60)),
                    'max_score'   => 20,
                ]);
            }

            // Create students
            $students = [];
            for ($s = 0; $s < $studentCount; $s++) {
                $fn = $firstNames[$s % count($firstNames)];
                $ln = $lastNames[$s % count($lastNames)];
                $gender = $s % 2 === 0 ? 'MASCULIN' : 'FEMININ';
                $matricule = $schoolPrefix . '2024' . str_pad($matriculeCounter, 5, '0', STR_PAD_LEFT);
                $matriculeCounter++;

                $student = Student::create([
                    'school_id'     => $school->id,
                    'class_id'      => $class->id,
                    'first_name'    => $fn,
                    'last_name'     => $ln,
                    'email'         => strtolower($fn . '.' . $ln . $matriculeCounter . '@email.com'),
                    'matricule'     => $matricule,
                    'gender'        => $gender,
                    'date_of_birth' => now()->subYears(rand(18, 28))->subDays(rand(0, 365))->format('Y-m-d'),
                ]);

                $students[] = $student;
                $allStudents[] = $student;
            }

            // Create grades for each student & academic item
            foreach ($items as $item) {
                foreach ($students as $student) {
                    $maxScore = 20;
                    $score = round(rand(5, 200) / 10, 1);  // 0.5 to 20
                    Grade::create([
                        'school_id'        => $school->id,
                        'student_id'       => $student->id,
                        'academic_item_id' => $item->id,
                        'score'            => min($score, $maxScore),
                        'max_score'        => $maxScore,
                        'comments'         => rand(0, 1) ? "test" : null,
                        'graded_at'        => now()->subDays(rand(1, 30)),
                    ]);
                }
            }

            // Create schedule entries for this class
            $days = ['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI'];
            $sessionTypes = ['COURSE', 'TD', 'TP'];
            foreach ($days as $i => $day) {
                if ($i >= 3) break; // 3 days per class
                $sessType = $sessionTypes[$i % 3];
                $subj = $subjects[$i % count($subjects)];
                Schedule::create([
                    'school_id'   => $school->id,
                    'class_id'    => $class->id,
                    'subject'     => $subj,
                    'day_of_week' => $day,
                    'start_time'  => '08:00',
                    'end_time'    => '10:00',
                    'room'        => 'Salle ' . (100 + $i + 1),
                    'teacher'     => 'Dr. ' . $lastNames[$i % count($lastNames)],
                    'type'        => $sessType,
                    'is_active'   => true,
                ]);

                Schedule::create([
                    'school_id'   => $school->id,
                    'class_id'    => $class->id,
                    'subject'     => $subjects[($i + 2) % count($subjects)],
                    'day_of_week' => $day,
                    'start_time'  => '10:30',
                    'end_time'    => '12:30',
                    'room'        => 'Amphi A',
                    'teacher'     => 'Prof. ' . $lastNames[($i + 3) % count($lastNames)],
                    'type'        => 'COURSE',
                    'is_active'   => true,
                ]);
            }
        }

        // Create announcements of each priority type
        $announcementData = [
            ['title' => 'Bienvenue à l\'année académique 2024-2025',          'priority' => 'NORMAL', 'content' => 'Nous souhaitons la bienvenue à tous les étudiants et membres du personnel pour cette nouvelle année académique.'],
            ['title' => 'Examens de mi-semestre - Dates importantes',          'priority' => 'HIGH',   'content' => 'Les examens de mi-semestre auront lieu du 15 au 22 avril 2025. Veuillez vous préparer en conséquence.'],
            ['title' => 'URGENT : Maintenance du système informatique',        'priority' => 'URGENT', 'content' => 'Le système sera indisponible ce weekend du vendredi 18h au dimanche 20h pour maintenance critique.'],
            ['title' => 'Réunion pédagogique - Convocation',                   'priority' => 'HIGH',   'content' => 'Tous les étudiants de Licence 3 sont convoqués à une réunion pédagogique le 10 avril à 14h en salle E201.'],
            ['title' => 'Rappel : Dépôt des mémoires de Master',               'priority' => 'URGENT', 'content' => 'La date limite de dépôt des mémoires de Master 2 est fixée au 30 juin 2025. Aucun délai supplémentaire ne sera accordé.'],
            ['title' => 'Ateliers de préparation aux stages',                  'priority' => 'NORMAL', 'content' => 'Des ateliers de préparation aux stages et à l\'insertion professionnelle seront organisés tous les jeudis de 16h à 18h.'],
            ['title' => 'Mise à jour du règlement intérieur',                  'priority' => 'LOW',    'content' => 'Le règlement intérieur a été mis à jour. Vous pouvez le consulter dans l\'espace étudiant en ligne.'],
            ['title' => 'Journée portes ouvertes - 5 avril 2025',              'priority' => 'NORMAL', 'content' => 'L\'université organise une journée portes ouvertes le samedi 5 avril. Votre présence est encouragée pour accueillir les futurs étudiants.'],
        ];

        foreach ($announcementData as $idx => $ann) {
            Announcement::create([
                'school_id' => $school->id,
                'author_id' => $idx % 2 === 0 ? $admin->id : $manager->id,
                'class_id'  => $idx % 3 === 0 ? $classes[$idx % count($classes)]->id : null,
                'title'     => $ann['title'],
                'content'   => $ann['content'],
                'priority'  => $ann['priority'],
                'expires_at'=> $idx % 3 === 2 ? now()->addDays(30) : null,
            ]);
        }

        // 8. Create another school to test multi-tenancy isolation
        $otherSchool = School::create(['name' => 'Autre Université', 'slug' => 'autre-uni']);
        User::create([
            'name'      => 'Admin Autre',
            'email'     => 'other@gesty.sn',
            'password'  => Hash::make('password'),
            'school_id' => $otherSchool->id,
        ]);
        SchoolClass::create([
            'school_id'     => $otherSchool->id,
            'name'          => 'Classe Isolée',
            'level'         => 'L1',
            'code'          => 'ISO-01',
            'capacity'      => 30,
            'academic_year' => '2024-2025',
            'is_active'     => true,
        ]);

        // 9. Create a SUPER_ADMIN for global platform management
        User::create([
            'name'      => 'Gesty Super Admin',
            'email'     => 'superadmin@gesty.sn',
            'password'  => Hash::make('password'),
            'school_id' => null, // Super Admins are not tied to a school
        ])->assignRole('SUPER_ADMIN');

        $this->command->info("✅ Seeded: 1 Super Admin (superadmin@gesty.sn), 2 schools, {$admin->name} (admin@gesty.sn), " . count($classes) . " classes, " . count($allStudents) . " students, grades, schedules, " . count($announcementData) . " announcements.");
    }
}
