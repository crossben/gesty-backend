<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StudentAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user() instanceof Student) {
                return response()->json(['message' => 'Accès étudiant uniquement.'], 403);
            }
            return $next($request);
        })->except(['login']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'matricule' => 'required|string',
            'password' => 'required|string',
        ]);

        $student = Student::with(['schoolClass', 'school'])->where('matricule', $request->matricule)->first();

        if (!$student || !$student->password || !Hash::check($request->password, $student->password)) {
            throw ValidationException::withMessages([
                'matricule' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        $token = $student->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'student' => $student->load(['schoolClass', 'school']),
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        if (!$request->user() instanceof Student) {
            return response()->json(['message' => 'Accès étudiant uniquement.'], 403);
        }

        return response()->json([
            'student' => $request->user()->load(['schoolClass', 'school', 'grades.academicItem']),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function schedule(Request $request)
    {
        $student = $request->user()->load('schoolClass');
        $classId = $student->class_id;

        $schedules = \App\Models\Schedule::where('class_id', $classId)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return response()->json(['data' => $schedules]);
    }

    public function academicItems(Request $request)
    {
        $student = $request->user()->load('schoolClass');
        $classId = $student->class_id;

        $query = \App\Models\AcademicItem::where('class_id', $classId);

        if ($request->has('type')) {
            $query->where('type', strtoupper($request->type));
        }

        if ($request->has('status')) {
            $query->where('status', strtoupper($request->status));
        }

        $items = $query->orderBy('due_date', 'asc')->get();

        return response()->json(['data' => $items]);
    }

    public function grades(Request $request)
    {
        $student = $request->user();

        $grades = \App\Models\Grade::with('academicItem')
            ->where('student_id', $student->id)
            ->orderBy('graded_at', 'desc')
            ->get();

        $average = $grades->isNotEmpty() ? round($grades->avg('score'), 2) : null;

        return response()->json([
            'data' => $grades,
            'average' => $average,
        ]);
    }

    public function announcements(Request $request)
    {
        $student = $request->user()->load('schoolClass');
        $classId = $student->class_id;

        $announcements = \App\Models\Announcement::with('author')
            ->where(function ($q) use ($classId) {
                $q->whereNull('class_id')->orWhere('class_id', $classId);
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $announcements]);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $request->user()->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json(['message' => 'Token enregistré.']);
    }
}
