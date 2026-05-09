<?php

namespace App\Http\Controllers\Api\Distribution;

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
 *   POST /api/distribution/map/location/{schedule}   – courier GPS ping
 *
 * PINTU KELUAR:
 *   GET  /api/distribution/map/active-couriers       – admin live map
 *   GET  /api/distribution/map/trail/{schedule}      – route trail for a delivery
 *   POST /api/distribution/map/optimize-route        – get optimized route
 *
 * REVERB CHANNELS (subscribed by FE):
 *   presence-distribution.map  → event: distribution.courier.location
 *   presence-distribution.operations → event: distribution.status.updated
 * ============================================================
 */
class SpatialMapController extends Controller
{
    public function __construct(
        private readonly CourierLocationService  $locationService,
        private readonly RouteOptimizationService $routeService,
    ) {
    }

    // ─── [POST] Courier sends GPS ping ───────────────────────────────────────
    // PINTU MASUK – called by mobile app every ~5 seconds while delivering
    public function recordLocation(RecordLocationRequest $request, DeliverySchedule $schedule): JsonResponse
    {
        // Ensure courier owns this schedule
        $courierId = $request->user()->employee?->id;
        abort_unless($schedule->courier_id === $courierId || $request->user()->hasRole('super_admin'), 403);

        $location = $this->locationService->recordLocation($schedule, $request->validated());

        return response()->json([
            'success'     => true,
            'recorded_at' => $location->recorded_at->toIso8601String(),
        ]);
    }

    // ─── [GET] All active couriers' latest locations ──────────────────────────
    // PINTU KELUAR – admin live map initial load
    public function activeCouriers(Request $request): JsonResponse
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
        ]);
    }

    // ─── [GET] Full location trail for a specific delivery ────────────────────
    // PINTU KELUAR – route replay / detailed view
    public function locationTrail(Request $request, DeliverySchedule $schedule): JsonResponse
    {
        // Courier can only see their own trail
        if ($request->user()->hasRole('courier')) {
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

    // ─── [POST] Calculate optimized delivery route ────────────────────────────
    // PINTU MASUK/KELUAR – admin logistik requests optimized route before submitting task
    // Uses Nearest-Neighbour TSP + OSRM road routing
    public function optimizeRoute(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin_logistik', 'admin_sppg', 'super_admin']),
            403
        );

        $validated = $request->validate([
            'origin'              => ['required', 'array'],
            'origin.lat'          => ['required', 'numeric', 'between:-90,90'],
            'origin.lng'          => ['required', 'numeric', 'between:-180,180'],
            'waypoints'           => ['required', 'array', 'min:1', 'max:30'],
            'waypoints.*.lat'     => ['required', 'numeric', 'between:-90,90'],
            'waypoints.*.lng'     => ['required', 'numeric', 'between:-180,180'],
            'waypoints.*.school_id' => ['required', 'integer', 'exists:schools,id'],
            'waypoints.*.name'    => ['nullable', 'string'],
        ]);

        $result = $this->routeService->optimize($validated['origin'], $validated['waypoints']);

        return response()->json([
            'success' => true,
            'data'    => [
                'ordered_waypoints'  => $result['ordered_waypoints'],
                'geojson'            => $result['geojson'],         // LineString for map rendering
                'total_distance_km'  => $result['total_distance_km'],
                'total_duration_min' => $result['total_duration_min'],
            ],
        ]);
    }

    // ─── [GET] SPG depot coordinates ─────────────────────────────────────────
    // PINTU KELUAR – map uses this as the origin point "A"
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