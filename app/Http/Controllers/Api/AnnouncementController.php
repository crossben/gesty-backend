<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $announcements = QueryBuilder::for(Announcement::class)
            ->allowedFilters(
                'priority',
                AllowedFilter::exact('class_id'),
                AllowedFilter::callback('target_class', function ($query, $value) {
                    if ($value === 'all') {
                        $query->whereNull('class_id');
                    } else {
                        $query->where('class_id', $value);
                    }
                })
            )
            ->allowedIncludes('author', 'schoolClass')
            ->latest()
            ->paginate();

        $announcements->getCollection()->transform(function ($announcement) {
            return [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'content' => $announcement->content,
                'priority' => $announcement->priority,
                'authorId' => $announcement->author_id,
                'authorName' => $announcement->author ? $announcement->author->name : 'System',
                'targetClass' => $announcement->class_id,
                'className' => $announcement->schoolClass ? $announcement->schoolClass->name : null,
                'expiresAt' => $announcement->expires_at,
                'createdAt' => $announcement->created_at,
                'updatedAt' => $announcement->updated_at,
            ];
        });

        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'content' => 'required|string|max:1000',
            'priority' => 'required|in:LOW,NORMAL,HIGH,URGENT',
            'class_id' => 'nullable|exists:school_classes,id',
            'expires_at' => 'nullable|date',
        ]);

        // Map camelCase from frontend to snake_case if needed
        if ($request->has('targetClass')) {
            $validated['class_id'] = $request->targetClass;
        }
        if ($request->has('expiresAt')) {
            $validated['expires_at'] = $request->expiresAt;
        }

        $announcement = Announcement::create([
            ...$validated,
            'school_id' => Auth::user()->school_id,
            'author_id' => Auth::id(),
        ]);

        return response()->json($announcement, 201);
    }

    public function show(Announcement $announcement)
    {
        return response()->json($announcement->load(['author', 'schoolClass']));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:100',
            'content' => 'sometimes|required|string|max:1000',
            'priority' => 'sometimes|required|in:LOW,NORMAL,HIGH,URGENT',
            'class_id' => 'nullable|exists:school_classes,id',
            'expires_at' => 'nullable|date',
        ]);

        // Map camelCase from frontend to snake_case if needed
        if ($request->has('targetClass')) {
            $validated['class_id'] = $request->targetClass;
        }
        if ($request->has('expiresAt')) {
            $validated['expires_at'] = $request->expiresAt;
        }

        $announcement->update($validated);

        return response()->json($announcement);
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return response()->json(null, 204);
    }
}
