<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DeliverySchedule;
use App\Models\Employee;
use App\Models\School;
use App\Models\User;


class DeliveryHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_schedule_id',
        'courier_id',
        'school_id',
        'courier_name',
        'school_name',
        'school_address',
        'vehicle_type',
        'vehicle_plate',
        'departed_at',
        'arrived_at',
        'proof_photo_path',
        'route_snapshot',
        'distance_km',
        'confirmed_by',
        'confirmed_at',
        'notes',
    ];

    protected $casts = [
        'departed_at'    => 'datetime',
        'arrived_at'     => 'datetime',
        'confirmed_at'   => 'datetime',
        'route_snapshot' => 'array',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function schedule()
    {
        return $this->belongsTo(DeliverySchedule::class, 'delivery_schedule_id');
    }

    public function courier()
    {
        return $this->belongsTo(Employee::class, 'courier_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // ─── Computed ────────────────────────────────────────────────────────────

    public function getDurationMinutesAttribute(): ?int
    {
        if ($this->departed_at && $this->arrived_at) {
            return $this->departed_at->diffInMinutes($this->arrived_at);
        }
        return null;
    }
}