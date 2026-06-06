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
    private function getSppgId(Request $request): int
    {
        $sppgId = $request->user()->sppg_id ?? $request->user()->employee?->sppg_id;
        abort_if(!$sppgId, 403, 'Anda tidak terhubung dengan SPPG manapun.');
        return (int) $sppgId;
    }

    private function validateOwnership(Request $request, Employee $employee): void
    {
        abort_if((int) $employee->sppg_id !== $this->getSppgId($request), 403, 'Anda tidak memiliki akses ke karyawan ini.');
    }

    // ── PINTU TARIK ───────────────────────────────────────────────────────────
    
    public function index(Request $request)
    {
        $sppgId = $this->getSppgId($request);
        $query = Employee::with('role')->where('sppg_id', $sppgId)->latest();

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

    public function show(Request $request, Employee $employee)
    {
        $this->validateOwnership($request, $employee);

        return response()->json(
            $employee->load('role.permissions', 'user', 'sppg')
        );
    }

    // ── PINTU MASUK ───────────────────────────────────────────────────────────

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();
        $data['sppg_id'] = $this->getSppgId($request);
        $employee = Employee::create($data);

        return response()->json([
            'message'  => 'Employee created successfully.',
            'employee' => $employee->load('role'),
        ], 201);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $this->validateOwnership($request, $employee);
        $employee->update($request->validated());

        return response()->json([
            'message'  => 'Employee updated successfully.',
            'employee' => $employee->fresh('role'),
        ]);
    }

    public function destroy(Request $request, Employee $employee)
    {
        $this->validateOwnership($request, $employee);
        $employee->deleteOrFail();

        return response()->json([
            'message' => 'Employee deleted successfully.',
        ]);
    }

    // ── ASSIGN ROLE ───────────────────────────────────────────────────────────

    public function showAssignRole(Request $request, Employee $employee)
    {
        $this->validateOwnership($request, $employee);
        $sppgId = $this->getSppgId($request);

        return response()->json([
            'employee' => $employee->load('role'),
            'roles'    => Role::where('sppg_id', $sppgId)->orderBy('name', 'asc')->get(),
        ]);
    }

    public function assignRole(Request $request, Employee $employee)
    {
        $this->validateOwnership($request, $employee);
        $sppgId = $this->getSppgId($request);

        $request->validate([
            'role_id' => [
                'required', 
                \Illuminate\Validation\Rule::exists('roles', 'id')->where('sppg_id', $sppgId)
            ],
        ]);

        $employee->update(['role_id' => $request->role_id]);

        return response()->json([
            'message'  => 'Role assigned successfully.',
            'employee' => $employee->fresh('role'),
        ]);
    }
}