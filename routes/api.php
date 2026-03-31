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

// Student Authentication
Route::post('/student/login', [\App\Http\Controllers\Api\StudentAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Student Routes (authenticated as student via Sanctum)
    Route::prefix('student')->group(function () {
        Route::get('/me', [\App\Http\Controllers\Api\StudentAuthController::class, 'me']);
        Route::post('/logout', [\App\Http\Controllers\Api\StudentAuthController::class, 'logout']);
        Route::get('/schedule', [\App\Http\Controllers\Api\StudentAuthController::class, 'schedule']);
        Route::get('/academic-items', [\App\Http\Controllers\Api\StudentAuthController::class, 'academicItems']);
        Route::get('/grades', [\App\Http\Controllers\Api\StudentAuthController::class, 'grades']);
        Route::get('/announcements', [\App\Http\Controllers\Api\StudentAuthController::class, 'announcements']);
        Route::post('/fcm-token', [\App\Http\Controllers\Api\StudentAuthController::class, 'updateFcmToken']);
    });

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

    // Super Admin (B2B Management)
    Route::get('/super-admin/stats', [\App\Http\Controllers\Api\SuperAdminController::class, 'stats']);
    Route::apiResource('/super-admin/schools', \App\Http\Controllers\Api\SuperAdminController::class);
    Route::patch('/super-admin/schools/{school}/toggle', [\App\Http\Controllers\Api\SuperAdminController::class, 'toggleStatus']);

    // School management (Legacy/Fallback)
    Route::get('/schools', function (Request $request) {
        return \App\Models\School::all();
    });
});
