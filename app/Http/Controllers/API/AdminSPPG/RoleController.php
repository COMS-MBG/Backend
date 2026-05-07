<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // PINTU TARIK — list semua role
    public function index()
    {
        $roles = Role::with('permissions')->withCount('employees')->latest()->paginate(10);
        return response()->json($roles);
    }

    // PINTU TARIK — detail 1 role
    public function show(Role $role)
    {
        return response()->json($role->load('permissions', 'employees'));
    }

    // PINTU MASUK — buat role baru
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:roles,name',
            'description'   => 'nullable|string|max:500',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return response()->json([
            'message' => 'Role created successfully.',
            'role'    => $role->load('permissions'),
        ], 201);
    }

    // PINTU MASUK — update role
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'          => "required|string|max:255|unique:roles,name,{$role->id}",
            'description'   => 'nullable|string|max:500',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return response()->json([
            'message' => 'Role updated successfully.',
            'role'    => $role->fresh('permissions'),
        ]);
    }

    // PINTU MASUK — hapus role
    public function destroy(Role $role)
    {
        if ($role->employees()->count() > 0) {
            return response()->json([
                'message' => "Cannot delete role '{$role->name}' — still assigned to {$role->employees()->count()} employee(s).",
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully.',
        ]);
    }
}