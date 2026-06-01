<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by route middleware
    }

    public function rules(): array
    {
        $partnerId = $this->route('partner') ?? $this->route('id');

        return [
            'school_name'      => 'sometimes|required|string|max:255',
            'npsn'             => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('partners', 'npsn')->ignore($partnerId),
            ],
            'school_type'      => 'sometimes|required|string|in:SD,SMP,SMA,SMK,MI,MTs,MA,MAK',
            'ownership_status' => 'sometimes|required|string|in:public,private',
            'address'          => 'nullable|string',
            'district'         => 'nullable|string|max:100',
            'city'             => 'nullable|string|max:100',
            'latitude'         => 'nullable|numeric|between:-90,90',
            'longitude'        => 'nullable|numeric|between:-180,180',
            'portion_count'    => 'sometimes|required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'school_name.required'      => 'School name is required.',
            'school_type.required'      => 'School type is required.',
            'school_type.in'            => 'Invalid school type. Must be one of: SD, SMP, SMA, SMK, MI, MTs, MA, MAK.',
            'ownership_status.required' => 'Ownership status is required.',
            'ownership_status.in'       => 'Ownership status must be either public or private.',
            'npsn.unique'               => 'This NPSN is already registered.',
            'portion_count.required'    => 'Portion count is required.',
            'portion_count.min'         => 'Portion count cannot be negative.',
        ];
    }
}
