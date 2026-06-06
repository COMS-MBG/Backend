<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SPPG;
use App\Models\SppgDraft;
use App\Services\SuperAdmin\MapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MapController extends Controller
{
    /**
     * GET /api/super-admin/map/data
     * Get all map data (SPPG layers, draft layers, and recommendations).
     */
    public function getMapData(Request $request, MapService $mapService): JsonResponse
    {
        $sppgLayers = $this->getSppgLayersData();
        $submissionLayers = $this->getSubmissionLayersData();
        $recommendations = $mapService->getKMeansRecommendations();

        return response()->json([
            'success' => true,
            'sppg_layers' => $sppgLayers,
            'submission_layers' => $submissionLayers,
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * GET /api/super-admin/map/sppg-layers
     * Get active SPPG layers and their partners.
     */
    public function getSppgLayers(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getSppgLayersData(),
        ]);
    }

    /**
     * GET /api/super-admin/map/submission-layers
     * Get draft submissions that have coordinates.
     */
    public function getSubmissionLayers(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getSubmissionLayersData(),
        ]);
    }

    /**
     * GET /api/super-admin/map/recommendations
     * Get system recommendations for placing SPPGs based on K-Means clustering.
     */
    public function getRecommendations(Request $request, MapService $mapService): JsonResponse
    {
        $recommendations = $mapService->getKMeansRecommendations();
        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }

    /**
     * POST /api/super-admin/map/geocode
     * Proxy Nominatim Geocoding API with required User-Agent headers.
     */
    public function geocode(Request $request): JsonResponse
    {
        $query = $request->input('query') ?? $request->input('address');
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Query atau alamat pencarian wajib diisi.',
            ], 400);
        }

        $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($query) . "&format=json&limit=5";

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'COMS-MBG-SuperAdmin/1.0',
                    'Accept' => 'application/json',
                ])
                ->get($url);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung dengan server geocoding: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => false,
            'message' => 'Geocoding menggunakan Nominatim gagal.',
        ], 500);
    }

    /**
     * POST /api/super-admin/map/route-check
     * Proxy OSRM Routing API with required User-Agent headers.
     */
    public function routeCheck(Request $request, MapService $mapService): JsonResponse
    {
        $request->validate([
            'lat_a' => 'required|numeric',
            'lon_a' => 'required|numeric',
            'lat_b' => 'required|numeric',
            'lon_b' => 'required|numeric',
        ]);

        $latA = $request->input('lat_a');
        $lonA = $request->input('lon_a');
        $latB = $request->input('lat_b');
        $lonB = $request->input('lon_b');

        $route = $mapService->getRouteDurationAndDistance($latA, $lonA, $latB, $lonB);

        if ($route) {
            return response()->json([
                'success' => true,
                'data' => $route,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mendapatkan estimasi rute dari OSRM.',
        ], 500);
    }

    /**
     * POST /api/super-admin/map/validate-point
     * Validate coordinates suitability based on distance to other SPPGs and takeover rules.
     */
    public function validatePoint(Request $request, MapService $mapService): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'draft_id' => 'nullable|exists:sppg_drafts,id',
            'partners' => 'nullable|array|max:100',
        ]);

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        $partners = [];
        if ($request->has('partners')) {
            $partners = $request->input('partners');
        } elseif ($request->has('draft_id')) {
            $draft = SppgDraft::with('partners')->find($request->input('draft_id'));
            if ($draft) {
                $partners = $draft->partners->toArray();
            }
        }

        $validation = $mapService->validatePoint($lat, $lng, $partners);

        return response()->json([
            'success' => true,
            'data' => $validation,
        ]);
    }

    /**
     * POST /api/super-admin/map/suggest-shift
     * Suggest shifting centroid for draft partners within range.
     */
    public function suggestShift(Request $request, MapService $mapService): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'draft_id' => 'nullable|exists:sppg_drafts,id',
            'partners' => 'nullable|array|max:100',
        ]);

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        $partners = [];
        if ($request->has('partners')) {
            $partners = $request->input('partners');
        } elseif ($request->has('draft_id')) {
            $draft = SppgDraft::with('partners')->find($request->input('draft_id'));
            if ($draft) {
                $partners = $draft->partners->toArray();
            }
        }

        $suggest = $mapService->suggestCentroidShift($lat, $lng, $partners);

        return response()->json([
            'success' => true,
            'data' => $suggest,
        ]);
    }

    /**
     * POST /api/super-admin/map/confirm-point/{submission_id}
     * Confirm point and save to draft.
     */
    public function confirmPoint(Request $request, string $submissionId, MapService $mapService): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $draft = SppgDraft::with('partners')->findOrFail($submissionId);

        $confirmedLat = (float) $request->input('latitude');
        $confirmedLng = (float) $request->input('longitude');

        $validation = $mapService->validatePoint($confirmedLat, $confirmedLng, $draft->partners->toArray());

        $draft->update([
            'confirmed_latitude' => $confirmedLat,
            'confirmed_longitude' => $confirmedLng,
            'point_status' => $validation['status'],
            'map_confirmed' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Titik koordinat berhasil dikonfirmasi.',
            'data' => $draft->load('partners'),
        ]);
    }

    /**
     * Fetch SPPG layers data helper
     */
    private function getSppgLayersData(): array
    {
        return SPPG::where('status', 'active')
            ->with('partners')
            ->get()
            ->toArray();
    }

    /**
     * Fetch submission layers data helper
     */
    private function getSubmissionLayersData(): array
    {
        return SppgDraft::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('partners')
            ->get()
            ->toArray();
    }
}
