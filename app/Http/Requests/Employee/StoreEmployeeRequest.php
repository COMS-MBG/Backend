<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('employee.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|unique:users,email',
            'password'    => 'nullable|string|min:8',
            'nik'         => 'nullable|string|size:16|unique:employees,nik',
            'position'    => 'required|in:' . implode(',', Employee::POSITIONS),
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string',
            'joined_at'   => 'nullable|date',
            'base_salary' => 'nullable|numeric|min:0',
            //'status'      => 'sometimes|in:active,inactive',
            'photo'       => 'nullable|image|max:2048',
            'role_id'     => 'nullable|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.exists'   => 'Role yang dipilih tidak ditemukan.',
            'position.in'      => 'Posisi yang dipilih tidak valid.',
            'email.unique'     => 'Email sudah digunakan.',
        ];
    }
}