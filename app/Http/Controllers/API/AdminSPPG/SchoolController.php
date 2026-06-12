<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Http\Resources\SchoolResource;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SchoolController – AdminSPPG
 *
 * CRUD schools for the Admin SPPG panel.
 * School data is used by logistics admin when creating delivery schedules.
 *
 * Base URL: /api/admin-sppg/schools
 */
class SchoolController extends Controller
{
    private function getSppgId(Request $request): int
    {
        $sppgId = $request->user()->sppg_id ?? $request->user()->employee?->sppg_id;
        abort_if(!$sppgId, 403, 'Anda tidak terhubung dengan SPPG manapun.');
        return (int) $sppgId;
    }

    private function validateOwnership(Request $request, School $school): void
    {
        abort_if((int) $school->sppg_id !== $this->getSppgId($request), 403, 'Anda tidak memiliki akses ke sekolah ini.');
    }

    /**
     * [GET] Daftar semua sekolah (paginated + filterable).
     * Endpoint: GET /api/admin-sppg/schools
     *
     * Query params:
     *   search    : string  (cari by nama/kecamatan)
     *   jenjang   : string  (SD/SMP/SMA/SMK)
     *   status    : string  (active/inactive)
     *   per_page  : int     (default 15)
     */
    public function index(Request $request): JsonResponse
    {
        $sppgId = $this->getSppgId($request);
        $query = School::query()->where('sppg_id', $sppgId)->latest();

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name',     'like', "%{$keyword}%")
                  ->orWhere('district', 'like', "%{$keyword}%")
                  ->orWhere('city',     'like', "%{$keyword}%")
                  ->orWhere('address',  'like', "%{$keyword}%");
            });
        }

        if ($request->filled('school_level')) {
            $query->where('school_level', $request->school_level);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter hanya sekolah yg punya koordinat GPS (untuk peta distribusi)
        if ($request->boolean('has_coordinates')) {
            $query->whereNotNull('latitude')->whereNotNull('longitude');
        }

        $schools = $query->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => SchoolResource::collection($schools),
            'meta'    => [
                'current_page' => $schools->currentPage(),
                'last_page'    => $schools->lastPage(),
                'total'        => $schools->total(),
            ],
        ]);
    }

    /**
     * [GET] Detail satu sekolah.
     * Endpoint: GET /api/admin-sppg/schools/{school}
     */
    public function show(Request $request, School $school): JsonResponse
    {
        $this->validateOwnership($request, $school);

        return response()->json([
            'success' => true,
            'data'    => new SchoolResource($school),
        ]);
    }

    /**
     * [POST] Tambah sekolah baru.
     * Endpoint: POST /api/admin-sppg/schools
     */
    public function store(Request $request): JsonResponse
    {
        $sppgId = $this->getSppgId($request);

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'address'       => ['nullable', 'string', 'max:1000'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
            'student_count' => ['nullable', 'integer', 'min:0'],
            'school_level'  => ['nullable', 'string', 'in:SD,SMP,SMA,SMK'],
            'district'      => ['nullable', 'string', 'max:100'],
            'city'          => ['nullable', 'string', 'max:100'],
            'province'      => ['nullable', 'string', 'max:100'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'principal'     => ['nullable', 'string', 'max:255'],
            'status'        => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $validated['status']  = $validated['status'] ?? 'active';
        $validated['sppg_id'] = $sppgId;

        $school = School::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'School created successfully.',
            'data'    => new SchoolResource($school),
        ], 201);
    }

    /**
     * [PUT/PATCH] Update data sekolah.
     * Endpoint: PUT /api/admin-sppg/schools/{school}
     */
    public function update(Request $request, School $school): JsonResponse
    {
        $this->validateOwnership($request, $school);

        $validated = $request->validate([
            'name'          => ['sometimes', 'string', 'max:255'],
            'address'       => ['nullable', 'string', 'max:1000'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
            'student_count' => ['nullable', 'integer', 'min:0'],
            'school_level'  => ['nullable', 'string', 'in:SD,SMP,SMA,SMK'],
            'district'      => ['nullable', 'string', 'max:100'],
            'city'          => ['nullable', 'string', 'max:100'],
            'province'      => ['nullable', 'string', 'max:100'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'principal'     => ['nullable', 'string', 'max:255'],
            'status'        => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $school->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'School updated successfully.',
            'data'    => new SchoolResource($school->fresh()),
        ]);
    }

    /**
     * [DELETE] Hapus sekolah (soft delete).
     * Endpoint: DELETE /api/admin-sppg/schools/{school}
     */
    public function destroy(Request $request, School $school): JsonResponse
    {
        $this->validateOwnership($request, $school);

        // Cek apakah sekolah masih punya jadwal aktif
        $activeSchedules = \App\Models\DeliverySchedule::where('school_id', $school->id)
            ->whereNotIn('status', ['confirmed', 'rejected'])
            ->count();

        if ($activeSchedules > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete school with {$activeSchedules} active delivery schedule(s).",
            ], 422);
        }

        $school->delete();

        return response()->json([
            'success' => true,
            'message' => 'School deleted successfully.',
        ]);
    }
}
