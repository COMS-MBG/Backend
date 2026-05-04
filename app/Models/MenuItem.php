<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * MODEL untuk tabel 'menu_items'.
 * Satu baris = satu resep yang dijadwalkan di hari tertentu dalam satu paket menu.
 */
class MenuItem extends Model
{
    use HasFactory;

    protected $table = 'menu_items';

    protected $fillable = [
        'menu_id',
        'recipe_id',
        'day_of_week',
        'menu_date',
        'order',
    ];

    protected $casts = [
        'menu_date'   => 'date',
        'day_of_week' => 'integer',
        'order'       => 'integer',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    // =============================================
    // ACCESSOR
    // =============================================

    /**
     * Nama hari dalam Bahasa Indonesia
     * Dipanggil: $menuItem->day_name → "Senin"
     */
    public function getDayNameAttribute(): string
    {
        return match ($this->day_of_week) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            default => 'Tidak Diketahui',
        };
    }
}