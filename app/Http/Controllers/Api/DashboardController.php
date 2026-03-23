<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Grade;
use App\Models\AcademicItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'totalStudents' => Student::count(),
            'totalClasses' => SchoolClass::count(),
            'totalAdmins' => 1,
            'pendingItems' => AcademicItem::where('due_date', '>', now())->count(),
            'averageGrade' => round(Grade::avg('score') ?? 0, 2),
            'attendanceRate' => 95,
        ]);
    }

    public function charts()
    {
        // 1. Students by Level (Actual student count)
        $studentsByLevel = SchoolClass::join('students', 'school_classes.id', '=', 'students.class_id')
            ->select('school_classes.level', DB::raw('count(students.id) as count'))
            ->groupBy('school_classes.level')
            ->get()
            ->map(fn($item) => ['name' => $item->level, 'value' => (int)$item->count]);

        // 2. Performance by Subject
        $performanceBySubject = AcademicItem::select(
                'academic_items.subject', 
                DB::raw('AVG(grades.score) as average'), 
                DB::raw('COUNT(grades.id) as grade_count'),
                DB::raw('SUM(CASE WHEN grades.score >= 10 THEN 1 ELSE 0 END) as passing_count')
            )
            ->leftJoin('grades', 'academic_items.id', '=', 'grades.academic_item_id')
            ->groupBy('academic_items.subject')
            ->having(DB::raw('COUNT(grades.id)'), '>', 0)
            ->get()
            ->map(fn($item) => [
                'subject' => $item->subject,
                'average' => round((float)$item->average, 1),
                'passing' => $item->grade_count > 0 ? round(($item->passing_count / $item->grade_count) * 100, 1) : 0
            ]);

        // 3. Grades Distribution (The missing chart!)
        // Buckets: 0-5, 5-10, 10-15, 15-20
        $gradesDistribution = [
            ['name' => '0-5', 'value' => Grade::whereBetween('score', [0, 5])->count()],
            ['name' => '5-10', 'value' => Grade::whereBetween('score', [5, 10])->count()],
            ['name' => '10-15', 'value' => Grade::whereBetween('score', [10, 15])->count()],
            ['name' => '15-20', 'value' => Grade::whereBetween('score', [15, 20])->count()],
        ];

        return response()->json([
            'studentsByLevel' => $studentsByLevel,
            'performanceBySubject' => $performanceBySubject,
            'gradesDistribution' => $gradesDistribution
        ]);
    }
}
