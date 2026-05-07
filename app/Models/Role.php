<?php

namespace App\Models;

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
    ];

    // ─── Auto-generate slug from name ────────────────────────────────────────
    protected static function booted(): void
    {
        static::creating(function (Role $role) {
            $role->slug = Str::slug($role->name);
        });

        static::updating(function (Role $role) {
            if ($role->isDirty('name')) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    /** A role can have many permissions (many-to-many) */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /** A role can be assigned to many employees */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Check if this role has a specific permission slug */
    public function hasPermission(string $slug): bool
    {
        return $this->permissions->contains('slug', $slug);
    }

    /** Group permissions by module+feature for easy display */
    public function permissionsGrouped(): array
    {
        return $this->permissions
            ->groupBy('feature')
            ->map(fn ($perms) => $perms->pluck('action')->toArray())
            ->toArray();
    }
}