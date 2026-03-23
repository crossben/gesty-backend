<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ParseExcelJob;
use App\Models\SchoolClass;
use App\Models\Grade;
use App\Models\AcademicItem;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\AIReport;
use Carbon\Carbon;

class AIController extends Controller
{
    /**
     * Main entry point for class performance analysis.
     */
    public function analyzeClass(Request $request, $class_id)
    {
        $class = SchoolClass::findOrFail($class_id);
        
        $studentsData = $this->getStudentDataForAI($class);
        Log::info("AI Analysis: sending " . count($studentsData) . " students for class {$class_id}");

        $aiService = app(AIService::class);
        $raw = $aiService->analyzeClass($class_id, $studentsData);

        if ($errorResponse = $this->handleAIServiceError($raw, $class_id)) {
            return $errorResponse;
        }

        $result = $this->transformAIResponse($class, $studentsData, $raw);

        AIReport::create([
            'school_id'       => $class->school_id,
            'class_id'        => $class_id,
            'type'            => 'CLASS_ANALYSIS',
            'report'          => $result['insights'],
            'recommendations' => $result['recommendations'],
        ]);

        return response()->json($result);
    }

    /**
     * Get a unified history of AI activities.
     */
    public function getHistory(Request $request)
    {
        $query = AIReport::orderBy('created_at', 'desc')->with('schoolClass');
        
        if ($request->has('classId')) {
            $query->where('class_id', $request->classId);
        }
        
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $paginator = $query->paginate($request->get('limit', 15));

        $paginator->getCollection()->transform(function ($report) {
            return [
                'id' => $report->id,
                'type' => strtoupper($report->type),
                'status' => 'COMPLETED',
                'classId' => $report->class_id,
                'className' => $report->schoolClass ? $report->schoolClass->name : 'Non spécifié',
                'input' => json_encode(['requested_at' => $report->created_at]),
                'output' => json_encode($report->type === 'CLASS_ANALYSIS' 
                    ? ['report' => $report->report, 'recommendations' => $report->recommendations]
                    : $report->report),
                'generatedByName' => 'System AI',
                'duration' => 2500,
                'createdAt' => $report->created_at,
            ];
        });

        return response()->json($paginator);
    }

    /**
     * Generates an academic item (Exam/Homework/Project) without saving by default.
     */
    public function generateAcademicItem(Request $request)
    {
        $data = $this->mapFields($request->all());

        if (isset($data['type']))       $data['type']       = strtoupper($data['type']);
        if (isset($data['difficulty'])) $data['difficulty'] = strtolower($data['difficulty']);

        $request->merge($data);

        $validated = $request->validate([
            'type'       => 'required|in:DEVOIR,EXAMEN,PROJET',
            'level'      => 'required|string',
            'subject'    => 'required|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'class_id'   => 'required|uuid|exists:school_classes,id',
        ]);

        $validated['school_id'] = Auth::user()->school_id;

        $aiService = app(AIService::class);
        $result = $aiService->generateAcademicItem($validated);

        if (!$result) {
            return response()->json(['error' => true, 'message' => 'Le service de génération IA est inaccessible.'], 503);
        }

        // Always log to history
        AIReport::create([
            'school_id' => $validated['school_id'],
            'class_id' => $validated['class_id'],
            'type' => 'GENERATE_' . $validated['type'],
            'report' => $result, // Store the full content for history preview
            'recommendations' => [],
        ]);

        // Auto-save if explicitly requested
        if ($request->boolean('save', false)) {
            $this->storeGeneratedItem($validated, $result);
        }

        return response()->json($result);
    }

