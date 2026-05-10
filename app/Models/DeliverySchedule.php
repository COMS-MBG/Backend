<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliverySchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'courier_id',
        'school_id',
        'assigned_by',
        'submitted_by',
        'vehicle_type',
        'vehicle_plate',
        'status',
        'scheduled_at',
        'departed_at',
        'arrived_at',
        'delivery_notes',
        'rejection_reason',
        'rejection_photo_path',
        'rejected_at',
        'proof_photo_path',
        'proof_submitted_at',
        'confirmed_by',
        'confirmed_at',
        'confirmation_notes',
        'route_snapshot',
    ];

    protected $casts = [
        'scheduled_at'      => 'datetime',
        'departed_at'       => 'datetime',
        'arrived_at'        => 'datetime',
        'rejected_at'       => 'datetime',
        'proof_submitted_at'=> 'datetime',
        'confirmed_at'      => 'datetime',
        'route_snapshot'    => 'array',
    ];

    // Status constants
    const STATUS_IN_ORDER          = 'in_order';
    const STATUS_ACCEPTED          = 'accepted';
    const STATUS_REJECTED          = 'rejected';
    const STATUS_DELIVERING        = 'delivering';
    const STATUS_DELIVERED         = 'delivered';
    const STATUS_CONFIRMED         = 'confirmed';
    const STATUS_REVISION_REQUIRED = 'revision_required';

    // Vehicle type constants
    const VEHICLE_MOTORCYCLE = 'motorcycle';
    const VEHICLE_CAR        = 'car';
    const VEHICLE_VAN        = 'van';
    const VEHICLE_TRUCK      = 'truck';

    // ─── Relationships ───────────────────────────────────────────────────────

    public function courier()
    {
        return $this->belongsTo(Employee::class, 'courier_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function locations()
    {
        return $this->hasMany(CourierLocation::class);
    }

    public function latestLocation()
    {
        return $this->hasOne(CourierLocation::class)->latestOfMany('recorded_at');
    }

    public function history()
    {
        return $this->hasOne(DeliveryHistory::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_IN_ORDER,
            self::STATUS_ACCEPTED,
            self::STATUS_DELIVERING,
            self::STATUS_DELIVERED,
            self::STATUS_REVISION_REQUIRED,
        ]);
    }

    public function scopeForCourier($query, int $courierId)
    {
        return $query->where('courier_id', $courierId);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_IN_ORDER, self::STATUS_REJECTED]);
    }
}