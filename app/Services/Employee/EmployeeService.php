<?php
namespace App\Services\Employee;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
    public function getAll(string $sppgId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Employee::with('user')
            ->where('sppg_id', $sppgId);

        if (!empty($filters['jabatan'])) {
            $query->where('jabatan', $filters['jabatan']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $query->where('nama', 'ilike', "%{$filters['search']}%");
        }

        return $query->orderBy('nama')->paginate($perPage);
    }

    public function findById(string $id, string $sppgId): Employee
    {
        return Employee::with('user')
            ->where('sppg_id', $sppgId)
            ->findOrFail($id);
    }

    public function create(array $data, string $sppgId): Employee
    {
        return DB::transaction(function () use ($data, $sppgId) {
            // Buat akun user untuk karyawan
            $user = User::create([
                'nama'     => $data['nama'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password'] ?? 'sppg@123'),
                'sppg_id'  => $sppgId,
            ]);

            // Assign role Spatie sesuai jabatan
            $user->assignRole($this->mapJabatanToRole($data['jabatan']));

            // Buat employee record
            $employee = Employee::create(array_merge(
                $data,
                ['user_id' => $user->id, 'sppg_id' => $sppgId]
            ));

            return $employee->fresh('user');
        });
    }

    public function update(string $id, array $data, string $sppgId): Employee
    {
        return DB::transaction(function () use ($id, $data, $sppgId) {
            $employee = $this->findById($id, $sppgId);
            $employee->update($data);

            // Update role jika jabatan berubah
            if (!empty($data['jabatan']) && $employee->user) {
                $employee->user->syncRoles([$this->mapJabatanToRole($data['jabatan'])]);
            }

            return $employee->fresh('user');
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

    private function mapJabatanToRole(string $jabatan): string
    {
        return match ($jabatan) {
            'pemilik'              => 'pemilik',
            'manajer'              => 'manajer',
            'ahli_gizi'            => 'ahli_gizi',
            'admin_logistik'       => 'admin_logistik',
            'kurir'                => 'kurir',
            'karyawan_operasional' => 'karyawan_operasional',
            default                => 'karyawan_operasional',
        };
    }
}