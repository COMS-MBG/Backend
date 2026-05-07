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

    // ─── Relationships ────────────────────────────────────────────────────────

    /** A permission can belong to many roles */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** Filter by module */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /** Filter by feature */
    public function scopeByFeature($query, string $feature)
    {
        return $query->where('feature', $feature);
    }
}