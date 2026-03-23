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
    public function __construct()
    {
        $this->authorizeResource(Announcement::class, 'announcement');
    }

    public function index()
    {
        $announcements = QueryBuilder::for(Announcement::class)
            ->allowedFilters(
                'priority',
                'class_id'
            )
            ->allowedIncludes('author', 'schoolClass')
            ->latest()
            ->paginate();

        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:100',
            'content'    => 'required|string|max:1000',
            'priority'   => 'required|in:LOW,NORMAL,HIGH,URGENT',
            'class_id'   => 'nullable|exists:school_classes,id',
            'expires_at' => 'nullable|date',
        ]);

        $announcement = Announcement::create([
            ...$validated,
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
            'title'      => 'sometimes|required|string|max:100',
            'content'    => 'sometimes|required|string|max:1000',
            'priority'   => 'sometimes|required|in:LOW,NORMAL,HIGH,URGENT',
            'class_id'   => 'nullable|exists:school_classes,id',
            'expires_at' => 'nullable|date',
        ]);

        $announcement->update($validated);

        return response()->json($announcement);
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return response()->json(null, 204);
    }
}
