<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;

class SchoolClassController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SchoolClass::class, 'schoolClass');
    }

    /**
     * Map camelCase frontend fields to snake_case backend fields.
     */
    private function mapFields(array $data): array
    {
        $mappings = [
            'academicYear' => 'academic_year',
            'isActive'     => 'is_active',
        ];

        foreach ($mappings as $frontend => $backend) {
            if (array_key_exists($frontend, $data) && !array_key_exists($backend, $data)) {
                $data[$backend] = $data[$frontend];
            }
        }

        return $data;
    }

    /**
     * Transform class model to camelCase for frontend.
     */
    private function transform(SchoolClass $class): array
    {
        return [
            'id'           => $class->id,
            'name'         => $class->name,
            'level'        => $class->level,
            'code'         => $class->code,
            'capacity'     => $class->capacity,
            'academicYear' => $class->academic_year,
            'description'  => $class->description,
            'isActive'     => (bool)($class->is_active ?? true),
            'studentCount' => $class->students_count ?? 0,
            'createdAt'    => $class->created_at,
            'updatedAt'    => $class->updated_at,
        ];
    }

    public function index()
    {
        $classes = QueryBuilder::for(SchoolClass::class)
            ->allowedFilters('name', 'level', 'code')
            ->allowedSorts('name', 'level', 'created_at')
            ->withCount('students')
            ->paginate();

        $transformed = $classes->getCollection()->map(fn($c) => $this->transform($c));
        $classes->setCollection($transformed);

        return response()->json($classes);
    }

    public function store(Request $request)
    {
        $data = $this->mapFields($request->all());

        $validated = validator($data, [
            'name'         => 'required|string|max:255',
            'level'        => 'required|string|max:255',
            'code'         => 'nullable|string|max:50',
            'capacity'     => 'nullable|integer|min:1',
            'academic_year'=> 'nullable|string|max:20',
            'description'  => 'nullable|string',
            'is_active'    => 'boolean',
        ])->validate();

        $class = SchoolClass::create([
            ...$validated,
            'school_id' => Auth::user()->school_id,
        ]);

        return response()->json($this->transform($class), 201);
    }

    public function show(SchoolClass $schoolClass)
    {
        $schoolClass->loadCount('students');
        return response()->json($this->transform($schoolClass));
    }

    public function update(Request $request, SchoolClass $schoolClass)
    {
        $data = $this->mapFields($request->all());

        $validated = validator($data, [
            'name'         => 'sometimes|required|string|max:255',
            'level'        => 'sometimes|required|string|max:255',
            'code'         => 'nullable|string|max:50',
            'capacity'     => 'nullable|integer|min:1',
            'academic_year'=> 'nullable|string|max:20',
            'description'  => 'nullable|string',
            'is_active'    => 'boolean',
        ])->validate();

        $schoolClass->update($validated);

        return response()->json($this->transform($schoolClass));
    }

    public function destroy(SchoolClass $schoolClass)
    {
        $schoolClass->delete();
        return response()->json(null, 204);
    }
}
