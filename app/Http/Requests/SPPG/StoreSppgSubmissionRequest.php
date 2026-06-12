<?php

namespace App\Http\Requests\SPPG;

use Illuminate\Foundation\Http\FormRequest;

class StoreSppgSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ─── SPPG DATA (form1_data) ──────────────────────────────────────
            'form1_data'               => 'sometimes|array',
            'form1_data.name'          => 'required_with:form1_data|string|min:3|max:100',
            'form1_data.address'       => 'required_with:form1_data|string|min:10|max:255',
            'form1_data.district'      => 'required_with:form1_data|string|min:3|max:100',
            'form1_data.city'          => 'required_with:form1_data|string|min:3|max:100',
            'form1_data.province'      => 'required_with:form1_data|string|min:3|max:100',
            'form1_data.capacity'      => 'required_with:form1_data|integer|min:1|max:9999',

            // ─── ADMIN SPPG DATA (form2_data) ────────────────────────────────
            'form2_data'               => 'sometimes|array',
            'form2_data.name'          => 'required_with:form2_data|string|min:3|max:100',
            'form2_data.email'         => 'required_with:form2_data|email|unique:users,email',
            'form2_data.password'      => 'required_with:form2_data|string|min:8',

            // ─── MITRA SEKOLAH (partners) ────────────────────────────────────
            'partners'                 => 'sometimes|array',
            'partners.*.school_name'   => 'required|string|min:3|max:150',
            'partners.*.address'       => 'required|string|min:10|max:255',
            'partners.*.district'      => 'required|string|min:3|max:100',
            'partners.*.city'          => 'required|string|min:3|max:100',
            'partners.*.jumlah_porsi'  => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'form1_data.address.min'   => 'Alamat SPPG minimal 10 karakter (contoh: Jl. Raya Cibiru No. 45)',
            'form1_data.address.required_with' => 'Alamat SPPG wajib diisi',
            'partners.*.address.min'   => 'Alamat sekolah minimal 10 karakter',
            'partners.*.address.required' => 'Alamat sekolah wajib diisi',
        ];
    }

    public function prepareForValidation(): void
    {
        // Auto-trim whitespace
        if ($this->has('form1_data.address')) {
            $form1 = $this->input('form1_data', []);
            $form1['address'] = trim($form1['address'] ?? '');
            $this->merge(['form1_data' => $form1]);
        }

        if ($this->has('partners')) {
            $partners = collect($this->input('partners'))->map(function ($p) {
                return array_merge($p, [
                    'address'  => trim($p['address'] ?? ''),
                    'district' => trim($p['district'] ?? ''),
                    'city'     => trim($p['city'] ?? ''),
                ]);
            })->toArray();
            $this->merge(['partners' => $partners]);
        }
    }
}