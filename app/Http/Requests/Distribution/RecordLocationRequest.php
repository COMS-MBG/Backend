<?php

namespace App\Http\Requests\Distribution;

use Illuminate\Foundation\Http\FormRequest;

class RecordLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['courier', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'latitude'        => ['required', 'numeric', 'between:-90,90'],
            'longitude'       => ['required', 'numeric', 'between:-180,180'],
            'speed_kmh'       => ['nullable', 'numeric', 'min:0', 'max:300'],
            'heading_degrees' => ['nullable', 'numeric', 'between:0,360'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}