<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'schools';

    protected $fillable = [
        'nama',
        'alamat',
        'latitude',
        'longitude',
        'jumlah_siswa',
        'jenjang',        // SD, SMP, SMA, SMK
        'kecamatan',
        'kota',
        'provinsi',
        'telepon',
        'kepala_sekolah',
        'sppg_id',
        'status',
    ];

    protected $casts = [
        'latitude'     => 'float',
        'longitude'    => 'float',
        'jumlah_siswa' => 'integer',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function sppg()
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    // public function distributions()
    // {
    //     return $this->hasMany(Distribution::class, 'school_id');
    // }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeWithoutSppg($query)
    {
        return $query->whereNull('sppg_id');
    }

    public function scopeBySppg($query, string $sppgId)
    {
        return $query->where('sppg_id', $sppgId);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getCoordinatesAttribute(): array
    {
        return ['lat' => $this->latitude, 'lng' => $this->longitude];
    }

    public function getHasSppgAttribute(): bool
    {
        return !is_null($this->sppg_id);
    }

    // Hitung jarak ke SPPG yang melayani (km)
    public function distanceToSppg(): ?float
    {
        if (!$this->sppg) {
            return null;
        }

        $lat1 = deg2rad($this->latitude);
        $lon1 = deg2rad($this->longitude);
        $lat2 = deg2rad($this->sppg->latitude);
        $lon2 = deg2rad($this->sppg->longitude);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
        $c = 2 * asin(sqrt($a));

        return round(6371 * $c, 2);
    }
}