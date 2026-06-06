<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SPPG;
use App\Models\SppgDraft;
use App\Models\SppgDraftPartner;
use App\Services\SuperAdmin\MapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MapController extends Controller
{
    // ─── GET /api/super-admin/map/data ──────────────────────────────────────────
    // Satu-satunya endpoint yang perlu dipanggil FE untuk render peta lengkap.
    public function getMapData(Request $request, MapService $mapService): JsonResponse
    {
        return response()->json([
            'success'           => true,
            'sppg_layers'       => $this->buildSppgLayers(),
            'submission_layers' => $this->buildSubmissionLayers(),
            'recommendations'   => $mapService->getKMeansRecommendations(),
            'schools'           => $mapService->getSchoolsLayerData(),
        ]);
    }

    // ─── POST /api/super-admin/map/geocode ──────────────────────────────────────
    public function geocode(Request $request): JsonResponse
    {
        $query = $request->input('query') ?? $request->input('address');
        if (empty($query)) {
            return response()->json(['success' => false, 'message' => 'Query wajib diisi.'], 400);
        }

        $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($query) . '&format=json&limit=5';

        try {
            $res = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'COMS-MBG-SuperAdmin/1.0', 'Accept' => 'application/json'])
                ->get($url);

            if ($res->successful()) {
                return response()->json(['success' => true, 'data' => $res->json()]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal ke server geocoding: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => false, 'message' => 'Geocoding gagal.'], 500);
    }

    // ─── POST /api/super-admin/map/route-check ──────────────────────────────────
    public function routeCheck(Request $request, MapService $mapService): JsonResponse
    {
        $request->validate([
            'lat_a' => 'required|numeric',
            'lon_a' => 'required|numeric',
            'lat_b' => 'required|numeric',
            'lon_b' => 'required|numeric',
        ]);

        $route = $mapService->getRouteDurationAndDistance(
            $request->lat_a, $request->lon_a,
            $request->lat_b, $request->lon_b
        );

        return $route
            ? response()->json(['success' => true, 'data' => $route])
            : response()->json(['success' => false, 'message' => 'Gagal mendapat rute dari OSRM.'], 500);
    }

    // ─── POST /api/super-admin/map/validate-point ───────────────────────────────
    public function validatePoint(Request $request, MapService $mapService): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'draft_id'  => 'nullable|exists:sppg_drafts,id',
            'partners'  => 'nullable|array|max:100',
        ]);

        $partners = $this->resolvePartners($request);
        $result   = $mapService->validatePoint($request->latitude, $request->longitude, $partners);

        return response()->json(['success' => true, 'data' => $result]);
    }

    // ─── POST /api/super-admin/map/suggest-shift ────────────────────────────────
    public function suggestShift(Request $request, MapService $mapService): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'draft_id'  => 'nullable|exists:sppg_drafts,id',
            'partners'  => 'nullable|array|max:100',
        ]);

        $partners = $this->resolvePartners($request);
        $suggest  = $mapService->suggestCentroidShift($request->latitude, $request->longitude, $partners);

        return response()->json(['success' => true, 'data' => $suggest]);
    }

    // ─── POST /api/super-admin/map/confirm-point/{submission_id} ────────────────
    // Simpan koordinat yang dikonfirmasi + rekomendasi mitra ke draft.
    public function confirmPoint(Request $request, string $submissionId, MapService $mapService): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'capacity'  => 'nullable|integer|min:1',
        ]);

        $draft       = SppgDraft::with('partners')->findOrFail($submissionId);
        $confirmedLat = (float) $request->latitude;
        $confirmedLng = (float) $request->longitude;
        $capacity     = (int)   ($request->capacity ?? 3000);

        // Validasi status titik
        $validation = $mapService->validatePoint($confirmedLat, $confirmedLng, $draft->partners->toArray());

        // Simpan koordinat + status ke draft
        $draft->update([
            'confirmed_latitude'  => $confirmedLat,
            'confirmed_longitude' => $confirmedLng,
            'point_status'        => $validation['status'],
            'map_confirmed'       => true,
        ]);

        // Update lat/lng di form1_data juga agar pengajuan membawa koordinat terkonfirmasi
        $form1 = $draft->form1_data ?? [];
        $form1['latitude']  = $confirmedLat;
        $form1['longitude'] = $confirmedLng;
        $draft->update(['form1_data' => $form1]);

        // Rekomendasi mitra dari sistem berdasarkan titik terkonfirmasi
        $recommendedPartners = $mapService->recommendPartnersForPoint($confirmedLat, $confirmedLng, $capacity);

        // Tandai mitra rekomendasi yg belum ada di draft, tambahkan sebagai draft partner
        $existingNpsns = $draft->partners->pluck('npsn')->filter()->toArray();

        foreach ($recommendedPartners as $rp) {
    if (!empty($rp['npsn']) && in_array($rp['npsn'], $existingNpsns)) continue;

    SppgDraftPartner::create([
        'draft_id'     => $draft->id,
        'school_name'  => $rp['school_name'],
        'npsn'         => $rp['npsn']     ?? null,
        'level'        => $rp['level']    ?? 'SMA',      // fallback, Admin SPPG lengkapi nanti
        'school_status'=> $rp['school_status'] ?? 'negeri', // fallback
        'address'      => $rp['address']  ?? $rp['district'] . ', ' . $rp['city'],
        'city'         => $rp['city']     ?? '',
        'district'     => $rp['district'] ?? '',
        'latitude'     => $rp['latitude'],
        'longitude'    => $rp['longitude'],
        'jumlah_porsi' => $rp['portion_count'] ?? 0,
        'data_source'  => 'database',  // bukan system_recommendation
    ]);
}

        return response()->json([
            'success'     => true,
            'message'     => 'Titik dikonfirmasi. Rekomendasi mitra telah ditambahkan ke draft.',
            'point_status'=> $validation['status'],
            'conflicts'   => $validation['conflicts'],
            'data'        => $draft->fresh('partners'),
        ]);
    }

    // ─── Private helpers ────────────────────────────────────────────────────────

    private function resolvePartners(Request $request): array
    {
        if ($request->has('partners')) {
            return $request->input('partners');
        }
        if ($request->has('draft_id')) {
            $draft = SppgDraft::with('partners')->find($request->input('draft_id'));
            return $draft ? $draft->partners->toArray() : [];
        }
        return [];
    }

    private function buildSppgLayers(): array
    {
        return SPPG::where('status', 'active')
            ->with(['partners' => fn($q) => $q->select('id', 'sppg_id', 'school_name', 'latitude', 'longitude', 'portion_count')])
            ->get()
            ->map(fn($s) => [
                'id'        => $s->id,
                'name'      => $s->name,
                'latitude'  => $s->latitude,
                'longitude' => $s->longitude,
                'status'    => $s->status,
                'capacity'  => $s->capacity,
                'partners'  => $s->partners->map(fn($p) => [
                    'id'            => $p->id,
                    'school_name'   => $p->school_name,
                    'latitude'      => $p->latitude,
                    'longitude'     => $p->longitude,
                    'portion_count' => $p->portion_count,
                ])->values()->all(),
            ])
            ->values()->all();
    }

    private function buildSubmissionLayers(): array
    {
        return SppgDraft::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['partners' => fn($q) => $q->select('id', 'draft_id', 'school_name', 'latitude', 'longitude', 'jumlah_porsi', 'data_source')])
            ->get()
            ->map(fn($d) => [
                'id'                  => $d->id,
                'submission_number'   => $d->submission_number,
                'latitude'            => $d->latitude,
                'longitude'           => $d->longitude,
                'confirmed_latitude'  => $d->confirmed_latitude,
                'confirmed_longitude' => $d->confirmed_longitude,
                'point_status'        => $d->point_status,
                'map_confirmed'       => $d->map_confirmed,
                'status'              => $d->status,
                'partners'            => $d->partners->map(fn($p) => [
                    'id'           => $p->id,
                    'school_name'  => $p->school_name,
                    'latitude'     => $p->latitude,
                    'longitude'    => $p->longitude,
                    'jumlah_porsi' => $p->jumlah_porsi,
                    'data_source'  => $p->data_source,
                ])->values()->all(),
            ])
            ->values()->all();
    }
}