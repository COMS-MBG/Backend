<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable; //HasUuids, 

    protected $fillable = [
        'nama',
        'email',
        'password',
        'sppg_id',
        'is_active',
        'foto',
        'telepon',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function sppg()
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isPemilik(): bool
    {
        return $this->hasRole('pemilik');
    }

    public function isManajer(): bool
    {
        return $this->hasRole('manajer');
    }

    public function isAhliGizi(): bool
    {
        return $this->hasRole('ahli_gizi');
    }

    public function isAdminLogistik(): bool
    {
        return $this->hasRole('admin_logistik');
    }

    public function isKurir(): bool
    {
        return $this->hasRole('kurir');
    }

    public function getSppgIdAttribute(): ?string
    {
        return $this->attributes['sppg_id'] ?? null;
    }
}