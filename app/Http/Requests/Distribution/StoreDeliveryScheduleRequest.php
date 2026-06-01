<?php

namespace App\Http\Requests\Distribution;

use Illuminate\Foundation\Http\FormRequest;

/**
 * "PINTU MASUK" – validasi data form sebelum masuk ke database
 * POST /api/distribution/schedules
 */
class StoreDeliveryScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only Admin Logistik role allowed
        return $this->user()?->hasAnyRole(['admin_logistik', 'super_admin', 'admin_sppg']) ?? false;
    }

    public function rules(): array
    {
        return [
            'courier_id'     => ['required', 'integer', 'exists:employees,id'],
            'school_id'      => ['required', 'integer', 'exists:schools,id'],
            'vehicle_type'   => ['required', 'string', 'in:motorcycle,car,van,truck'],
            'vehicle_plate'  => ['nullable', 'string', 'max:20'],
            'scheduled_at'   => ['required', 'date', 'after:now'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'courier_id.exists'   => 'Selected courier does not exist.',
            'school_id.exists'    => 'Selected school does not exist.',
            'vehicle_type.in'     => 'Vehicle type must be one of: motorcycle, car, van, truck.',
            'scheduled_at.after'  => 'Scheduled time must be in the future.',
        ];
    }
}