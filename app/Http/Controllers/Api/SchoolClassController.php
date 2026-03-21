<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class SchoolClassController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SchoolClass::class, 'schoolClass');
    }

    public function index()
    {
        $classes = QueryBuilder::for(SchoolClass::class)
            ->allowedFilters('name', 'level')
            ->allowedSorts('name', 'level', 'created_at')
            ->paginate();

        return response()->json($classes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $class = SchoolClass::create($validated);

        return response()->json($class, 201);
    }

    public function show(SchoolClass $schoolClass)
    {
        return response()->json($schoolClass->load('students'));
    }

    public function update(Request $request, SchoolClass $schoolClass)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'level' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $schoolClass->update($validated);

        return response()->json($schoolClass);
    }

    public function destroy(SchoolClass $schoolClass)
    {
        $schoolClass->delete();
        return response()->json(null, 204);
    }
}
