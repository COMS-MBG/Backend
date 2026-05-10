<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('employee.edit');
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee');

        return [
            'nama'              => 'sometimes|string|max:255',
            'nik'               => "nullable|string|size:16|unique:employees,nik,{$employeeId}",
            'jabatan'           => 'sometimes|in:' . implode(',', Employee::ROLES),
            'telepon'           => 'nullable|string|max:20',
            'alamat'            => 'nullable|string',
            'tanggal_bergabung' => 'nullable|date',
            'gaji_pokok'        => 'nullable|numeric|min:0',
            'status'            => 'sometimes|in:aktif,nonaktif,cuti',
            'foto'              => 'nullable|image|max:2048',
            'role_id'           => 'nullable|exists:roles,id', // ← tambahan baru
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.exists' => 'Role yang dipilih tidak ditemukan.',
        ];
    }
}