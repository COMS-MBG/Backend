<?php

namespace App\Models;

use App\Models\SPPG;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;


class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sppg_id',
    ];

    // ── Auto slug ─────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Role $role) {
            $role->slug ??= Str::slug($role->name);
        });

        static::updating(function (Role $role) {
            if ($role->isDirty('name') && !$role->isDirty('slug')) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function sppg()
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function hasPermission(string $slug): bool
    {
        if (!$this->relationLoaded('permissions')) {
            $this->load('permissions');
        }

        return $this->permissions->contains('slug', $slug);
    }

    /**
     * Global role = belongs to super_admin, not tied to any SPPG.
     */
    public function isGlobal(): bool
    {
        return $this->sppg_id === null;
    }

    /**
     * For the role & permission management page.
     * Output: ['distribution' => ['delivery_schedule' => ['read', 'create']]]
     */
    public function permissionsGrouped(): array
    {
        return $this->permissions
            ->groupBy('module')
            ->map(fn($byModule) => $byModule
                ->groupBy('feature')
                ->map(fn($byFeature) => $byFeature->pluck('action')->toArray())
            )
            ->toArray();
    }
}