<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    /**
     * Get global statistics across all schools.
     */
    public function stats()
    {
        return response()->json(['data' => [
            'total_schools'  => School::count(),
            'total_students' => Student::withoutGlobalScopes()->count(),
            'total_admins'   => User::whereHas('roles', function($q) {
                $q->where('name', 'ADMIN');
            })->count(),
            'active_schools' => School::where('is_active', true)->count(),
        ]]);
    }

    /**
     * List all schools.
     */
    public function index()
    {
        return response()->json(['data' => School::withCount(['students', 'users'])->get()]);
    }

    /**
     * Store a new school and its first administrator.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255|unique:schools,slug',
            'email'      => 'nullable|email|max:255',
            'admin_name' => 'required|string|max:255',
            'admin_email'=> 'required|email|unique:users,email',
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Create School
            $school = School::create([
                'name'      => $validated['name'],
                'slug'      => $validated['slug'] ?? Str::slug($validated['name']),
                'email'     => $validated['email'],
                'is_active' => true,
            ]);

            // 2. Create Admin User
            $user = User::create([
                'name'      => $validated['admin_name'],
                'email'     => $validated['admin_email'],
                'password'  => Hash::make('password'), // Static for now, or random
                'school_id' => $school->id,
            ]);

            $user->assignRole('ADMIN');

            return response()->json([
                'message' => 'School and admin created successfully',
                'school'  => $school,
                'admin'   => $user
            ], 201);
        });
    }

    /**
     * Toggle school status (Lock/Unlock).
     */
    public function toggleStatus(School $school)
    {
        $school->update([
            'is_active' => !$school->is_active
        ]);

        return response()->json([
            'message'   => 'School status updated',
            'is_active' => $school->is_active
        ]);
    }

    /**
     * Remove the specified school and all associated data.
     */
    public function destroy(School $school)
    {
        // In a real B2B app, we would probably use soft deletes or a background job
        $school->delete();
        return response()->json(['message' => 'School deleted successfully']);
    }
}
