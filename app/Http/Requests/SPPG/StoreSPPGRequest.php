<?php

namespace App\Http\Requests\SPPG;

use Illuminate\Foundation\Http\FormRequest;

class StoreSPPGRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('sppg.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'address'     => 'required|string',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'capacity'    => 'required|integer|min:1|max:100',
            'status'      => 'sometimes|in:active,inactive,pending',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'district'    => 'nullable|string|max:100',
            'city'        => 'nullable|string|max:100',
            'province'    => 'nullable|string|max:100',
            'pemilik_id'  => 'nullable|exists:users,id',
            'school_ids'  => 'nullable|array',
            'school_ids.*' => 'exists:schools,id',
        ];
    }
}