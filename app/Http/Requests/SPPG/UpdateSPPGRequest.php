<?php

namespace App\Http\Requests\SPPG;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSPPGRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('sppg.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'        => 'sometimes|string|max:255',
            'address'     => 'sometimes|string',
            'latitude'    => 'sometimes|numeric|between:-90,90',
            'longitude'   => 'sometimes|numeric|between:-180,180',
            'capacity'    => 'sometimes|integer|min:1|max:100',
            'status'      => 'sometimes|in:active,inactive,pending',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'district'    => 'nullable|string|max:100',
            'city'        => 'nullable|string|max:100',
            'province'    => 'nullable|string|max:100',
            'pemilik_id'  => 'nullable|exists:users,id',
        ];
    }
}