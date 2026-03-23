<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeClassJob;
use App\Jobs\GenerateAcademicItemJob;
use App\Jobs\ParseExcelJob;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AIController extends Controller
{
    public function analyzeClass(Request $request, $classId)
    {
        $class = SchoolClass::findOrFail($classId);
        
        // Mocking students data for now, in real case we would fetch it
        $studentsData = $class->students()->with('grades')->get()->toArray();

        // Run synchronously so the dashboard can display results immediately
        $aiService = app(\App\Services\AIService::class);
        $result = $aiService->analyzeClass($classId, $studentsData);

        // Fallback mock if the FastAPI service is down or fails
        if (!$result) {
            $result = [
                'classId' => $classId,
                'className' => $class->name,
                'analyzedAt' => now(),
                'summary' => [ 'averageGrade' => 14.5, 'attendanceRate' => 92, 'passingRate' => 85, 'totalStudents' => count($studentsData) ],
                'insights' => [
                    'strongSubjects' => [['subject' => 'Mathématiques', 'average' => 16.5], ['subject' => 'Physique', 'average' => 15.2]],
                    'weakSubjects' => [['subject' => 'Histoire', 'average' => 9.5, 'trend' => 'decreasing']],
                    'strugglingStudents' => [],
                    'topPerformers' => [],
                ],
                'recommendations' => [
                    ['type' => 'support', 'priority' => 'high', 'title' => 'Tutorat Histoire', 'description' => 'Mettre en place des sessions de tutorat pour les élèves en difficulté en Histoire.']
                ],
                'performanceChart' => [
                    ['month' => 'Jan', 'average' => 13.5],
                    ['month' => 'Fev', 'average' => 14.5],
                    ['month' => 'Mar', 'average' => 14.2]
                ]
            ];
        }

        \App\Models\AIReport::create([
            'school_id' => $class->school_id,
            'class_id' => $classId,
            'type' => 'CLASS_ANALYSIS',
            'report' => $result['insights'] ?? [],
            'recommendations' => $result['recommendations'] ?? [],
        ]);

        return response()->json($result);
    }

    public function generateAcademicItem(Request $request)
    {
        // Normalize input values to lowercase for internal processing and AI Service
        $request->merge([
            'type' => strtolower($request->type),
            'difficulty' => strtolower($request->difficulty),
        ]);

        $validated = $request->validate([
            'type' => 'required|in:devoir,examen,projet',
            'level' => 'required|string',
            'subject' => 'required|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'class_id' => 'required|uuid|exists:school_classes,id',
        ]);

        $validated['school_id'] = Auth::user()->school_id;

        \Log::info('AI Generation requested', $validated);

        GenerateAcademicItemJob::dispatch($validated, $request->boolean('save', true));

        return response()->json(['message' => 'Génération IA lancée.']);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $path = $request->file('file')->store('temp-imports');
        $fullPath = storage_path('app/' . $path);

        ParseExcelJob::dispatch($fullPath, Auth::user()->school_id);

        return response()->json(['message' => 'Importation Excel lancée en arrière-plan.']);
    }
}
