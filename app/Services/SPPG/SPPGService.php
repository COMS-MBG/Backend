<?php

namespace App\Services\SPPG;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Partner;
use App\Models\School;
use App\Models\SPPG;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SPPGService
{
    // ─── Daftar SPPG ─────────────────────────────────────────────────────────

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SPPG::with(['owner'])
            ->withCount('partners')
            ->withSum('partners as total_porsi', 'portion_count');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Support only English keys (city / district) — drop old Indo aliases
        if (!empty($filters['city'])) {
            $query->where(DB::raw('lower(city)'), 'like', '%' . strtolower($filters['city']) . '%');
        }
        if (!empty($filters['district'])) {
            $query->where(DB::raw('lower(district)'), 'like', '%' . strtolower($filters['district']) . '%');
        }
        if (!empty($filters['search'])) {
            $query->where(DB::raw('lower(name)'), 'like', '%' . strtolower($filters['search']) . '%');
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function findById(string $id): SPPG
    {
        return SPPG::with(['owner', 'schools', 'employees'])->findOrFail($id);
    }

    // ─── Region Helpers (dependent dropdown) ─────────────────────────────────

    /**
     * Return distinct cities that have at least one SPPG.
     * Sorted alphabetically.
     */
    public function getAvailableCities(): array
    {
        return SPPG::whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->values()
            ->toArray();
    }

    /**
     * Return distinct districts that belong to the given city (from SPPG records).
     */
    public function getAvailableDistricts(string $city): array
    {
        return SPPG::where(DB::raw('lower(city)'), strtolower($city))
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->distinct()
            ->orderBy('district')
            ->pluck('district')
            ->values()
            ->toArray();
    }

    // ─── Partners Tab ─────────────────────────────────────────────────────────

    /**
     * Get all partner (mitra) data for a specific SPPG.
     * Used for the "Mitra" tab on the detail page.
     */
    public function getPartners(string $sppgId): Collection
    {
        return Partner::where('sppg_id', $sppgId)
            ->select([
                'id', 'school_name', 'npsn', 'school_type', 'ownership_status',
                'address', 'district', 'city',
                'latitude', 'longitude', 'portion_count',
            ])
            ->orderBy('school_name')
            ->get();
    }

    // ─── Menu Tab ─────────────────────────────────────────────────────────────

    /**
     * Get all menus for a specific SPPG, grouped by period (week).
     * Order: published → scheduled → planned → archived
     * Within each period, days are sorted Mon–Thu.
     */
    public function getMenusGrouped(string $sppgId): array
    {
        $statusOrder = [
            'published' => 1,
            'scheduled' => 2,
            'planned'   => 3,
            'archived'  => 4,
        ];

        $dayNames = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];

        $menus = Menu::where('sppg_id', $sppgId)
            ->with([
                'menuItems' => fn($q) => $q
                    ->with('recipe:id,name,total_calorie,total_protein,total_carbohydrate,total_fat')
                    ->orderBy('day_of_week')
                    ->orderBy('meal_time')
                    ->orderBy('order'),
            ])
            ->get()
            ->sortBy(fn($m) => [$statusOrder[$m->status] ?? 99, $m->week_start])
            ->values();

        $periods = [];
        foreach ($menus as $index => $menu) {
            $days = [];
            foreach ($menu->menuItems as $item) {
                $days[] = [
                    'day_of_week'     => $item->day_of_week,
                    'day_name'        => $dayNames[$item->day_of_week] ?? "Day {$item->day_of_week}",
                    'menu_date'       => $item->menu_date,
                    'meal_time'       => $item->meal_time,
                    'recipe_id'       => $item->recipe_id,
                    'recipe_name'     => $item->recipe?->name,
                    'calories_kcal'   => $item->recipe?->total_calorie
                        ? round($item->recipe->total_calorie) . ' kcal'
                        : null,
                    'total_calorie'   => $item->recipe?->total_calorie,
                    'total_protein'   => $item->recipe?->total_protein,
                    'total_carbs'     => $item->recipe?->total_carbohydrate,
                    'total_fat'       => $item->recipe?->total_fat,
                ];
            }

            $periods[] = [
                'period_index'  => $index + 1,
                'menu_id'       => $menu->id,
                'menu_name'     => $menu->name,
                'week_start'    => $menu->week_start?->toDateString(),
                'week_end'      => $menu->week_end?->toDateString(),
                'status'        => $menu->status,
                'status_label'  => match ($menu->status) {
                    'published' => 'Published',
                    'scheduled' => 'Scheduled',
                    'planned'   => 'Planned',
                    'archived'  => 'Archived',
                    default     => 'Unknown',
                },
                'recipes_by_day' => $days,
            ];
        }

        return $periods;
    }

    // ─── CRUD ─────────────────────────────────────────────────────────────────

    public function create(array $data): SPPG
    {
        return DB::transaction(function () use ($data) {
            $sppg = SPPG::create($data);

            if (!empty($data['school_ids'])) {
                foreach ($data['school_ids'] as $schoolId) {
                    School::where('id', $schoolId)->update(['sppg_id' => $sppg->id]);
                }
            }

            return $sppg->fresh(['owner', 'schools']);
        });
    }

    public function update(string $id, array $data): SPPG
    {
        $sppg = SPPG::findOrFail($id);
        $sppg->update($data);
        return $sppg->fresh(['owner', 'schools']);
    }

    public function delete(string $id): void
    {
        $sppg = SPPG::findOrFail($id);
        $sppg->schools()->update(['sppg_id' => null]);
        $sppg->delete();
    }

    public function assignSchool(string $sppgId, string $schoolId): void
    {
        $sppg   = SPPG::findOrFail($sppgId);
        $school = School::findOrFail($schoolId);

        DB::transaction(function () use ($sppg, $school) {
            if ($school->sppg_id && $school->sppg_id !== $sppg->id) {
                \App\Models\SPPGSchool::where('school_id', $school->id)
                    ->where('sppg_id', $school->sppg_id)
                    ->update(['status' => 'transferred']);
            }

            $school->update(['sppg_id' => $sppg->id]);

            \App\Models\SPPGSchool::updateOrCreate(
                ['sppg_id' => $sppg->id, 'school_id' => $school->id],
                ['joined_at' => now(), 'status' => 'active']
            );

            // Sync with partners table by NPSN
            if ($school->npsn) {
                Partner::where('npsn', $school->npsn)->update(['sppg_id' => $sppg->id]);
            }
        });
    }

    public function detachSchool(string $sppgId, string $schoolId): void
    {
        DB::transaction(function () use ($sppgId, $schoolId) {
            // 1. If schoolId is a UUID, it is a Partner ID
            if (\Illuminate\Support\Str::isUuid($schoolId)) {
                $partner = Partner::where('id', $schoolId)->where('sppg_id', $sppgId)->first();
                if ($partner) {
                    $partner->update(['sppg_id' => null]);

<<<<<<< Updated upstream
                    // Sync with schools table by NPSN
                    if ($partner->npsn) {
                        $school = School::where('npsn', $partner->npsn)->where('sppg_id', $sppgId)->first();
                        if ($school) {
                            $school->update(['sppg_id' => null]);

                            \App\Models\SPPGSchool::where('sppg_id', $sppgId)
                                ->where('school_id', $school->id)
                                ->update(['status' => 'inactive']);
                        }
                    }
                }
            } else {
                // 2. Otherwise it is a bigint School ID
                $school = School::where('id', $schoolId)->where('sppg_id', $sppgId)->first();
                if ($school) {
                    $school->update(['sppg_id' => null]);

                    \App\Models\SPPGSchool::where('sppg_id', $sppgId)
                        ->where('school_id', $schoolId)
                        ->update(['status' => 'inactive']);

                    // Sync with partners table by NPSN
                    if ($school->npsn) {
                        Partner::where('npsn', $school->npsn)
                            ->where('sppg_id', $sppgId)
                            ->update(['sppg_id' => null]);
                    }
                }
            }
        });
=======
        \App\Models\SPPGSchool::where('sppg_id', $sppgId)
            ->where('school_id', $schoolId)
            ->update(['status' => 'inactive']);
>>>>>>> Stashed changes
    }

    public function getSummaryStats(): array
    {
        return [
            'total'      => SPPG::count(),
            'active'     => SPPG::where('status', 'active')->count(),
            'inactive'   => SPPG::where('status', 'inactive')->count(),
            'pending'    => SPPG::where('status', 'pending')->count(),
            'overcapacity' => SPPG::withCount('schools')
                ->get()
                ->filter(fn($s) => $s->schools_count >= $s->capacity)
                ->count(),
        ];
    }
}