    /**
     * Analyzes all school schedules for conflicts and quality issues.
     */
    public function analyzeScheduleConflicts(Request $request)
    {
        $user = Auth::user();
        $schedules = \App\Models\Schedule::with('schoolClass')
            ->where('school_id', $user->school_id)
            ->where('is_active', true)
            ->get();

        if ($schedules->isEmpty()) {
            return response()->json([
                'error' => true,
                'message' => "Aucun emploi du temps trouvé pour cette école.",
            ], 404);
        }

        // Format for AI service
        $payload = $schedules->map(function ($s) {
            return [
                'id'           => $s->id,
                'class_id'     => $s->class_id,
                'class_name'   => $s->schoolClass?->name ?? 'Classe inconnue',
                'subject'      => $s->subject,
                'day_of_week'  => $s->day_of_week,
                'start_time'   => $s->start_time,
                'end_time'     => $s->end_time,
                'room'         => $s->room,
                'teacher'      => $s->teacher,
                'type'         => $s->type,
            ];
        })->values()->toArray();

        $aiService = app(AIService::class);
        $result = $aiService->analyzeScheduleConflicts($payload);

        if (!$result) {
            return response()->json([
                'error' => true,
                'message' => 'Le service d\'analyse IA est inaccessible.',
            ], 503);
        }

        // Log to AIReport
        AIReport::create([
            'school_id'       => $user->school_id,
            'class_id'        => null,
            'type'            => 'SCHEDULE_CONFLICT',
            'report'          => $result,
            'recommendations' => $result['conflicts'] ?? [],
        ]);

        return response()->json($result);
    }

    /**
     * Formally saves a previously generated item.
     */
    public function saveGeneratedItem(Request $request)
    {
        $validated = $request->validate([
            'type'       => 'required|in:devoir,examen,projet,DEVOIR,EXAMEN,PROJET',
            'subject'    => 'required|string',
            'title'      => 'required|string',
            'classId'    => 'required|uuid|exists:school_classes,id',
            'content'    => 'required|array', // The full generated JSON object
        ]);

        // Wrap mapping to match storeGeneratedItem expectation
        $meta = [
            'school_id' => Auth::user()->school_id,
            'class_id' => $validated['classId'],
            'type' => strtoupper($validated['type']),
            'subject' => $validated['subject'],
        ];

        $item = $this->storeGeneratedItem($meta, $validated['content']);

        return response()->json(['message' => 'Contenu enregistré avec succès', 'id' => $item->id]);
    }

    /**
     * Internal helper to persist an AcademicItem.
     */
    private function storeGeneratedItem(array $meta, array $aiResult): AcademicItem
    {
        $description = $aiResult['description'] ?? $aiResult['content'] ?? '';
        if (empty($description) && (isset($aiResult['questions']) || isset($aiResult['exercises']))) {
            $description = "Contenu généré par l'IA (structure riche)";
        }

        return AcademicItem::create([
            'school_id' => $meta['school_id'],
            'class_id' => $meta['class_id'],
            'type' => strtoupper($meta['type']),
            'subject' => $meta['subject'],
            'title' => $aiResult['title'] ?? ($meta['subject'] . ' - ' . $meta['type']),
            'description' => $description,
            'due_date' => now()->addDays(7),
            'max_score' => (float)($aiResult['total_points'] ?? $aiResult['totalPoints'] ?? 20.00),
            'is_ai_generated' => true,
            'ai_content' => $aiResult,
        ]);
    }

