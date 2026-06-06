<?php

namespace App\Services\School;

use App\Models\School;
use Illuminate\Pagination\LengthAwarePaginator;

class SchoolService
{
    public function getAll(array $filters = [], int $perPage = 15, ?string $sppgId = null): LengthAwarePaginator
    {
        $query = School::with('sppg');

        // Limit to SPPG if admin SPPG is accessing
        if ($sppgId) {
            $query->where('sppg_id', $sppgId);
        }

        if (!empty($filters['sppg_id'])) {
            $query->where('sppg_id', $filters['sppg_id']);
        }
        if (!empty($filters['school_level'])) {
            $query->where('school_level', $filters['school_level']);
        }
        if (!empty($filters['city'])) {
            $query->where('city', 'like', "%{$filters['city']}%");
        }
        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }
        if (isset($filters['without_sppg']) && $filters['without_sppg']) {
            $query->whereNull('sppg_id');
        }

        return $query->orderBy('name')->paginate($perPage);
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