<?php

namespace App\Services\Employee;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function getAll(string $sppgId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Employee::with(['user', 'role'])
            ->where('sppg_id', $sppgId);

        if (!empty($filters['position'])) {
            $query->where('position', $filters['position']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $query->where('name', 'ilike', "%{$filters['search']}%");
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function findById(string $id, string $sppgId): Employee
    {
        return Employee::with(['user', 'role'])
            ->where('sppg_id', $sppgId)
            ->findOrFail($id);
    }

    /**
     * Buat employee baru.
     *
     * Tidak semua employee perlu akun (user_id nullable).
     * Akun hanya dibuat jika field 'email' dikirim.
     * Role RBAC di-set via 'role_id' pada employees, bukan di users.
     */
    public function create(array $data, string $sppgId): Employee
    {
        return DB::transaction(function () use ($data, $sppgId) {
            $userId = null;

            // Buat akun user HANYA jika email dikirim
            if (!empty($data['email'])) {
                $user = User::create([
                    'name'      => $data['name'],
                    'email'     => $data['email'],
                    'password'  => $data['password'] ?? 'sppg@123',
                    'phone'     => $data['phone'] ?? null,
                    'role_type' => 'sppg_user',
                    'sppg_id'   => $sppgId,
                ]);
                $userId = $user->id;
            }

            // Buat employee record — role_id langsung di employee
            $employee = Employee::create([
                'sppg_id'     => $sppgId,
                'user_id'     => $userId,
                'role_id'     => $data['role_id'] ?? null,
                'name'        => $data['name'],
                'nik'         => $data['nik'] ?? null,
                'position'    => $data['position'] ?? null,
                'phone'       => $data['phone'] ?? null,
                'address'     => $data['address'] ?? null,
                'photo'       => $data['photo'] ?? null,
                'joined_at'   => $data['joined_at'] ?? null,
                'base_salary' => $data['base_salary'] ?? null,
                'status'      => $data['status'] ?? 'active',
            ]);

            return $employee->fresh(['user', 'role']);
        });
    }

    public function update(string $id, array $data, string $sppgId): Employee
    {
        return DB::transaction(function () use ($id, $data, $sppgId) {
            $employee = $this->findById($id, $sppgId);
            $employee->update($data);

            return $employee->fresh(['user', 'role']);
        });
    }

    public function delete(string $id, string $sppgId): void
    {
        DB::transaction(function () use ($id, $sppgId) {
            $employee = $this->findById($id, $sppgId);

            // Nonaktifkan user tapi tidak hapus
            if ($employee->user) {
                $employee->user->update(['is_active' => false]);
            }

            $employee->delete();
        });
    }
}