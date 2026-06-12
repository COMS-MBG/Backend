<?php

namespace App\Http\Requests\Nutrition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sppgId = request()->attributes->get('sppg_id');

        return [
            'name'                => 'required|string|max:255',
            'week_start'          => 'required|date|date_format:Y-m-d',
            'week_end'            => 'required|date|date_format:Y-m-d|after_or_equal:week_start',
            'notes'               => 'nullable|string|max:2000',
            'items'               => 'required|array|min:1',
            'items.*.day_of_week' => 'required|integer|between:1,4',
            'items.*.menu_date'   => 'required|date|date_format:Y-m-d',
            'items.*.recipe_id'   => [
                'required',
                'integer',
                Rule::exists('recipes', 'id')->where(function ($query) use ($sppgId) {
                    return $query->where('sppg_id', $sppgId);
                })
            ],
            'items.*.order'       => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                 => 'Nama perencanaan menu wajib diisi.',
            'week_start.required'           => 'Tanggal mulai minggu wajib diisi.',
            'week_start.date_format'        => 'Format tanggal mulai harus Y-m-d.',
            'week_end.required'             => 'Tanggal akhir minggu wajib diisi.',
            'week_end.after_or_equal'       => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
            'items.required'                => 'Minimal tambahkan 1 item menu.',
            'items.*.day_of_week.between'   => 'Hari hanya boleh antara 1 (Senin) s.d 4 (Kamis).',
            'items.*.recipe_id.exists'      => 'Resep yang dipilih tidak ditemukan di data unit Anda.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('week_start')) {
                $weekStart = Carbon::parse($this->week_start);
                if ($weekStart->dayOfWeek !== Carbon::MONDAY) {
                    $validator->errors()->add('week_start', 'Tanggal mulai harus hari Senin.');
                }
            }

            if ($this->filled('week_end')) {
                $weekEnd = Carbon::parse($this->week_end);
                if ($weekEnd->dayOfWeek !== Carbon::THURSDAY) {
                    $validator->errors()->add('week_end', 'Tanggal akhir harus hari Kamis.');
                }
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}