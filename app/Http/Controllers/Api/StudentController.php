<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Student::class, 'student');
    }

    public function index()
    {
        $students = QueryBuilder::for(Student::class)
            ->allowedFilters('name', 'email', 'class_id')
            ->allowedIncludes('schoolClass')
            ->allowedSorts('name', 'created_at')
            ->paginate();

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|uuid|exists:school_classes,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
        ]);

        $student = Student::create($validated);

        return response()->json($student, 201);
    }

    public function show(Student $student)
    {
        return response()->json($student->load(['schoolClass', 'grades.academicItem']));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'class_id' => 'sometimes|required|uuid|exists:school_classes,id',
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:students,email,' . $student->id,
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
        ]);

        $student->update($validated);

        return response()->json($student);
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return response()->json(null, 204);
    }
}
