<?php

namespace App\Http\Controllers\API\Distribution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distribution\RecordLocationRequest;
use App\Models\DeliverySchedule;
use App\Services\Distribution\CourierLocationService;
use App\Services\Distribution\RouteOptimizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ============================================================
 *  SPATIAL MAP & ANALYTICS CONTROLLER
 * ============================================================
 *
 * PINTU MASUK:
 *   POST /api/distribution/map/location/{schedule}   – kurir kirim GPS ping
 *
 * PINTU KELUAR:
 *   GET  /api/distribution/map/active-couriers       – admin live map
 *   GET  /api/distribution/map/trail/{schedule}      – rute trail satu pengiriman
 *   GET  /api/distribution/map/depot                 – koordinat depot SPG
 *   POST /api/distribution/map/optimize-route        – optimasi rute
 *
 * REVERB CHANNELS (subscribe dari FE):
 *   presence-distribution.map       → event: distribution.courier.location
 *   presence-distribution.operations → event: distribution.status.updated
 * ============================================================
 */
class SpatialMapController extends Controller
{
    public function __construct(
        private readonly CourierLocationService   $locationService,
        private readonly RouteOptimizationService $routeService,
    ) {
    }

    // ─── [POST] Kurir kirim GPS ping ─────────────────────────────────────────
    // PINTU MASUK – dipanggil mobile app setiap ~5 detik saat mengantarkan
    public function recordLocation(RecordLocationRequest $request, DeliverySchedule $schedule): JsonResponse
    {
        $courierId = $request->user()->employee?->id;
        abort_unless(
            $schedule->courier_id === $courierId || $request->user()->hasAnyRole(['super_admin']),
            403,
            'You are not assigned to this delivery.'
        );

        $location = $this->locationService->recordLocation($schedule, $request->validated());

        return response()->json([
            'success'     => true,
            'recorded_at' => $location->recorded_at->toIso8601String(),
        ]);
    }

    // ─── [GET] Semua lokasi kurir aktif (admin live map) ──────────────────────
    // PINTU KELUAR
    public function activeCouriers(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()->hasAnyRole(['logistics_admin', 'sppg_admin', 'super_admin']),
            403
        );

        $data = $this->locationService->getActiveCourierLocations();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'count'   => count($data),
        ]);
    }

    // ─── [GET] Trail lokasi satu pengiriman ───────────────────────────────────
    // PINTU KELUAR – replay rute / tampilan detail
    public function locationTrail(Request $request, DeliverySchedule $schedule): JsonResponse
    {
        // Kurir hanya bisa lihat trail miliknya sendiri
        if ($request->user()->hasAnyRole(['courier'])) {
            $courierId = $request->user()->employee?->id;
            abort_unless($schedule->courier_id === $courierId, 403);
        }

        $trail = $this->locationService->getLocationTrail($schedule);

        return response()->json([
            'success'     => true,
            'schedule_id' => $schedule->id,
            'data'        => $trail,
            'total_pings' => count($trail),
        ]);
    }

    // ─── [POST] Optimasi rute pengiriman ─────────────────────────────────────
    // PINTU MASUK/KELUAR – admin logistik minta rute optimal sebelum submit tugas
    // Algoritma: Nearest-Neighbour TSP + OSRM road routing
    public function optimizeRoute(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()->hasAnyRole(['logistics_admin', 'sppg_admin', 'super_admin']),
            403
        );

        $validated = $request->validate([
            'origin'                => ['required', 'array'],
            'origin.lat'            => ['required', 'numeric', 'between:-90,90'],
            'origin.lng'            => ['required', 'numeric', 'between:-180,180'],
            'waypoints'             => ['required', 'array', 'min:1', 'max:30'],
            'waypoints.*.lat'       => ['required', 'numeric', 'between:-90,90'],
            'waypoints.*.lng'       => ['required', 'numeric', 'between:-180,180'],
            'waypoints.*.school_id' => ['required', 'string', 'exists:schools,id'],
            'waypoints.*.name'      => ['nullable', 'string'],
        ]);

        $result = $this->routeService->optimize($validated['origin'], $validated['waypoints']);

        return response()->json([
            'success' => true,
            'data'    => [
                'ordered_waypoints'  => $result['ordered_waypoints'],
                'geojson'            => $result['geojson'],
                'total_distance_km'  => $result['total_distance_km'],
                'total_duration_min' => $result['total_duration_min'],
            ],
        ]);
    }

    // ─── [GET] Koordinat depot SPG ────────────────────────────────────────────
    // PINTU KELUAR – map pakai ini sebagai titik awal "A"
    public function depotLocation(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'name'      => config('distribution.depot_name', 'SPG Depot'),
                'latitude'  => config('distribution.depot_lat'),
                'longitude' => config('distribution.depot_lng'),
            ],
        ]);
    }
}