<?php

namespace App\Services\School;

use App\Models\School;
use Illuminate\Pagination\LengthAwarePaginator;

class SchoolService
{
    public function getAll(array $filters = [], int $perPage = 15, ?string $sppgId = null): LengthAwarePaginator
    {
        $query = School::with('sppg');

        // Batasi per SPPG kalau admin SPPG yang akses
        if ($sppgId) {
            $query->where('sppg_id', $sppgId);
        }

        if (!empty($filters['sppg_id'])) {
            $query->where('sppg_id', $filters['sppg_id']);
        }
        if (!empty($filters['jenjang'])) {
            $query->where('jenjang', $filters['jenjang']);
        }
        if (!empty($filters['kota'])) {
            $query->where('kota', 'ilike', "%{$filters['kota']}%");
        }
        if (!empty($filters['search'])) {
            $query->where('nama', 'ilike', "%{$filters['search']}%");
        }
        if (isset($filters['tanpa_sppg']) && $filters['tanpa_sppg']) {
            $query->whereNull('sppg_id');
        }

        return $query->orderBy('nama')->paginate($perPage);
    }

    public function findById(string $id, ?string $sppgId = null): School
    {
        $query = School::with('sppg');
        if ($sppgId) {
            $query->where('sppg_id', $sppgId);
        }
        return $query->findOrFail($id);
    }

    public function create(array $data): School
    {
        return School::create($data);
    }

    public function update(string $id, array $data, ?string $sppgId = null): School
    {
        $school = $this->findById($id, $sppgId);
        $school->update($data);
        return $school->fresh('sppg');
    }

    public function delete(string $id, ?string $sppgId = null): void
    {
        $school = $this->findById($id, $sppgId);
        $school->delete();
    }
}