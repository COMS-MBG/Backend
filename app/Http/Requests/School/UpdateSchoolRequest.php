<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('school.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'          => 'sometimes|string|max:255',
            'address'       => 'sometimes|string',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'student_count' => 'sometimes|integer|min:0',
            'school_level'  => 'sometimes|in:SD,SMP,SMA,SMK,MI,MTs,MA',
            'district'      => 'nullable|string|max:100',
            'city'          => 'nullable|string|max:100',
            'province'      => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'principal'     => 'nullable|string|max:255',
            'status'        => 'sometimes|in:active,inactive',
        ];
    }
}