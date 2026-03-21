<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicItem;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class AcademicItemController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(AcademicItem::class, 'academicItem');
    }

    public function index()
    {
        $items = QueryBuilder::for(AcademicItem::class)
            ->allowedFilters('title', 'type', 'subject', 'class_id')
            ->allowedSorts('title', 'due_date', 'created_at')
            ->paginate();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:DEVOIR,EXAMEN,PROJET',
            'subject' => 'required|string',
            'difficulty' => 'required|in:EASY,MEDIUM,HARD',
            'class_id' => 'required|uuid|exists:school_classes,id',
            'due_date' => 'nullable|date',
            'total_points' => 'required|integer|min:0',
            'status' => 'required|in:PENDING,IN_PROGRESS,COMPLETED,CANCELLED',
            'ai_generated' => 'boolean',
            'content' => 'nullable|string',
        ]);

        $item = AcademicItem::create($validated);

        return response()->json($item, 211);
    }

    public function show(AcademicItem $academicItem)
    {
        return response()->json($academicItem);
    }

    public function update(Request $request, AcademicItem $academicItem)
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'type' => 'in:DEVOIR,EXAMEN,PROJET',
            'subject' => 'string',
            'difficulty' => 'in:EASY,MEDIUM,HARD',
            'class_id' => 'uuid|exists:school_classes,id',
            'due_date' => 'nullable|date',
            'total_points' => 'integer|min:0',
            'status' => 'in:PENDING,IN_PROGRESS,COMPLETED,CANCELLED',
            'ai_generated' => 'boolean',
            'content' => 'nullable|string',
        ]);

        $academicItem->update($validated);

        return response()->json($academicItem);
    }

    public function destroy(AcademicItem $academicItem)
    {
        $academicItem->delete();
        return response()->json(null, 204);
    }
}
