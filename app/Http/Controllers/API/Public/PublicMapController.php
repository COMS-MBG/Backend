<?php

namespace App\Http\Controllers\API\Public;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\MapService;
use Illuminate\Http\JsonResponse;

class PublicMapController extends Controller
{
    public function __construct(private MapService $mapService) {}

    /**
     * GET /api/public/maps/sppg
     * Layer sekolah yang sudah terdaftar (status = 'served') untuk peta publik.
     */
    public function index(): JsonResponse
    {
        $all = $this->mapService->getSchoolsLayerData();

        // Hanya tampilkan mitra yang benar-benar sudah terlayani
        $served = array_values(array_filter($all, fn($s) => $s['status'] === 'served'));

        return response()->json([
            'success' => true,
            'count'   => count($served),
            'data'    => $served,
        ]);
    }

    /**
     * GET /api/public/maps/recommendations
     * Titik rekomendasi K-Means untuk publik (hanya centroid + jumlah sekolah).
     */
    public function recommendations(): JsonResponse
    {
        $raw = $this->mapService->getKMeansRecommendations();

        // Strip detail sekolah — cukup koordinat + school_count untuk publik
        $simplified = array_map(fn($r) => [
            'latitude'     => $r['latitude'],
            'longitude'    => $r['longitude'],
            'school_count' => $r['school_count'],
        ], $raw);

        return response()->json([
            'success' => true,
            'count'   => count($simplified),
            'data'    => $simplified,
        ]);
    }
}
