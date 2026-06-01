<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Role;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // ── PINTU TARIK ───────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Employee::with('role')->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        return response()->json($query->paginate(10));
    }

    public function show(Employee $employee)
    {
        return response()->json(
            $employee->load('role.permissions', 'user', 'sppg')
        );
    }

    // ── PINTU MASUK ───────────────────────────────────────────────────────────

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();
        $data['sppg_id'] = $request->user()->sppg_id
            ?? $request->user()->employee?->sppg_id
            ?? 1;
        $employee = Employee::create($data);

        return response()->json([
            'message'  => 'Employee created successfully.',
            'employee' => $employee->load('role'),
        ], 201);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return response()->json([
            'message'  => 'Employee updated successfully.',
            'employee' => $employee->fresh('role'),
        ]);
    }

    public function destroy(Employee $employee)
    {
        $employee->deleteOrFail();

        return response()->json([
            'message' => 'Employee deleted successfully.',
        ]);
    }

    // ── ASSIGN ROLE ───────────────────────────────────────────────────────────

    public function showAssignRole(Employee $employee)
    {
        return response()->json([
            'employee' => $employee->load('role'),
            'roles'    => Role::orderBy('name', 'asc')->get(),
        ]);
    }

    public function assignRole(Request $request, Employee $employee)
    {
        $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $employee->update(['role_id' => $request->role_id]);

        return response()->json([
            'message'  => 'Role assigned successfully.',
            'employee' => $employee->fresh('role'),
        ]);
    }
}