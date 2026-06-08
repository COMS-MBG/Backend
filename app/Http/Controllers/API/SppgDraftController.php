<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SppgDraft;
use App\Models\SppgDraftPartner;
use App\Services\AddressValidationService;
use App\Services\SuperAdmin\MapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SppgDraftController extends Controller
{
    // ─── POST /api/sppg-drafts ──────────────────────────────────────────
    /**
     * Buat draft & simpan form1 (alamat SPPG)
     */
    public function storeForm1(
        Request $request,
        AddressValidationService $addressService
    ): JsonResponse {
        $request->validate([
            'name'     => 'required|string|min:3|max:100',
            'address'  => 'required|string|min:10|max:255',
            'district' => 'required|string|min:3|max:100',
            'city'     => 'required|string|min:3|max:100',
            'province' => 'required|string|min:3|max:100',
            'capacity' => 'required|integer|min:1|max:9999',
        ], [
            'address.min' => 'Alamat minimal 10 karakter. Contoh: Jl. Raya Cibiru No. 45',
        ]);

        // ── 1. Format untuk geocoding ────────────────────────────────────
        $fullAddress = $addressService->formatForGeocoding($request->only([
            'address', 'district', 'city', 'province'
        ]));

        // ── 2. Validasi alamat via Nominatim ─────────────────────────────
        $validation = $addressService->validateAndSuggest($fullAddress, $request->city);

        if (!$validation['valid']) {
            return response()->json([
                'success'    => false,
                'message'    => 'Alamat tidak valid: ' . $validation['message'],
                'confidence' => $validation['confidence'] ?? 0,
                'suggestion' => 'Format: Jl. Nama No. X, Kecamatan, Kota, Provinsi',
            ], 422);
        }

        // ── 3. Buat draft baru ───────────────────────────────────────────
        $draft = SppgDraft::create([
            'submission_number' => 'DRAFT-' . date('YmdHis'),
            'submitted_by'      => auth()->id(),
            'source'            => 'internal',
            'form1_data'        => array_merge(
                $request->only(['name', 'address', 'district', 'city', 'province', 'capacity']),
                [
                    'latitude'  => $validation['lat'],
                    'longitude' => $validation['lng'],
                ]
            ),
            'latitude'          => $validation['lat'],
            'longitude'         => $validation['lng'],
            'point_status'      => 'green',
            'map_confirmed'     => false,
            'status'            => 'draft',
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Form SPPG berhasil dibuat. Selanjutnya: tambah minimal 1 sekolah mitra.',
            'data'      => $draft,
            'next_step' => "POST /api/sppg-drafts/{$draft->id}/partners (untuk tambah mitra)",
            'validation' => [
                'confidence'    => $validation['confidence'],
                'display_name'  => $validation['display_name'],
                'lat'           => $validation['lat'],
                'lng'           => $validation['lng'],
            ],
        ]);
    }

    // ─── POST /api/sppg-drafts/{draftId}/partners ────────────────────────
    /**
     * Tambah mitra ke draft
     */
    public function addPartner(
        Request $request,
        string $draftId,
        AddressValidationService $addressService
    ): JsonResponse {
        $draft = SppgDraft::findOrFail($draftId);

        $request->validate([
            'school_name'  => 'required|string|min:3|max:150',
            'npsn'         => 'nullable|string|max:20|unique:sppg_draft_partners,npsn',
            'level'        => 'nullable|in:SD,SMP,SMA,SMK',
            'school_status'=> 'nullable|in:negeri,swasta',
            'address'      => 'required|string|min:10|max:255',
            'district'     => 'required|string|min:3|max:100',
            'city'         => 'required|string|min:3|max:100',
            'jumlah_porsi' => 'nullable|integer|min:0',
        ], [
            'address.min'   => 'Alamat minimal 10 karakter',
            'npsn.unique'   => 'NPSN sudah terdaftar di draft ini',
        ]);

        // ── 1. Format untuk geocoding ────────────────────────────────────
        $fullAddress = $addressService->formatForGeocoding($request->only([
            'address', 'district', 'city'
        ]));

        // ── 2. Validasi alamat ───────────────────────────────────────────
        $validation = $addressService->validateAndSuggest($fullAddress, $request->city);

        if (!$validation['valid']) {
            return response()->json([
                'success'       => false,
                'message'       => 'Alamat sekolah tidak valid: ' . $validation['message'],
                'alternatives'  => $validation['alternatives'] ?? [],
                'suggestion'    => 'Contoh: SMA Negeri 1 Cileunyi, Jl. Pendidikan No.1, Cileunyi, Kab. Bandung',
            ], 422);
        }

        // ── 3. Cek duplikat NPSN ─────────────────────────────────────────
        if (!empty($request->npsn)) {
            $existingNpsn = $draft->partners()
                ->where('npsn', $request->npsn)
                ->exists();

            if ($existingNpsn) {
                return response()->json([
                    'success' => false,
                    'message' => 'NPSN ini sudah ada di draft.',
                ], 422);
            }
        }

        // ── 4. Cek duplikat koordinat (< 50m) ────────────────────────────
        $mapService = app(MapService::class);
        foreach ($draft->partners as $existing) {
            if (empty($existing->latitude) || empty($existing->longitude)) continue;

            $distance = $mapService->calculateHaversineDistance(
                $validation['lat'], $validation['lng'],
                $existing->latitude, $existing->longitude
            );

            if ($distance < 0.05) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sekolah ini sudah ada di draft (koordinat serupa dengan: ' . $existing->school_name . ')',
                ], 422);
            }
        }

        // ── 5. Tambahkan partner ─────────────────────────────────────────
        $partner = SppgDraftPartner::create([
            'draft_id'      => $draftId,
            'school_name'   => $request->school_name,
            'npsn'          => $request->npsn,
            'level'         => $request->level ?? 'SMA',
            'school_status' => $request->school_status ?? 'negeri',
            'address'       => $request->address,
            'district'      => $request->district,
            'city'          => $request->city,
            'latitude'      => $validation['lat'],
            'longitude'     => $validation['lng'],
            'jumlah_porsi'  => $request->jumlah_porsi ?? 0,
            'data_source'   => 'manual',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mitra berhasil ditambahkan & tergeocoding.',
            'data'    => $partner,
            'validation' => [
                'confidence'   => $validation['confidence'],
                'display_name' => $validation['display_name'],
            ],
        ]);
    }

    // ─── PUT /api/sppg-drafts/{draftId}/partners/{partnerId} ──────────────
    /**
     * Update partner yang sudah ada
     */
    public function updatePartner(
        Request $request,
        string $draftId,
        string $partnerId,
        AddressValidationService $addressService
    ): JsonResponse {
        $draft   = SppgDraft::findOrFail($draftId);
        $partner = SppgDraftPartner::where('draft_id', $draftId)
            ->where('id', $partnerId)
            ->firstOrFail();

        $request->validate([
            'school_name'   => 'sometimes|string|min:3|max:150',
            'address'       => 'sometimes|string|min:10|max:255',
            'district'      => 'sometimes|string|min:3|max:100',
            'city'          => 'sometimes|string|min:3|max:100',
            'jumlah_porsi'  => 'sometimes|integer|min:0',
        ]);

        // Jika alamat diubah, geocode ulang
        if ($request->has('address') || $request->has('city')) {
            $fullAddress = $addressService->formatForGeocoding([
                'address'  => $request->address ?? $partner->address,
                'district' => $request->district ?? $partner->district,
                'city'     => $request->city ?? $partner->city,
            ]);

            $validation = $addressService->validateAndSuggest($fullAddress, $request->city ?? $partner->city);

            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alamat tidak valid: ' . $validation['message'],
                ], 422);
            }

            $partner->update(array_merge(
                $request->only(['school_name', 'address', 'district', 'city', 'jumlah_porsi']),
                [
                    'latitude'  => $validation['lat'],
                    'longitude' => $validation['lng'],
                ]
            ));
        } else {
            $partner->update($request->only(['school_name', 'jumlah_porsi']));
        }

        return response()->json([
            'success' => true,
            'message' => 'Mitra berhasil diupdate.',
            'data'    => $partner,
        ]);
    }

    // ─── DELETE /api/sppg-drafts/{draftId}/partners/{partnerId} ──────────
    public function deletePartner(string $draftId, string $partnerId): JsonResponse
    {
        $partner = SppgDraftPartner::where('draft_id', $draftId)
            ->where('id', $partnerId)
            ->firstOrFail();

        $schoolName = $partner->school_name;
        $partner->delete();

        return response()->json([
            'success' => true,
            'message' => "Mitra '{$schoolName}' berhasil dihapus.",
        ]);
    }

    // ─── GET /api/sppg-drafts/{draftId} ──────────────────────────────────
    public function show(string $draftId): JsonResponse
    {
        $draft = SppgDraft::with('partners')->findOrFail($draftId);

        $partnerCount = $draft->partners->count();

        return response()->json([
            'success' => true,
            'data'    => $draft,
            'summary' => [
                'form1_complete'     => !empty($draft->form1_data),
                'partners_count'     => $partnerCount,
                'map_confirmed'      => $draft->map_confirmed,
                'ready_to_submit'    => $draft->map_confirmed && $partnerCount > 0,
                'message'            => !$draft->map_confirmed 
                    ? 'Menunggu SuperAdmin konfirmasi titik & rekomendasi mitra di Map'
                    : 'Siap di-submit',
            ],
        ]);
    }
}