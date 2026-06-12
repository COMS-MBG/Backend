<?php
namespace App\Services\SPPG;

use App\Models\SPPG;

class SPPGCapacityService
{
    public function isOvercapacity(SPPG $sppg): bool
    {
        return $sppg->schools()->count() >= $sppg->capacity;
    }

    public function getCapacityStatus(SPPG $sppg): array
    {
        $current = $sppg->schools()->where('status', 'active')->count();
        $max     = $sppg->capacity;

        return [
            'current'     => $current,
            'max'         => $max,
            'available'   => max(0, $max - $current),
            'percentage'  => $max > 0 ? round(($current / $max) * 100, 1) : 0,
            'is_full'     => $current >= $max,
            'is_critical' => $current >= ($max * 0.9), // >= 90%
        ];
    }

    public function getOvercapacitySppgs(): \Illuminate\Database\Eloquent\Collection
    {
        return SPPG::withCount(['schools' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->filter(fn($s) => $s->schools_count >= $s->capacity)
            ->values();
    }
}
