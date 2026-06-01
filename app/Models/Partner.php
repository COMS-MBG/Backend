<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'partners';

    protected $fillable = [
        'school_name',
        'npsn',
        'school_type',
        'ownership_status',
        'address',
        'district',
        'city',
        'latitude',
        'longitude',
        'portion_count',
        'sppg_id',
    ];

    protected $casts = [
        'portion_count' => 'integer',
        'latitude'      => 'float',
        'longitude'     => 'float',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function sppg()
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeBySchoolType($query, string $type)
    {
        return $query->where('school_type', $type);
    }

    public function scopeByOwnershipStatus($query, string $status)
    {
        return $query->where('ownership_status', $status);
    }

    public function scopeByDistrict($query, string $district)
    {
        return $query->where('district', $district);
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeBySppg($query, string $sppgId)
    {
        return $query->where('sppg_id', $sppgId);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getIsPublicAttribute(): bool
    {
        return strtolower($this->ownership_status) === 'public';
    }

    public function getIsPrivateAttribute(): bool
    {
        return strtolower($this->ownership_status) === 'private';
    }
}
