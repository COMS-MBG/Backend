<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Services\Distribution\CourierLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * DistributionMapController – AdminSPPG namespace
 *
 * Endpoint peta distribusi untuk panel admin SPPG.
 * Delegasi ke CourierLocationService untuk data real-time.
 *
 * Base URL: /api/admin-sppg/maps/distribution
 *
 * REVERB: admin subscribe ke channel presence-distribution.map
 * untuk mendapat update posisi kurir secara real-time.
 */
class DistributionMapController extends Controller
{
    public function __construct(private readonly CourierLocationService $locationService)
    {
    }

    /**
     * [GET] Semua kurir aktif beserta posisi terakhirnya.
     * Dipakai sebagai data awal peta (initial load).
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin_logistik', 'admin_sppg', 'super_admin']),
            403
        );

        $data = $this->locationService->getActiveCourierLocations();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'count'   => count($data),
            'note'    => 'Subscribe to presence-distribution.map via Reverb for real-time updates.',
        ]);
    }

    // Store, update, destroy tidak relevan untuk map controller
    public function store(Request $request): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Not implemented.'], 405);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Not implemented.'], 405);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Not implemented.'], 405);
    }

    public function destroy(string $id): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Not implemented.'], 405);
    }
}
