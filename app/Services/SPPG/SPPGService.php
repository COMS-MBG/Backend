<?php

namespace App\Services\SPPG;

use App\Models\SPPG;
use App\Models\School;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SPPGService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SPPG::with(['owner'])
            ->withCount('partners')
            ->withSum('partners as total_porsi', 'portion_count');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $city = $filters['city'] ?? $filters['kota'] ?? null;
        if (!empty($city)) {
            $query->where(DB::raw('lower(city)'), 'like', '%' . strtolower($city) . '%');
        }
        $district = $filters['district'] ?? $filters['kecamatan'] ?? null;
        if (!empty($district)) {
            $query->where(DB::raw('lower(district)'), 'like', '%' . strtolower($district) . '%');
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

    public function create(array $data): SPPG
    {
        return DB::transaction(function () use ($data) {
            $sppg = SPPG::create($data);

            // Jika ada sekolah yang langsung diasosiasikan
            if (!empty($data['school_ids'])) {
                foreach ($data['school_ids'] as $schoolId) {
                    School::where('id', $schoolId)->update(['sppg_id' => $sppg->id]);
                    // TODO: sppg_schools pivot — cek migration sebelum uncomment
                    // $sppg->schools()->attach($schoolId, [
                    //     'joined_at' => now(),
                    //     'status'    => 'active',
                    // ]);
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

        // Lepas semua sekolah mitra dulu
        $sppg->schools()->update(['sppg_id' => null]);
        $sppg->delete();
    }

    public function assignSchool(string $sppgId, string $schoolId): void
    {
        $sppg   = SPPG::findOrFail($sppgId);
        $school = School::findOrFail($schoolId);

        DB::transaction(function () use ($sppg, $school) {
            // Lepas dari SPPG lama kalau ada
            if ($school->sppg_id && $school->sppg_id !== $sppg->id) {
                \App\Models\SPPGSchool::where('school_id', $school->id)
                    ->where('sppg_id', $school->sppg_id)
                    ->update(['status' => 'pindah']);
            }

            $school->update(['sppg_id' => $sppg->id]);

            \App\Models\SPPGSchool::updateOrCreate(
                ['sppg_id' => $sppg->id, 'school_id' => $school->id],
                ['tanggal_bergabung' => now(), 'status' => 'aktif']
            );
        });
    }

    public function detachSchool(string $sppgId, string $schoolId): void
    {
        School::where('id', $schoolId)->where('sppg_id', $sppgId)
              ->update(['sppg_id' => null]);

        \App\Models\SPPGSchool::where('sppg_id', $sppgId)
            ->where('school_id', $schoolId)
            ->update(['status' => 'nonaktif']);
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