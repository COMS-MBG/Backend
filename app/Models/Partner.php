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
        'nama_sekolah',
        'npsn',
        'bentuk',
        'status',
        'alamat',
        'kecamatan',
        'kabupaten_kota',
        'latitude',
        'longitude',
        'jumlah_porsi',
        'sppg_id',
    ];

    protected $casts = [
        'jumlah_porsi' => 'integer',
        'latitude'     => 'float',
        'longitude'    => 'float',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function sppg()
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeByBentuk($query, string $bentuk)
    {
        return $query->where('bentuk', $bentuk);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByKecamatan($query, string $kecamatan)
    {
        return $query->where('kecamatan', $kecamatan);
    }

    public function scopeByKabupatenKota($query, string $kota)
    {
        return $query->where('kabupaten_kota', $kota);
    }

    public function scopeBySppg($query, string $sppgId)
    {
        return $query->where('sppg_id', $sppgId);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getIsNegeriAttribute(): bool
    {
        return strtolower($this->status) === 'negeri';
    }
}
