<?php

namespace App\Http\Requests\Nutrition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

/**
 * Validasi input untuk create/update Menu Planning.
 */
class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'week_start'  => 'required|date|date_format:Y-m-d',
            'week_end'    => 'required|date|date_format:Y-m-d|after_or_equal:week_start',
            'notes'       => 'nullable|string|max:2000',

            // Array items (resep per hari)
            'items'                   => 'required|array|min:1',
            'items.*.day_of_week'     => 'required|integer|between:1,4',
            // between:1,4 = hanya Senin(1) s.d Kamis(4)
            'items.*.menu_date'       => 'required|date|date_format:Y-m-d',
            'items.*.recipe_id'       => 'required|integer|exists:recipes,id',
            'items.*.order'           => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                 => 'Nama perencanaan menu wajib diisi.',
            'week_start.required'           => 'Tanggal mulai minggu wajib diisi.',
            'week_start.date_format'        => 'Format tanggal mulai harus Y-m-d (contoh: 2025-04-07).',
            'week_end.required'             => 'Tanggal akhir minggu wajib diisi.',
            'week_end.after_or_equal'       => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
            'items.required'                => 'Minimal tambahkan 1 item menu.',
            'items.*.day_of_week.between'   => 'Hari hanya boleh antara 1 (Senin) s.d 4 (Kamis).',
            'items.*.recipe_id.exists'      => 'Resep yang dipilih tidak ditemukan.',
            //'items.*.meal_time.in'          => 'Waktu makan hanya boleh: breakfast, lunch, dinner, snack.',
        ];
    }

    /**
     * Validasi tambahan setelah rules() - pastikan week_start adalah hari Senin
     */
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