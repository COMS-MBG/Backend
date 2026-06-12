<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role')->id;

        return [
            'name'          => ['required', 'string', 'max:255', "unique:roles,name,{$roleId}"],
            'description'   => ['nullable', 'string', 'max:500'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Role name is required.',
            'name.unique'          => 'This role name already exists.',
            'permissions.*.exists' => 'One or more selected permissions are invalid.',
        ];
    }
}