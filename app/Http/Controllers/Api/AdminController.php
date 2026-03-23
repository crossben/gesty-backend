<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AdminController extends Controller
{
    public function index()
    {
        $admins = QueryBuilder::for(User::class)
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('role', function ($query, $value) {
                    if ($value !== 'all') {
                        $query->role($value);
                    }
                }),
            )
            ->whereNotNull('school_id') // Admins belong to a school
            ->get();

        // Map roles for the frontend
        $data = $admins->map(function ($admin) {
            $role = $admin->getRoleNames()->first() ?: 'ADMIN';
            return [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $role,
                'isActive' => true,
                'lastLogin' => null,
                'createdAt' => $admin->created_at,
                'updatedAt' => $admin->updated_at,
            ];
        });

        return response()->json($data);
    }

    public function show($id)
    {
        $admin = User::findOrFail($id);
        $role = $admin->getRoleNames()->first() ?: 'ADMIN';

        return response()->json([
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => $role,
            'isActive' => true,
            'lastLogin' => null,
            'createdAt' => $admin->created_at,
            'updatedAt' => $admin->updated_at,
        ]);
    }

    public function destroy($id)
    {
        $admin = User::findOrFail($id);
        $admin->delete();

        return response()->json(null, 204);
    }
}
