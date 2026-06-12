<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'sppg_id',
        'name',
        'email',
        'rating',
        'comment',
        'is_approved',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'is_approved' => 'boolean',
    ];

    // ── Relations ─────────────────────────────────────────────────
    public function sppg()
    {
        return $this->belongsTo(SPPG::class, 'sppg_id');
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    // ── Accessor: Nama yang disamarkan ────────────────────────────
    // "Mahardhitya Pratama" → "M********** P******"
    public function getMaskedNameAttribute(): string
    {
        return self::maskName($this->name);
    }

    /**
     * Helper statis untuk menyamarkan nama.
     * "Mahardhitya Pratama" → "M********** P******"
     */
    public static function maskName(string $name): string
    {
        $words = explode(' ', $name);
        $masked = [];
        foreach ($words as $word) {
            if (mb_strlen($word) > 1) {
                $masked[] = mb_substr($word, 0, 1) . str_repeat('*', mb_strlen($word) - 1);
            } else {
                $masked[] = $word;
            }
        }
        return implode(' ', $masked);
    }
}
