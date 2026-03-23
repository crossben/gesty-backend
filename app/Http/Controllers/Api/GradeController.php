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
        $validated = $request->validate([
            'student_id'       => 'required|uuid|exists:students,id',
            'academic_item_id' => 'required|uuid|exists:academic_items,id',
            'score'            => 'required|numeric|min:0',
            'max_score'        => 'required|numeric|min:0',
            'comments'         => 'nullable|string',
            'graded_at'        => 'nullable|date',
        ]);

        // Default graded_at to now if not provided
        if (empty($validated['graded_at'])) {
            $validated['graded_at'] = now();
        }

        $grade = Grade::create($validated);

        return response()->json($grade->load(['student', 'academicItem']), 201);
    }

    public function show(Grade $grade)
    {
        return response()->json($grade->load(['student', 'academicItem']));
    }

    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'score'     => 'sometimes|numeric|min:0',
            'max_score' => 'sometimes|numeric|min:0',
            'comments'  => 'nullable|string',
            'graded_at' => 'nullable|date',
        ]);

        $grade->update($validated);

        return response()->json($grade->load(['student', 'academicItem']));
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
        return response()->json(null, 204);
    }
}
