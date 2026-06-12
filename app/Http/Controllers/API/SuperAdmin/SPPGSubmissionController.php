<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SppgDraft;
use App\Models\SppgDraftPartner;
use App\Services\SPPG\SppgRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    public function submit(Request $request, string $id,SppgRegistrationService $registrationService) : JsonResponse {
        $draft = SppgDraft::with('partners')->findOrFail($id);

        // ── Guard 1: Status check ────────────────────────────────────────────
        if ($draft->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Draft ini sudah didaftarkan.',
            ], 422);
        }

        // ── Guard 2: Form data lengkap ────────────────────────────────────────
        if (empty($draft->form1_data) || empty($draft->form2_data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data SPPG (Form 1) dan Admin SPPG (Form 2) harus diisi terlebih dahulu.',
            ], 422);
        }

        // ── Guard 3: Ada minimal 1 mitra ──────────────────────────────────────
        if ($draft->partners->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Draft harus memiliki minimal 1 sekolah mitra. Jika belum ada rekomendasi, minta SuperAdmin confirm titik di Map terlebih dahulu.',
                'hint'    => 'Setiap rekomendasi akan ditambahkan saat SuperAdmin confirm titik SPPG.',
            ], 422);
        }

        // ── Guard 4: Map sudah dikonfirmasi ───────────────────────────────────
        if (!$draft->map_confirmed) {
            return response()->json([
                'success' => false,
                'message' => 'Titik koordinat SPPG belum dikonfirmasi di Map Rekomendasi. SuperAdmin harus confirm titik terlebih dahulu untuk mendapat rekomendasi mitra.',
            ], 422);
        }

        // ── Guard 5: Cek duplikasi email admin/karyawan di tabel users ────────
        $emailsToCheck = [];
        if (!empty($draft->form2_data['email'])) {
            $emailsToCheck['Admin SPPG'] = trim($draft->form2_data['email']);
        }
        
        $nutri = $draft->form3_data['nutritionist'] ?? $draft->form3_data['ahli_gizi'] ?? null;
        if (!empty($nutri['email'])) {
            $emailsToCheck['Ahli Gizi'] = trim($nutri['email']);
        }
        
        $logis = $draft->form3_data['logistics_admin'] ?? $draft->form3_data['admin_logistik'] ?? null;
        if (!empty($logis['email'])) {
            $emailsToCheck['Admin Logistik'] = trim($logis['email']);
        }

        foreach ($emailsToCheck as $role => $email) {
            if (\App\Models\User::where('email', $email)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => "Email untuk {$role} ('{$email}') sudah terdaftar dalam sistem. Silakan gunakan email lain.",
                ], 422);
            }
        }

        // ── STEP 1: Auto-geocode mitra yang belum punya koordinat ────────────
        foreach ($draft->partners as $partner) {
            if (!empty($partner->latitude) && !empty($partner->longitude)) {
                continue; // Sudah punya koordinat
            }

            $address = implode(', ', array_filter([
                $partner->school_name,
                $partner->address,
                $partner->district,
                $partner->city,
            ]));

            $url = 'https://nominatim.openstreetmap.org/search?q='
                . urlencode($address)
                . '&format=json&limit=1';

            try {
                $res = Http::timeout(5)
                    ->withHeaders(['User-Agent' => 'COMS-MBG-SuperAdmin/1.0'])
                    ->get($url);

                if ($res->successful() && !empty($res->json()[0])) {
                    $geo = $res->json()[0];
                    $partner->update([
                        'latitude'  => (float) $geo['lat'],
                        'longitude' => (float) $geo['lon'],
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning("Geocode mitra gagal: {$partner->school_name} — " . $e->getMessage());
            }
        }

        // ── STEP 2: Cek apakah semua mitra sudah punya koordinat ─────────────
        $draft->refresh()->load('partners');
        $missingCoords = $draft->partners
            ->filter(fn($p) => empty($p->latitude) || empty($p->longitude))
            ->pluck('school_name')
            ->toArray();

        if (!empty($missingCoords)) {
            return response()->json([
                'success' => false,
                'message' => 'Koordinat tidak ditemukan untuk mitra berikut: ' . implode(', ', $missingCoords) . '. Pastikan alamat mitra sudah lengkap dan benar.',
                'missing_coords' => $missingCoords,
            ], 422);
        }

        // ── STEP 3: Daftar SPPG (dalam transaction) ────────────────────────
        $sppg = DB::transaction(function () use ($draft, $registrationService) {
            $registrationData = [
                'sppg'            => array_merge($draft->form1_data ?? [], [
                    'latitude'  => $draft->confirmed_latitude ?? $draft->latitude,
                    'longitude' => $draft->confirmed_longitude ?? $draft->longitude,
                ]),
                'admin_sppg'      => $draft->form2_data,
                'nutritionist'    => $draft->form3_data['nutritionist'] ?? $draft->form3_data['ahli_gizi'] ?? null,
                'logistics_admin' => $draft->form3_data['logistics_admin'] ?? $draft->form3_data['admin_logistik'] ?? null,
                'partners'        => $draft->partners->map(fn($p) => [
                    'school_name'      => $p->school_name,
                    'npsn'             => $p->npsn,
                    'school_type'      => $p->level,
                    'ownership_status' => $p->school_status,
                    'address'          => $p->address,
                    'city'             => $p->city,
                    'district'         => $p->district,
                    'latitude'         => $p->latitude,
                    'longitude'        => $p->longitude,
                    'portion_count'    => $p->jumlah_porsi,
                ])->toArray(),
            ];

            $sppgModel = $registrationService->register($registrationData);

            $draft->update([
                'status'       => 'registered',
                'submitted_at' => now(),
            ]);

            return $sppgModel;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran SPPG berhasil diselesaikan.',
            'data'    => $sppg,
        ]);
    }
}
