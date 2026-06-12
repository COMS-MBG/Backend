<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'profile_picture',
        'is_active', 'role_type', 'sppg_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    public function employee(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function sppg(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role_type === 'super_admin';
    }

    public function isSppgUser(): bool
    {
        return $this->role_type === 'sppg_user';
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) return true;
        return $this->employee?->hasPermission($permissionSlug) ?? false;
    }

    public function hasAnyRole(array|string $roles): bool
    {
        $roles = (array) $roles;
        if (in_array($this->role_type, $roles, true)) {
            return true;
        }

        $roleSlug = $this->employee?->role?->slug;
        if (!$roleSlug) {
            return false;
        }

        $normalizedRoles = [];
        foreach ($roles as $role) {
            $normalizedRoles[] = $role;
            if (in_array($role, ['courier', 'kurir'], true)) {
                array_push($normalizedRoles, 'courier', 'kurir');
            } elseif (in_array($role, ['logistics_admin', 'admin_logistik', 'admin-logistik'], true)) {
                array_push($normalizedRoles, 'logistics_admin', 'admin_logistik', 'admin-logistik');
            } elseif (in_array($role, ['sppg_admin', 'admin_sppg', 'admin-sppg'], true)) {
                array_push($normalizedRoles, 'sppg_admin', 'admin_sppg', 'admin-sppg');
            } elseif (in_array($role, ['nutritionist', 'ahli_gizi', 'ahli-gizi'], true)) {
                array_push($normalizedRoles, 'nutritionist', 'ahli_gizi', 'ahli-gizi');
            } elseif (in_array($role, ['owner', 'pemilik'], true)) {
                array_push($normalizedRoles, 'owner', 'pemilik');
            }
        }

        return in_array($roleSlug, $normalizedRoles, true);
    }

    public function ownsSppg(int $sppgId): bool
    {
        if ($this->isSuperAdmin()) return true;
        return (int) $this->sppg_id === $sppgId;
    }

    public function getRoleNameAttribute(): string
    {
        if ($this->isSuperAdmin()) return 'Super Admin';
        return $this->employee?->role?->name ?? 'Tanpa Akses';
    }
}