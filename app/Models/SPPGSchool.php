<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPPGSchool extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'sppg_schools';

    protected $fillable = [
        'sppg_id',
        'school_id',
        'tanggal_bergabung',
        'status',         // aktif, nonaktif, pindah
        'catatan',
    ];

    protected $casts = [
        'tanggal_bergabung' => 'date',
    ];

    public function sppg()
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}