<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    /**
     * Posisi/jabatan struktural karyawan.
     * Dipakai untuk validasi di StoreEmployeeRequest / UpdateEmployeeRequest.
     * Ini label struktural, BUKAN role RBAC.
     */
    const POSITIONS = [
        'pemilik',
        'manajer',
        'ahli_gizi',
        'admin_logistik',
        'kurir',
        'karyawan_operasional',
    ];

    protected $fillable = [
        'sppg_id',
        'user_id',
        'role_id',
        'name',
        'nik',
        'position',
        'phone',
        'address',
        'photo',
        'joined_at',
        'base_salary',
        'status',
    ];

    protected $casts = [
        'joined_at'   => 'date',
        'base_salary' => 'decimal:2',
    ];

    protected $hidden = ['base_salary'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sppg(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    /**
     * Role sistem untuk akses fitur.
     * Berbeda dari 'position' yang hanya label struktural.
     */
    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id')
            ->withDefault([
                'name' => 'Tanpa Akses',
                'slug' => null,
            ]);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySppg($query, int $sppgId)
    {
        return $query->where('sppg_id', $sppgId);
    }

    public function scopeWithSystemAccess($query)
    {
        return $query->whereNotNull('user_id')->whereNotNull('role_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Cek apakah employee punya permission tertentu via role-nya.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if (!$this->role_id) return false;

        if (!$this->relationLoaded('role')) {
            $this->load('role.permissions');
        }

        return $this->role->permissions
            ->pluck('slug')
            ->contains($permissionSlug);
    }

    /**
     * Apakah karyawan ini punya akun dan role sistem?
     */
    public function hasSystemAccess(): bool
    {
        return $this->user_id !== null && $this->role_id !== null;
    }
}