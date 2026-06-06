<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private function getSppgId(Request $request): int
    {
        $sppgId = $request->user()->sppg_id ?? $request->user()->employee?->sppg_id;
        abort_if(!$sppgId, 403, 'Anda tidak terhubung dengan SPPG manapun.');
        return (int) $sppgId;
    }

    private function validateOwnership(Request $request, Role $role): void
    {
        // Null sppg_id means it's a global role (like super admin roles), but admin SPPG shouldn't modify it.
        abort_if($role->sppg_id === null || (int) $role->sppg_id !== $this->getSppgId($request), 403, 'Anda tidak memiliki akses ke peran ini.');
    }

    // PINTU TARIK — list semua role
    public function index(Request $request)
    {
        $sppgId = $this->getSppgId($request);
        $roles = Role::where('sppg_id', $sppgId)
            ->with('permissions')
            ->withCount('employees')
            ->latest()
            ->paginate(10);

        return response()->json($roles);
    }

    // PINTU TARIK — detail 1 role
    public function show(Request $request, Role $role)
    {
        $this->validateOwnership($request, $role);
        return response()->json($role->load('permissions', 'employees'));
    }

    // PINTU MASUK — buat role baru
    public function store(Request $request)
    {
        $sppgId = $this->getSppgId($request);

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->where('sppg_id', $sppgId)
            ],
            'description'   => 'nullable|string|max:500',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name'        => $request->name,
            'description' => $request->description,
            'sppg_id'     => $sppgId,
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
        $this->validateOwnership($request, $role);
        $sppgId = $this->getSppgId($request);

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')
                    ->where('sppg_id', $sppgId)
                    ->ignore($role->id)
            ],
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
    public function destroy(Request $request, Role $role)
    {
        $this->validateOwnership($request, $role);

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