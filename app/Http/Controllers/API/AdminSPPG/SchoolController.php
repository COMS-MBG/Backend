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
 * CRUD sekolah untuk panel admin SPPG.
 * Data sekolah dipakai admin logistik saat membuat jadwal pengiriman.
 *
 * CATATAN: School model memakai kolom bahasa Indonesia:
 *   nama, alamat, jumlah_siswa, jenjang, kecamatan, kota, provinsi,
 *   telepon, kepala_sekolah, latitude, longitude, status
 *
 * Base URL: /api/admin-sppg/schools
 */
class SchoolController extends Controller
{
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
        $query = School::query()->latest();

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                  ->orWhere('kecamatan', 'like', "%{$keyword}%")
                  ->orWhere('kota', 'like', "%{$keyword}%")
                  ->orWhere('alamat', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('jenjang')) {
            $query->where('jenjang', $request->jenjang);
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
    public function show(School $school): JsonResponse
    {
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
        $validated = $request->validate([
            'nama'           => ['required', 'string', 'max:255'],
            'alamat'         => ['nullable', 'string', 'max:1000'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'jumlah_siswa'   => ['nullable', 'integer', 'min:0'],
            'jenjang'        => ['nullable', 'string', 'in:SD,SMP,SMA,SMK'],
            'kecamatan'      => ['nullable', 'string', 'max:100'],
            'kota'           => ['nullable', 'string', 'max:100'],
            'provinsi'       => ['nullable', 'string', 'max:100'],
            'telepon'        => ['nullable', 'string', 'max:20'],
            'kepala_sekolah' => ['nullable', 'string', 'max:255'],
            'sppg_id'        => ['nullable', 'integer', 'exists:s_p_p_g_s,id'],
            'status'         => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        // Gunakan sppg_id dari user yang login jika tidak disediakan
        if (!isset($validated['sppg_id'])) {
            $validated['sppg_id'] = $request->user()->sppg_id
                ?? $request->user()->employee?->sppg_id
                ?? null;
        }

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
        $validated = $request->validate([
            'nama'           => ['sometimes', 'string', 'max:255'],
            'alamat'         => ['nullable', 'string', 'max:1000'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'jumlah_siswa'   => ['nullable', 'integer', 'min:0'],
            'jenjang'        => ['nullable', 'string', 'in:SD,SMP,SMA,SMK'],
            'kecamatan'      => ['nullable', 'string', 'max:100'],
            'kota'           => ['nullable', 'string', 'max:100'],
            'provinsi'       => ['nullable', 'string', 'max:100'],
            'telepon'        => ['nullable', 'string', 'max:20'],
            'kepala_sekolah' => ['nullable', 'string', 'max:255'],
            'status'         => ['nullable', 'string', 'in:active,inactive'],
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
    public function destroy(School $school): JsonResponse
    {
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