    /**
     * Extracts and formats student performance data for the AI service.
     */
    private function getStudentDataForAI(SchoolClass $class): array
    {
        return $class->students()->with('grades')->get()->map(function ($student) {
            return [
                'name' => $student->first_name . ' ' . $student->last_name,
                'grades' => $student->grades->map(function ($grade) {
                    return [
                        'course' => $grade->subject ?? $grade->academic_item?->title ?? 'Matière inconnue',
                        'score' => (float) $grade->score,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    /**
     * Handles AI service connectivity or logic errors.
     */
    private function handleAIServiceError($raw, $class_id)
    {
        if (!$raw) {
            Log::error("AI Analysis: service returned null for class {$class_id}");
            return response()->json([
                'error' => true,
                'message' => 'Le service IA est inaccessible.',
            ], 503);
        }

        if (isset($raw['detail']) || isset($raw['error'])) {
            $errorMsg = $raw['detail'] ?? $raw['error'] ?? 'Erreur inconnue du service IA.';
            return response()->json(['error' => true, 'message' => $errorMsg], 422);
        }

        return null;
    }

    /**
     * Transforms basic AI service data into the rich dashboard structure.
     */
    private function transformAIResponse(SchoolClass $class, array $studentsData, array $raw): array
    {
        $averages = $raw['averages'] ?? [];
        $overallAverage = count($averages) > 0 ? round(array_sum($averages) / count($averages), 2) : 0;

        $passingRate = 0;
        if (count($studentsData) > 0) {
            $passing = collect($studentsData)->filter(function ($s) {
                if (empty($s['grades'])) return false;
                $avg = array_sum(array_column($s['grades'], 'score')) / count($s['grades']);
                return $avg >= 10;
            })->count();
            $passingRate = round(($passing / count($studentsData)) * 100);
        }

        $strongSubjects = [];
        $weakSubjects = [];
        foreach ($averages as $subject => $avg) {
            if ($avg >= 14) {
                $strongSubjects[] = ['subject' => $subject, 'average' => round((float)$avg, 2)];
            } elseif ($avg < 10) {
                $weakSubjects[] = ['subject' => $subject, 'average' => round((float)$avg, 2), 'trend' => 'decreasing'];
            }
        }

        $strugglingStudents = [];
        $topPerformers = [];
        foreach ($studentsData as $student) {
            if (empty($student['grades'])) continue;
            $avg = array_sum(array_column($student['grades'], 'score')) / count($student['grades']);
            $entry = ['name' => $student['name'], 'averageGrade' => round($avg, 2), 'missingAssignments' => 0];
            if ($avg < 10) {
                $strugglingStudents[] = $entry;
            } elseif ($avg >= 16) {
                $topPerformers[] = $entry;
            }
        }

        $recommendations = collect($raw['recommendations'] ?? [])->map(function ($rec) {
            return [
                'type' => 'academic',
                'title' => 'Conseil IA',
                'description' => $rec,
                'priority' => 'medium',
            ];
        })->toArray();

        $performanceChart = Grade::whereIn('student_id', $class->students()->pluck('id'))
            ->selectRaw('AVG(score) as average, TO_CHAR(created_at, \'Mon\') as month, EXTRACT(MONTH FROM created_at) as month_num')
            ->groupBy('month', 'month_num')
            ->orderBy('month_num')
            ->get()
            ->map(fn($g) => ['month' => $g->month, 'average' => round((float)$g->average, 1)])
            ->toArray();

        return [
            'classId'   => $class->id,
            'className' => $class->name,
            'analyzedAt' => now(),
            'summary' => [
                'averageGrade'   => $overallAverage,
                'attendanceRate' => 92,
                'passingRate'    => $passingRate,
                'totalStudents'  => count($studentsData),
            ],
            'insights' => [
                'strongSubjects'      => $strongSubjects,
                'weakSubjects'        => $weakSubjects,
                'strugglingStudents'  => $strugglingStudents,
                'topPerformers'       => $topPerformers,
                'aiInsights'          => $raw['insights'] ?? [],
            ],
            'performanceChart' => $performanceChart,
            'recommendations' => $recommendations,
        ];
    }

    private function mapFields(array $data): array
    {
        $mappings = ['classId' => 'class_id'];
        foreach ($mappings as $frontend => $backend) {
            if (isset($data[$frontend]) && !isset($data[$backend])) {
                $data[$backend] = $data[$frontend];
            }
        }
        return $data;
    }

    public function importExcel(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:10240']);
        $path = $request->file('file')->store('temp-imports');
        $fullPath = storage_path('app/' . $path);
        ParseExcelJob::dispatch($fullPath, Auth::user()->school_id);

        AIReport::create([
            'school_id' => Auth::user()->school_id,
            'type' => 'IMPORT_EXCEL',
            'report' => ['file' => $request->file('file')->getClientOriginalName()],
            'recommendations' => [],
        ]);

        return response()->json(['message' => 'Importation Excel lancée.']);
    }
}
