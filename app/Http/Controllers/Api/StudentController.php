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

    /**
     * Map camelCase frontend fields to snake_case backend fields.
     */
    private function mapFields(array $data): array
    {
        $mappings = [
            'classId'     => 'class_id',
            'firstName'   => 'first_name',
            'lastName'    => 'last_name',
            'dateOfBirth' => 'date_of_birth',
        ];

        foreach ($mappings as $frontend => $backend) {
            if (isset($data[$frontend]) && !isset($data[$backend])) {
                $data[$backend] = $data[$frontend];
            }
        }

        return $data;
    }

    /**
     * Transform student model to camelCase for frontend.
     */
    private function transform(Student $student): array
    {
        $student->load('schoolClass');
        return [
            'id'          => $student->id,
            'matricule'   => $student->matricule,
            'firstName'   => $student->first_name,
            'lastName'    => $student->last_name,
            'email'       => $student->email,
            'gender'      => $student->gender,
            'dateOfBirth' => $student->date_of_birth,
            'classId'     => $student->class_id,
            'className'   => $student->schoolClass?->name,
            'isActive'    => true,
            'createdAt'   => $student->created_at,
            'updatedAt'   => $student->updated_at,
        ];
    }

    public function index()
    {
        $students = QueryBuilder::for(Student::class)
            ->allowedFilters('first_name', 'last_name', 'email', 'class_id', 'matricule')
            ->allowedIncludes('schoolClass')
            ->allowedSorts('first_name', 'last_name', 'created_at')
            ->with('schoolClass')
            ->paginate();

        // Transform data to camelCase
        $transformed = $students->getCollection()->map(fn($s) => $this->transform($s));
        $students->setCollection($transformed);

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $data = $this->mapFields($request->all());

        $validated = validator($data, [
            'class_id'     => 'required|uuid|exists:school_classes,id',
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:students,email',
            'matricule'    => 'required|string|max:50|unique:students,matricule',
            'gender'       => 'nullable|in:MASCULIN,FEMININ',
            'date_of_birth'=> 'nullable|date',
        ])->validate();

        $student = Student::create([
            ...$validated,
            'school_id' => Auth::user()->school_id,
        ]);

        return response()->json($this->transform($student), 201);
    }

    public function show(Student $student)
    {
        return response()->json($this->transform($student));
    }

    public function update(Request $request, Student $student)
    {
        $data = $this->mapFields($request->all());

        $validated = validator($data, [
            'class_id'     => 'sometimes|required|uuid|exists:school_classes,id',
            'first_name'   => 'sometimes|required|string|max:255',
            'last_name'    => 'sometimes|required|string|max:255',
            'email'        => 'sometimes|required|email|unique:students,email,' . $student->id,
            'matricule'    => 'sometimes|required|string|max:50|unique:students,matricule,' . $student->id,
            'gender'       => 'nullable|in:MASCULIN,FEMININ',
            'date_of_birth'=> 'nullable|date',
        ])->validate();

        $student->update($validated);

        return response()->json($this->transform($student));
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return response()->json(null, 204);
    }
}
