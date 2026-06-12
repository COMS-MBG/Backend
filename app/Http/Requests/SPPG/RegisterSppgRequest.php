<?php

namespace App\Http\Requests\SPPG;

use Illuminate\Foundation\Http\FormRequest;

class RegisterSppgRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role_type === 'super_admin';
    }

    public function rules(): array
    {
        return [
            // Section 1: SPPG
            'sppg' => 'required|array',
            'sppg.name' => 'required|string|max:255',
            'sppg.address' => 'required|string',
            'sppg.district' => 'required|string|max:100',
            'sppg.city' => 'required|string|max:100',
            'sppg.province' => 'required|string|max:100',
            'sppg.latitude' => 'required|numeric|between:-90,90',
            'sppg.longitude' => 'required|numeric|between:-180,180',
            'sppg.capacity' => 'required|integer|min:1',

            // Section 2: Mitra (min 1)
            'partners' => 'required|array|min:1',
            'partners.*.id' => 'nullable|uuid|exists:partners,id',
            'partners.*.school_name' => 'required_without:partners.*.id|string|max:255',
            'partners.*.npsn' => 'nullable|string|max:20',
            'partners.*.school_type' => 'required_without:partners.*.id|string|max:50',
            'partners.*.ownership_status' => 'required_without:partners.*.id|string|in:public,private',
            'partners.*.address' => 'nullable|string',
            'partners.*.district' => 'nullable|string|max:100',
            'partners.*.city' => 'nullable|string|max:100',
            'partners.*.latitude' => 'required_without:partners.*.id|numeric|between:-90,90',
            'partners.*.longitude' => 'required_without:partners.*.id|numeric|between:-180,180',
            'partners.*.portion_count' => 'required_without:partners.*.id|integer|min:0',

            // Section 3: Admin SPPG
            'admin_sppg' => 'required|array',
            'admin_sppg.name' => 'required|string|max:255',
            'admin_sppg.email' => 'required|email|unique:users,email',
            'admin_sppg.password' => 'required|string|min:8',

            // Section 4: Nutritionist (Optional)
            'nutritionist'          => 'nullable|array',
            'nutritionist.name'     => 'required_with:nutritionist.email|string|max:255',
            'nutritionist.email'    => 'required_with:nutritionist.name|email|unique:users,email',
            'nutritionist.password' => 'required_with:nutritionist.email|string|min:8',

            // Section 5: Logistics Admin (Optional)
            'logistics_admin'          => 'nullable|array',
            'logistics_admin.name'     => 'required_with:logistics_admin.email|string|max:255',
            'logistics_admin.email'    => 'required_with:logistics_admin.name|email|unique:users,email',
            'logistics_admin.password' => 'required_with:logistics_admin.email|string|min:8',
        ];
    }
}
