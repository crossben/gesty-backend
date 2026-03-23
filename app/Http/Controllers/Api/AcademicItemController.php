<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicItem;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

use Spatie\QueryBuilder\AllowedFilter;

class AcademicItemController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(AcademicItem::class, 'academicItem');
    }

    public function index()
    {
        $items = QueryBuilder::for(AcademicItem::class)
            ->allowedFilters(
                'title',
                'subject',
                AllowedFilter::exact('type'),
                AllowedFilter::exact('class_id'),
            )
            ->allowedSorts('title', 'due_date', 'created_at')
            ->paginate();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'type'            => 'required|in:DEVOIR,EXAMEN,PROJET',
            'subject'         => 'required|string',
            'difficulty'      => 'required|in:EASY,MEDIUM,HARD',
            'class_id'        => 'required|uuid|exists:school_classes,id',
            'due_date'        => 'nullable|date',
            'max_score'       => 'required|numeric|min:0',
            'status'          => 'required|in:PENDING,IN_PROGRESS,COMPLETED,CANCELLED',
            'is_ai_generated' => 'boolean',
            'ai_content'      => 'nullable|array',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $item = AcademicItem::create($validated);

        return response()->json($item, 201);
    }

    public function show(AcademicItem $academicItem)
    {
        return response()->json($academicItem->load(['schoolClass', 'grades.student']));
    }

    public function update(Request $request, AcademicItem $academicItem)
    {
        $validated = $request->validate([
            'title'           => 'string|max:255',
            'description'     => 'nullable|string',
            'type'            => 'in:DEVOIR,EXAMEN,PROJET',
            'subject'         => 'string',
            'difficulty'      => 'in:EASY,MEDIUM,HARD',
            'class_id'        => 'uuid|exists:school_classes,id',
            'due_date'        => 'nullable|date',
            'max_score'       => 'numeric|min:0',
            'status'          => 'in:PENDING,IN_PROGRESS,COMPLETED,CANCELLED',
            'is_ai_generated' => 'boolean',
            'ai_content'      => 'nullable|array',
        ]);

        $academicItem->update($validated);

        return response()->json($academicItem->load(['schoolClass', 'grades.student']));
    }

    public function destroy(AcademicItem $academicItem)
    {
        $academicItem->delete();
        return response()->json(null, 204);
    }
}
