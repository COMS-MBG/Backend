<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'module',
        'feature',
        'action',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByFeature($query, string $feature)
    {
        return $query->where('feature', $feature);
    }
}