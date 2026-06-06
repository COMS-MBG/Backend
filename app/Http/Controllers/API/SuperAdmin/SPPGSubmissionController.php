<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SppgDraft;
use App\Models\SppgDraftPartner;
use App\Services\SPPG\SppgRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SppgSubmissionController extends Controller
{
    /**
     * GET /api/super-admin/sppg-submissions
     * List all drafts.
     */
    public function index(Request $request): JsonResponse
    {
        $drafts = SppgDraft::with('partners')->orderBy('updated_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $drafts,
        ]);
    }

    /**
     * POST /api/super-admin/sppg-submissions
     * Auto-save draft.
     */
    public function store(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Find existing draft for the current user that is still in 'draft' status
        $draft = SppgDraft::where('submitted_by', $userId)
            ->where('status', 'draft')
            ->first();

        $data = $request->only([
            'form1_data', 'form2_data', 'form3_data',
            'latitude', 'longitude', 'confirmed_latitude', 'confirmed_longitude',
            'point_status', 'map_confirmed'
        ]);

        if ($draft) {
            $draft->update($data);
        } else {
            // Generate submission number: DRAFT-YYYYMMDD-XXX
            $dateStr = now()->format('Ymd');
            $prefix = "DRAFT-{$dateStr}-";
            
            $lastDraft = SppgDraft::where('submission_number', 'like', "{$prefix}%")
                ->orderBy('submission_number', 'desc')
                ->first();

            $seq = 1;
            if ($lastDraft && preg_match('/-(\d+)$/', $lastDraft->submission_number, $matches)) {
                $seq = (int) $matches[1] + 1;
            }
            $submissionNumber = $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);

            $draft = SppgDraft::create(array_merge($data, [
                'submission_number' => $submissionNumber,
                'submitted_by' => $userId,
                'status' => 'draft',
                'source' => 'internal',
            ]));
        }

        // Update partners list if provided
        if ($request->has('partners')) {
            $draft->partners()->delete();
            foreach ($request->input('partners') as $p) {
                $draft->partners()->create([
                    'school_name' => $p['school_name'],
                    'npsn' => $p['npsn'] ?? null,
                    'level' => $p['level'],
                    'school_status' => $p['school_status'],
                    'address' => $p['address'],
                    'city' => $p['city'],
                    'district' => $p['district'],
                    'latitude' => $p['latitude'],
                    'longitude' => $p['longitude'],
                    'jumlah_porsi' => $p['jumlah_porsi'],
                    'data_source' => $p['data_source'] ?? 'database',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Draft berhasil disimpan.',
            'data' => $draft->load('partners'),
        ]);
    }

    /**
     * GET /api/super-admin/sppg-submissions/{id}
     * Get specific draft detail.
     */
    public function show(string $id): JsonResponse
    {
        $draft = SppgDraft::with('partners')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $draft,
        ]);
    }

    /**
     * PUT /api/super-admin/sppg-submissions/{id}
     * Update draft.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $draft = SppgDraft::findOrFail($id);
        
        $data = $request->only([
            'form1_data', 'form2_data', 'form3_data',
            'latitude', 'longitude', 'confirmed_latitude', 'confirmed_longitude',
            'point_status', 'map_confirmed'
        ]);

        $draft->update($data);

        // Update partners list if provided
        if ($request->has('partners')) {
            $draft->partners()->delete();
            foreach ($request->input('partners') as $p) {
                $draft->partners()->create([
                    'school_name' => $p['school_name'],
                    'npsn' => $p['npsn'] ?? null,
                    'level' => $p['level'],
                    'school_status' => $p['school_status'],
                    'address' => $p['address'],
                    'city' => $p['city'],
                    'district' => $p['district'],
                    'latitude' => $p['latitude'],
                    'longitude' => $p['longitude'],
                    'jumlah_porsi' => $p['jumlah_porsi'],
                    'data_source' => $p['data_source'] ?? 'database',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Draft berhasil diperbarui.',
            'data' => $draft->load('partners'),
        ]);
    }

    /**
     * DELETE /api/super-admin/sppg-submissions/{id}
     * Delete draft.
     */
    public function destroy(string $id): JsonResponse
    {
        $draft = SppgDraft::findOrFail($id);
        $draft->delete();

        return response()->json([
            'success' => true,
            'message' => 'Draft berhasil dihapus.',
        ]);
    }

    /**
     * POST /api/super-admin/sppg-submissions/{id}/submit
     * Submit draft and convert to registered SPPG.
     */
    public function submit(Request $request, string $id, SppgRegistrationService $registrationService): JsonResponse
    {
        $draft = SppgDraft::with('partners')->findOrFail($id);

        if ($draft->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Draft ini sudah didaftarkan.',
            ], 422);
        }

        if (empty($draft->form1_data) || empty($draft->form2_data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data SPPG (Form 1) dan Admin SPPG (Form 2) harus diisi terlebih dahulu.',
            ], 422);
        }

        if ($draft->partners->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Draft harus memiliki minimal 1 sekolah mitra (Form 2).',
            ], 422);
        }

        $sppg = DB::transaction(function () use ($draft, $registrationService) {
            $registrationData = [
                'sppg'            => $draft->form1_data,
                'admin_sppg'      => $draft->form2_data,
                'nutritionist'    => $draft->form3_data['nutritionist'] ?? $draft->form3_data['ahli_gizi'] ?? null,
                'logistics_admin' => $draft->form3_data['logistics_admin'] ?? $draft->form3_data['admin_logistik'] ?? null,
                'partners' => $draft->partners->map(function ($p) {
                    return [
                        'school_name' => $p->school_name,
                        'npsn' => $p->npsn,
                        'school_type' => $p->level,
                        'ownership_status' => $p->school_status,
                        'address' => $p->address,
                        'city' => $p->city,
                        'district' => $p->district,
                        'latitude' => $p->latitude,
                        'longitude' => $p->longitude,
                        'portion_count' => $p->jumlah_porsi,
                    ];
                })->toArray()
            ];

            $sppgModel = $registrationService->register($registrationData);

            $draft->update([
                'status' => 'registered',
                'submitted_at' => now(),
            ]);

            return $sppgModel;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran SPPG berhasil diselesaikan.',
            'data' => $sppg,
        ]);
    }
}
