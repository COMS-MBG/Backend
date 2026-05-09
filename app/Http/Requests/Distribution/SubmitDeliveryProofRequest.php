<?php

namespace App\Http\Requests\Distribution;

use Illuminate\Foundation\Http\FormRequest;

class SubmitDeliveryProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['courier', 'super_admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'proof_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'], // 10 MB
        ];
    }

    public function messages(): array
    {
        return [
            'proof_photo.required' => 'Delivery proof photo is required.',
            'proof_photo.image'    => 'Proof must be an image file.',
            'proof_photo.max'      => 'Photo must not exceed 10 MB.',
        ];
    }
}