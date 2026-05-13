<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SPPG extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 's_p_p_g_s';

    protected $fillable = [
        'name',
        'address',
        'district',
        'city',
        'province',
        'latitude',
        'longitude',
        'capacity',
        'phone',
        'email',
        'status',
        'pemilik_id',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'capacity' => 'integer',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function owner()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    public function schools()
    {
        return $this->hasMany(School::class, 'sppg_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'sppg_id');
    }

    public function roles()
    {
        return $this->hasMany(Role::class, 'sppg_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 10)
    {
        return $query->selectRaw("
            *,
            (6371 * acos(
                cos(radians(?)) * cos(radians(latitude))
                * cos(radians(longitude) - radians(?))
                + sin(radians(?)) * sin(radians(latitude))
            )) AS distance_km
        ", [$lat, $lng, $lat])
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getCoordinatesAttribute(): array
    {
        return ['lat' => $this->latitude, 'lng' => $this->longitude];
    }

    public function getIsOvercapacityAttribute(): bool
    {
        return $this->schools()->count() >= $this->capacity;
    }
}