<?php

namespace App\Http\Requests\Distribution;

use Illuminate\Foundation\Http\FormRequest;

class RejectDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['courier', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
            'rejection_photo'  => ['nullable', 'image', 'max:5120'], // 5 MB
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Rejection reason is required.',
            'rejection_reason.min'      => 'Please provide a detailed reason (at least 10 characters).',
        ];
    }
}