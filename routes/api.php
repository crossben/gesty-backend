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
    Route::post('/ai/save-generated', [AIController::class, 'saveGeneratedItem']);
    Route::post('/ai/import-excel', [AIController::class, 'importExcel']);
    Route::get('/ai/history', [AIController::class, 'getHistory']);
    Route::post('/ai/analyze-conflicts', [AIController::class, 'analyzeScheduleConflicts']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/charts', [DashboardController::class, 'charts']);

    // School management
    Route::get('/schools', function (Request $request) {
        return \App\Models\School::all();
    });
});
