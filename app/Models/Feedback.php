<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'name',
        'role',
        'message',
        'rating',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'rating'      => 'integer',
    ];

    /**
     * Scope: hanya ambil ulasan yang sudah disetujui moderator.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
