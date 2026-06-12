<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sppgId = request()->attributes->get('sppg_id');

        return [
            'school_name'      => 'required|string|max:255',
            'npsn'             => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('partners', 'npsn')->where(function ($query) use ($sppgId) {
                    return $query->where('sppg_id', $sppgId);
                }),
            ],
            'school_type'      => 'required|string|in:SD,SMP,SMA,SMK,MI,MTs,MA,MAK',
            'ownership_status' => 'required|string|in:public,private',
            'address'          => 'nullable|string',
            'district'         => 'nullable|string|max:100',
            'city'             => 'nullable|string|max:100',
            'latitude'         => 'nullable|numeric|between:-90,90',
            'longitude'        => 'nullable|numeric|between:-180,180',
            'portion_count'    => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'school_name.required'      => 'School name is required.',
            'school_type.required'      => 'School type is required.',
            'school_type.in'            => 'Invalid school type.',
            'ownership_status.required' => 'Ownership status is required.',
            'ownership_status.in'       => 'Ownership status must be either public or private.',
            'npsn.unique'               => 'This NPSN is already registered in your unit.',
            'portion_count.required'    => 'Portion count is required.',
            'portion_count.min'         => 'Portion count cannot be negative.',
        ];
    }
}