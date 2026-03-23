<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SchoolClassController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AcademicItemController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\AIController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Academic Management
    Route::apiResource('classes', SchoolClassController::class);
    Route::apiResource('students', StudentController::class);
    Route::apiResource('academic-items', AcademicItemController::class);
    Route::apiResource('grades', GradeController::class);
    Route::apiResource('announcements', AnnouncementController::class);
    Route::apiResource('schedules', ScheduleController::class);
    Route::apiResource('admins', AdminController::class);

    // AI Features
    Route::post('/ai/analyze-class/{class_id}', [AIController::class, 'analyzeClass']);
    Route::post('/ai/generate-academic-item', [AIController::class, 'generateAcademicItem']);
    Route::post('/ai/import-excel', [AIController::class, 'importExcel']);
    Route::get('/ai/history', function (Request $request) {
        $query = \App\Models\AIReport::orderBy('created_at', 'desc')->with('schoolClass');
        
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $paginator = $query->paginate();

        $paginator->getCollection()->transform(function ($report) {
            return [
                'id' => $report->id,
                'type' => strtoupper($report->type),
                'status' => 'COMPLETED',
                'classId' => $report->class_id,
                'className' => $report->schoolClass ? $report->schoolClass->name : 'Non spécifié',
                'input' => json_encode(['requested_at' => clone $report->created_at]),
                'output' => json_encode(['report' => $report->report, 'recommendations' => $report->recommendations]),
                'generatedBy' => 'system',
                'generatedByName' => 'System AI',
                'duration' => 2500,
                'createdAt' => clone $report->created_at,
            ];
        });

        return $paginator;
    });

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/charts', [DashboardController::class, 'charts']);

    // School management
    Route::get('/schools', function (Request $request) {
        return \App\Models\School::all();
    });
});
