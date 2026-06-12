<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('school.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'npsn'          => 'nullable|string|max:50|unique:schools,npsn',
            'name'          => 'required|string|max:255',
            'address'       => 'required|string',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'student_count' => 'required|integer|min:0',
            'school_level'  => 'required|in:SD,SMP,SMA,SMK,MI,MTs,MA',
            'district'      => 'nullable|string|max:100',
            'city'          => 'nullable|string|max:100',
            'province'      => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:20',
            'principal'     => 'nullable|string|max:255',
            'sppg_id'       => 'nullable|uuid|exists:sppgs,id',
            'status'        => 'sometimes|in:active,inactive',
        ];
    }
}