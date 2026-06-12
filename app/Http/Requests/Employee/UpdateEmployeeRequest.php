<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('employee.update') ?? false;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');
        $employeeId = $employee instanceof Employee ? $employee->id : $employee;

        return [
            'name'        => 'sometimes|string|max:255',
            'nik'         => "nullable|string|size:16|unique:employees,nik,{$employeeId}",
            'position'    => 'sometimes|in:' . implode(',', Employee::POSITIONS),
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
        ];
    }
}