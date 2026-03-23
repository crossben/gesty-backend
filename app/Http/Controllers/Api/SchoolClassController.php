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

    public function index()
    {
        $classes = QueryBuilder::for(SchoolClass::class)
            ->allowedFilters('name', 'level', 'code')
            ->allowedSorts('name', 'level', 'created_at')
            ->withCount('students')
            ->paginate();

        return response()->json($classes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'level'          => 'required|string|max:255',
            'code'           => 'nullable|string|max:50',
            'capacity'       => 'nullable|integer|min:1',
            'academic_year'  => 'nullable|string|max:20',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $class = SchoolClass::create($validated);

        return response()->json($class->loadCount('students'), 201);
    }

    public function show(SchoolClass $schoolClass)
    {
        $schoolClass->loadCount('students');
        return response()->json($schoolClass);
    }

    public function update(Request $request, SchoolClass $schoolClass)
    {
        $validated = $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'level'          => 'sometimes|required|string|max:255',
            'code'           => 'nullable|string|max:50',
            'capacity'       => 'nullable|integer|min:1',
            'academic_year'  => 'nullable|string|max:20',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $schoolClass->update($validated);

        return response()->json($schoolClass->loadCount('students'));
    }

    public function destroy(SchoolClass $schoolClass)
    {
        $schoolClass->delete();
        return response()->json(null, 204);
    }
}
