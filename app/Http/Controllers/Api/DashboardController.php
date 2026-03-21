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
            'totalAdmins' => 1, // Placeholder for now
            'pendingItems' => AcademicItem::where('due_date', '>', now())->count(),
            'averageGrade' => round(Grade::avg('score') ?? 0, 2),
            'attendanceRate' => 95, // Placeholder
        ]);
    }

    public function charts()
    {
        $studentsByLevel = SchoolClass::select('level', DB::raw('count(*) as count'))
            ->groupBy('level')
            ->get()
            ->map(fn($item) => ['name' => $item->level, 'value' => (int)$item->count]);

        $performanceBySubject = AcademicItem::select('academic_items.subject', DB::raw('AVG(grades.score) as average'), DB::raw('SUM(CASE WHEN grades.score >= 10 THEN 1 ELSE 0 END) * 100.0 / COUNT(grades.id) as passing'))
            ->leftJoin('grades', 'academic_items.id', '=', 'grades.academic_item_id')
            ->groupBy('academic_items.subject')
            ->get()
            ->map(fn($item) => [
                'subject' => $item->subject,
                'average' => round((float)$item->average, 1),
                'passing' => round((float)$item->passing, 1)
            ]);

        return response()->json([
            'studentsByLevel' => $studentsByLevel,
            'performanceBySubject' => $performanceBySubject,
        ]);
    }
}
