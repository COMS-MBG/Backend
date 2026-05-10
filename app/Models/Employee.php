<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    const ROLES = [
        'pemilik',
        'manajer',
        'ahli_gizi',
        'admin_logistik',
        'kurir',
        'karyawan_operasional',
    ];

    protected $fillable = [
        'user_id',
        'sppg_id',
        'nama',
        'nik',
        'jabatan',
        'telepon',
        'alamat',
        'tanggal_bergabung',
        'gaji_pokok',
        'status',
        'foto',
        'role_id',      // ← tambahan baru: FK ke tabel roles (untuk permission system)
    ];

    protected $casts = [
        'tanggal_bergabung' => 'date',
        'gaji_pokok'        => 'decimal:2',
    ];

    protected $hidden = ['gaji_pokok'];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sppg()
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    /**
     * Relasi ke Role (untuk sistem permission).
     * Berbeda dengan kolom 'jabatan' yang hardcoded di const ROLES,
     * role_id ini menghubungkan ke tabel roles untuk mengatur akses fitur.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id')->withDefault([
            'name' => 'No Role Assigned',
        ]);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeKurir($query)
    {
        return $query->where('jabatan', 'kurir');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeBySppg($query, string $sppgId)
    {
        return $query->where('sppg_id', $sppgId);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function hasPermission(string $permissionSlug): bool
    {
        if (!$this->role_id) {
            return false;
        }
        return $this->role->hasPermission($permissionSlug);
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }
}