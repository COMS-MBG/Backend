<?php

namespace App\Models;

use App\Models\MenuItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MODEL untuk tabel 'menus' (Perencanaan Menu).
 * Berisi logika penentuan status menu secara dinamis berdasarkan tanggal.
 */
class Menu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'menus';

    protected $fillable = [
        'name',
        'week_start',
        'week_end',
        'status',
        'notes',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end'   => 'date',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Menu ini punya item (resep per hari) apa saja
     */
    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'menu_id')
            ->orderBy('day_of_week')
            ->orderBy('order');
    }

    /**
     * Eager load menu items beserta resepnya sekaligus
     */
    public function menuItemsWithRecipes()
    {
        return $this->hasMany(MenuItem::class, 'menu_id')
            ->with('recipe')
            ->orderBy('day_of_week')
            ->orderBy('order');
    }

    // =============================================
    // STATIC / BUSINESS LOGIC
    // =============================================

    /**
     * Hitung status menu berdasarkan tanggal hari ini.
     *
     * Aturan:
     * - H-0 s.d H-6  (mulai minggu ini atau kurang dari 7 hari lagi) → 'published'
     * - H-7 s.d H-13 (7-13 hari lagi)                                 → 'scheduled'
     * - H-14 ke atas  (14 hari atau lebih)                             → 'planned'
     * - Sudah lewat                                                     → 'archived'
     *
     * @param string|\Carbon\Carbon $weekStart Tanggal mulai menu
     * @return string
     */
    public static function computeStatus($weekStart): string
    {
        $start = Carbon::parse($weekStart)->startOfDay();
        $today = Carbon::today();

        if ($start->isPast() && !$start->isToday()) {
            return 'archived';
        }

        $daysUntilStart = $today->diffInDays($start, false); // false = bisa negatif

        if ($daysUntilStart < 0) return 'archived';
        if ($daysUntilStart <= 6) return 'published';
        if ($daysUntilStart <= 13) return 'scheduled';

        return 'planned';
    }

    // =============================================
    // ACCESSOR
    // =============================================

    /**
     * Label status dalam Bahasa Indonesia untuk FE
     * Dipanggil: $menu->status_label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => 'Menu Ditampilkan',
            'scheduled' => 'Menu Dijadwalkan',
            'planned'   => 'Menu Direncanakan',
            'archived'  => 'Menu Selesai',
            default     => 'Tidak Diketahui',
        };
    }

    /**
     * Nama hari dalam format human-readable
     * Dipanggil: $menu->week_range_label → "Senin, 07 Apr - Kamis, 10 Apr 2025"
     */
    public function getWeekRangeLabelAttribute(): string
    {
        return $this->week_start->translatedFormat('l, d M') .
               ' - ' .
               $this->week_end->translatedFormat('l, d M Y');
    }
}