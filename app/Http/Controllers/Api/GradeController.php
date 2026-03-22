<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class GradeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Grade::class, 'grade');
    }

    /**
     * Map camelCase frontend fields to snake_case backend fields.
     */
    private function mapFields(array $data): array
    {
        $mappings = [
            'studentId'      => 'student_id',
            'academicItemId' => 'academic_item_id',
            'maxScore'       => 'max_score',
            'gradedAt'       => 'graded_at',
        ];

        foreach ($mappings as $frontend => $backend) {
            if (isset($data[$frontend]) && !isset($data[$backend])) {
                $data[$backend] = $data[$frontend];
            }
        }

        return $data;
    }

    public function index()
    {
        $grades = QueryBuilder::for(Grade::class)
            ->allowedFilters(
                AllowedFilter::exact('student_id'),
                AllowedFilter::exact('academic_item_id'),
            )
            ->allowedIncludes('student', 'academicItem')
            ->allowedSorts('score', 'graded_at', 'created_at')
            ->with(['student', 'academicItem'])
            ->paginate();

        return response()->json($grades);
    }

    public function store(Request $request)
    {
        $data = $this->mapFields($request->all());

        $validated = validator($data, [
            'student_id'       => 'required|uuid|exists:students,id',
            'academic_item_id' => 'required|uuid|exists:academic_items,id',
            'score'            => 'required|numeric|min:0',
            'max_score'        => 'required|numeric|min:0',
            'comments'         => 'nullable|string',
            'graded_at'        => 'nullable|date',
        ])->validate();

        // Default graded_at to now if not provided
        if (empty($validated['graded_at'])) {
            $validated['graded_at'] = now();
        }

        $grade = Grade::create([
            ...$validated,
            'school_id' => Auth::user()->school_id,
        ]);

        return response()->json($grade->load(['student', 'academicItem']), 201);
    }

    public function show(Grade $grade)
    {
        return response()->json($grade->load(['student', 'academicItem']));
    }

    public function update(Request $request, Grade $grade)
    {
        $data = $this->mapFields($request->all());

        $validated = validator($data, [
            'score'     => 'sometimes|numeric|min:0',
            'max_score' => 'sometimes|numeric|min:0',
            'comments'  => 'nullable|string',
            'graded_at' => 'nullable|date',
        ])->validate();

        $grade->update($validated);

        return response()->json($grade->load(['student', 'academicItem']));
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
        return response()->json(null, 204);
    }
}
