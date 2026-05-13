<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
use HasApiTokens, HasFactory, HasRoles, Notifiable; //HasUuids, 

    protected $guard_name = 'web';


    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'profile_picture',
        'is_active',
        'role_type',
        'sppg_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /**
     * Employee record milik user ini.
     * Nullable — tidak semua user punya employee record (misal: super_admin).
     */
    public function employee(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    /**
     * SPPG yang diasosiasikan dengan user ini.
     * Nullable — super_admin tidak terikat ke SPPG manapun.
     */
    public function sppg(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Cek apakah user adalah super admin.
     * Super admin bypass semua RBAC tabel.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role_type === 'super_admin';
    }

    /**
     * Cek apakah user adalah sppg user.
     */
    public function isSppgUser(): bool
    {
        return $this->role_type === 'sppg_user';
    }

    /**
     * Cek permission user via employee → role → permissions.
     * Super admin bypass semua RBAC tabel.
     * SPPG user dicek via employee → role → permissions.
     *
     * Contoh: $user->hasPermission('employee.create')
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) return true;

        return $this->employee?->hasPermission($permissionSlug) ?? false;
    }

    /**
     * Cek apakah user punya salah satu role yang diminta.
     *
     * Kompatibel dengan semua call-site yang sebelumnya pakai Spatie.
     * Pengecekan:
     *   1. role_type (super_admin)
     *   2. employee → role → slug (admin_sppg, kurir, ahli_gizi, dll)
     *
     * Contoh: $user->hasAnyRole(['admin_logistik', 'super_admin'])
     */
    public function hasAnyRole(array|string $roles): bool
    {
        $roles = (array) $roles;

        // Cek role_type langsung (super_admin)
        if (in_array($this->role_type, $roles, true)) {
            return true;
        }

        // Cek employee → role → slug
        $roleSlug = $this->employee?->role?->slug;

        return $roleSlug && in_array($roleSlug, $roles, true);
    }

    /**
     * Pastikan user hanya bisa akses SPPG miliknya.
     * Super admin bisa akses semua SPPG.
     */
    public function ownsSppg(int $sppgId): bool
    {
        if ($this->isSuperAdmin()) return true;

        return (int) $this->sppg_id === $sppgId;
    }

    /**
     * Nama role yang ditampilkan ke FE.
     * Ambil dari employee.role, bukan dari role_type.
     */
    public function getRoleNameAttribute(): string
    {
        if ($this->isSuperAdmin()) return 'Super Admin';

        return $this->employee?->role?->name ?? 'Tanpa Akses';
    }
}