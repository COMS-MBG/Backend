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
use Illuminate\Support\Facades\Log;

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

    // ─── POST /api/super-admin/map/route-check ──────────────────────────────
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
    /**
     * FLOW LENGKAP:
     * 1. Reverse geocode → update alamat SPPG
     * 2. Validasi semua mitra (existing + baru)
     * 3. Tandai mitra out of range
     * 4. Tambahkan rekomendasi (merge, skip duplikat)
     * 5. Simpan semuanya ke draft
     */
    public function confirmPoint(Request $request, string $submissionId, MapService $mapService): JsonResponse
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'capacity'  => 'nullable|integer|min:1',
        ]);

        $draft        = SppgDraft::with('partners')->findOrFail($submissionId);
        $confirmedLat = (float) $request->latitude;
        $confirmedLng = (float) $request->longitude;
        $capacity     = (int) ($request->capacity ?? 3000);

        // ── 1. Validasi status titik ─────────────────────────────────────────
        $validation = $mapService->validatePoint($confirmedLat, $confirmedLng, $draft->partners->toArray());

        // ── 2. Reverse geocoding → dapat alamat lengkap dari koordinat ───────
        $addressData = $this->reverseGeocode($confirmedLat, $confirmedLng);

        // ── 3. Update form1_data: lat/lng + alamat otomatis ──────────────────
        $form1             = $draft->form1_data ?? [];
        $form1['latitude'] = $confirmedLat;
        $form1['longitude']= $confirmedLng;
        
        // Update alamat jika reverse geocode berhasil
        if (!empty($addressData['address']))  $form1['address']  = $addressData['address'];
        if (!empty($addressData['district'])) $form1['district'] = $addressData['district'];
        if (!empty($addressData['city']))     $form1['city']     = $addressData['city'];
        if (!empty($addressData['province'])) $form1['province'] = $addressData['province'];

        // ── 4. Simpan koordinat + status + form1 ke draft ───────────────────
        $draft->update([
            'confirmed_latitude'  => $confirmedLat,
            'confirmed_longitude' => $confirmedLng,
            'point_status'        => $validation['status'],
            'map_confirmed'       => true,
            'form1_data'          => $form1,
        ]);

        // ── 5. Validasi & tandai mitra pengajuan yang out of range ─────────
        $outOfRange = [];
        foreach ($draft->partners as $partner) {
            if (empty($partner->latitude) || empty($partner->longitude)) continue;

            $route  = $mapService->getRouteDurationAndDistance(
                $confirmedLat, $confirmedLng,
                $partner->latitude, $partner->longitude
            );
            $distKm = $route
                ? $route['distance_km']
                : $mapService->calculateHaversineDistance(
                    $confirmedLat, $confirmedLng,
                    $partner->latitude, $partner->longitude
                  );
            $durMin = $route ? $route['duration_minutes'] : 999.0;

            $isOut = $distKm > 5.0 || $durMin > 30.0;
            $partner->update([
                'data_source' => $isOut ? 'out_of_range' : (
                    $partner->data_source === 'out_of_range' ? 'manual' : $partner->data_source
                ),
            ]);

            if ($isOut) {
                $outOfRange[] = [
                    'id'           => $partner->id,
                    'school_name'  => $partner->school_name,
                    'distance_km'  => round($distKm, 2),
                    'duration_min' => round($durMin, 1),
                ];
            }
        }

        // ── 6. Tambahkan mitra rekomendasi (merge, skip duplikat) ──────────────
        $recommendedPartners = $mapService->recommendPartnersForPoint($confirmedLat, $confirmedLng, $capacity);
        $draft->refresh()->load('partners');

        $existingNpsns  = $draft->partners->pluck('npsn')->filter()->toArray();
        $existingCoords = $draft->partners
            ->map(fn($p) => ['lat' => (float) $p->latitude, 'lng' => (float) $p->longitude])
            ->toArray();

        $addedPartners = [];
        foreach ($recommendedPartners as $rp) {
            // Skip jika NPSN duplikat
            if (!empty($rp['npsn']) && in_array($rp['npsn'], $existingNpsns)) continue;

            // Skip jika koordinat duplikat (< 50m)
            $isDuplicate = false;
            foreach ($existingCoords as $ec) {
                if ($mapService->calculateHaversineDistance(
                    $rp['latitude'], $rp['longitude'], $ec['lat'], $ec['lng']
                ) < 0.05) {
                    $isDuplicate = true;
                    break;
                }
            }
            if ($isDuplicate) continue;

            // Tambahkan mitra rekomendasi
            SppgDraftPartner::create([
                'draft_id'      => $draft->id,
                'school_name'   => $rp['school_name'],
                'npsn'          => $rp['npsn']          ?? null,
                'level'         => $rp['level']         ?? 'SMA',
                'school_status' => $rp['school_status'] ?? 'negeri',
                'address'       => $rp['address']       ?? trim(($rp['district'] ?? '') . ', ' . ($rp['city'] ?? ''), ', '),
                'city'          => $rp['city']          ?? '',
                'district'      => $rp['district']      ?? '',
                'latitude'      => $rp['latitude'],
                'longitude'     => $rp['longitude'],
                'jumlah_porsi'  => $rp['portion_count'] ?? 0,
                'data_source'   => 'system_recommendation',
            ]);

            $addedPartners[] = $rp['school_name'];
        }

        return response()->json([
            'success'                => true,
            'message'                => 'Titik dikonfirmasi. Alamat & rekomendasi mitra sudah diupdate.',
            'point_status'           => $validation['status'],
            'conflicts'              => $validation['conflicts'] ?? [],
            'address_updated'        => !empty($addressData),
            'address_data'           => $addressData,
            'partners_added'         => count($addedPartners),
            'partners_added_names'   => $addedPartners,
            'partners_out_of_range'  => $outOfRange,
            'out_of_range_warning'   => count($outOfRange) > 0
                ? count($outOfRange) . ' mitra dari pengajuan berada di luar radius 5km/30menit. Silakan tinjau kembali.'
                : null,
            'data' => $draft->fresh('partners'),
        ]);
    }

    // ─── Private helpers ────────────────────────────────────────────────────────

    private function resolvePartners(Request $request): array
    {
        if ($request->has('partners')) return $request->input('partners');
        if ($request->has('draft_id')) {
            $draft = SppgDraft::with('partners')->find($request->input('draft_id'));
            return $draft ? $draft->partners->toArray() : [];
        }
        return [];
    }

    private function reverseGeocode(float $lat, float $lng): array
    {
        $url = "https://nominatim.openstreetmap.org/reverse?lat={$lat}&lon={$lng}&format=json&addressdetails=1&zoom=18";

        try {
            $res = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'COMS-MBG-SuperAdmin/1.0', 'Accept' => 'application/json'])
                ->get($url);

            if ($res->successful()) {
                $data = $res->json();
                $addr = $data['address'] ?? [];

                $district = $addr['suburb']
                    ?? $addr['village']
                    ?? $addr['town']
                    ?? $addr['city_district']
                    ?? null;

                $city = $addr['city']
                    ?? $addr['regency']
                    ?? $addr['county']
                    ?? $addr['state_district']
                    ?? null;

                $addressParts = array_filter([
                    $addr['road']   ?? null,
                    $addr['house_number'] ?? null,
                    $addr['suburb'] ?? null,
                    $city,
                ]);

                return [
                    'address'  => implode(', ', $addressParts) ?: ($data['display_name'] ?? null),
                    'district' => $district,
                    'city'     => $city,
                    'province' => $addr['state'] ?? null,
                    'raw'      => $data['display_name'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Reverse geocode error: ' . $e->getMessage());
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
            ->where('status', 'draft')
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