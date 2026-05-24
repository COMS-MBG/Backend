<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingContent extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'landing_contents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'section_name',
        'title',
        'description',
        'image_path',
        'is_active',
    ];

    /**
     * Scope a query to only include active landing contents.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
