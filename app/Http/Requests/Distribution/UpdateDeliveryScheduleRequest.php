<?php

namespace App\Http\Requests\Distribution;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin_logistik','admin_sppg']) ?? false;
    }

    public function rules(): array
    {
        return [
            'courier_id'     => ['sometimes', 'integer', 'exists:employees,id'],
            'school_id'      => ['sometimes', 'integer', 'exists:schools,id'],
            'vehicle_type'   => ['sometimes', 'string', 'in:motorcycle,car,van,truck'],
            'vehicle_plate'  => ['nullable', 'string', 'max:20'],
            'scheduled_at'   => ['sometimes', 'date', 'after:now'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}