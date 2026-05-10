<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('employee.create');
    }

    public function rules(): array
    {
        return [
            'nama'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'nullable|string|min:8',
            'nik'               => 'nullable|string|size:16|unique:employees,nik',
            'jabatan'           => 'required|in:' . implode(',', Employee::ROLES),
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