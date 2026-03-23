<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ->allowedFilters('first_name', 'last_name', 'email', 'class_id', 'matricule')
            ->allowedIncludes('schoolClass')
            ->allowedSorts('first_name', 'last_name', 'created_at')
            ->with('schoolClass')
            ->paginate();

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id'       => 'required|uuid|exists:school_classes,id',
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => 'required|email|unique:students,email',
            'matricule'      => 'required|string|max:50|unique:students,matricule',
            'gender'         => 'nullable|in:MASCULIN,FEMININ',
            'date_of_birth'  => 'nullable|date',
        ]);

        $student = Student::create($validated);

        return response()->json($student->load('schoolClass'), 201);
    }

    public function show(Student $student)
    {
        return response()->json($student->load('schoolClass'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'class_id'       => 'sometimes|required|uuid|exists:school_classes,id',
            'first_name'     => 'sometimes|required|string|max:255',
            'last_name'      => 'sometimes|required|string|max:255',
            'email'          => 'sometimes|required|email|unique:students,email,' . $student->id,
            'matricule'      => 'sometimes|required|string|max:50|unique:students,matricule,' . $student->id,
            'gender'         => 'nullable|in:MASCULIN,FEMININ',
            'date_of_birth'  => 'nullable|date',
        ]);

        $student->update($validated);

        return response()->json($student->load('schoolClass'));
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return response()->json(null, 204);
    }
}
