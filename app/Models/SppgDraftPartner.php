<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SppgDraftPartner extends Model
{
    use HasFactory;

    protected $table = 'sppg_draft_partners';

    protected $fillable = [
        'draft_id',
        'school_name',
        'npsn',
        'level',
        'school_status',
        'address',
        'city',
        'district',
        'latitude',
        'longitude',
        'jumlah_porsi',
        'data_source',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'jumlah_porsi' => 'integer',
    ];

    public function draft()
    {
        return $this->belongsTo(SppgDraft::class, 'draft_id');
    }
}
