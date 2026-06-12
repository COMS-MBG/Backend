<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SppgDraft extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sppg_drafts';

    protected $fillable = [
        'submission_number',
        'submitted_by',
        'source',
        'form1_data',
        'form2_data',
        'form3_data',
        'latitude',
        'longitude',
        'confirmed_latitude',
        'confirmed_longitude',
        'point_status',
        'map_confirmed',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'form1_data' => 'array',
        'form2_data' => 'array',
        'form3_data' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'confirmed_latitude' => 'float',
        'confirmed_longitude' => 'float',
        'map_confirmed' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function partners()
    {
        return $this->hasMany(SppgDraftPartner::class, 'draft_id');
    }
}
