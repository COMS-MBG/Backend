<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class ImportPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by route middleware
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // max 10MB
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'An import file is required.',
            'file.mimes'    => 'File must be in CSV, XLSX, or XLS format.',
            'file.max'      => 'File size must not exceed 10MB.',
        ];
    }
}
