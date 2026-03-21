<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class GradeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Grade::class, 'grade');
    }

    public function index()
    {
        $grades = QueryBuilder::for(Grade::class)
            ->allowedFilters('student_id', 'academic_item_id')
            ->allowedIncludes('student', 'academicItem')
            ->allowedSorts('score', 'graded_at')
            ->paginate();

        return response()->json($grades);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|uuid|exists:students,id',
            'academic_item_id' => 'required|uuid|exists:academic_items,id',
            'score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|min:0',
            'comments' => 'nullable|string',
            'graded_at' => 'required|date',
        ]);

        $grade = Grade::create($validated);

        return response()->json($grade, 211);
    }

    public function show(Grade $grade)
    {
        return response()->json($grade->load(['student', 'academicItem']));
    }

    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'score' => 'numeric|min:0',
            'max_score' => 'numeric|min:0',
            'comments' => 'nullable|string',
            'graded_at' => 'date',
        ]);

        $grade->update($validated);

        return response()->json($grade);
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
        return response()->json(null, 204);
    }
}
