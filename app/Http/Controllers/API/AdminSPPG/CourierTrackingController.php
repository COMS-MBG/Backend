<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Models\DeliverySchedule;
use App\Services\Distribution\CourierLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CourierTrackingController – AdminSPPG namespace
 *
 * Endpoint tracking kurir untuk panel admin SPPG.
 * - Admin melihat posisi kurir via updateLocation() (polling fallback)
 * - Kurir kirim posisi GPS via updateLocation()
 *
 * Base URL: /api/admin-sppg/tracking
 *
 * NOTE: Untuk real-time, gunakan Reverb (presence-distribution.map).
 *       Endpoint ini adalah REST fallback jika websocket tidak tersedia.
 */
class CourierTrackingController extends Controller
{
    public function __construct(private readonly CourierLocationService $locationService)
    {
    }

    /**
     * [POST] Kurir kirim update lokasi GPS.
     * Endpoint: POST /api/admin-sppg/tracking/update-location
     *
     * Body:
     *   schedule_id    : int      (required)
     *   latitude       : float    (required)
     *   longitude      : float    (required)
     *   speed_kmh      : float    (optional)
     *   heading_degrees: float    (optional)
     *   accuracy_meters: float    (optional)
     */
    public function updateLocation(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()->hasAnyRole(['courier', 'super_admin']),
            403,
            'Only couriers can update location.'
        );

        $validated = $request->validate([
            'schedule_id'     => ['required', 'integer', 'exists:delivery_schedules,id'],
            'latitude'        => ['required', 'numeric', 'between:-90,90'],
            'longitude'       => ['required', 'numeric', 'between:-180,180'],
            'speed_kmh'       => ['nullable', 'numeric', 'min:0', 'max:200'],
            'heading_degrees' => ['nullable', 'numeric', 'between:0,360'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0'],
        ]);

        $schedule = DeliverySchedule::findOrFail($validated['schedule_id']);

        // Pastikan kurir yang mengirim adalah kurir yang bertugas
        $courierId = $request->user()->employee?->id;
        abort_unless(
            $schedule->courier_id === $courierId || $request->user()->hasAnyRole(['super_admin']),
            403,
            'You are not assigned to this delivery.'
        );

        $location = $this->locationService->recordLocation($schedule, $validated);

        return response()->json([
            'success'     => true,
            'message'     => 'Location recorded.',
            'recorded_at' => $location->recorded_at->toIso8601String(),
        ]);
    }

    /**
     * [GET] Semua kurir yang sedang aktif (untuk polling fallback).
     * Endpoint: GET /api/admin-sppg/tracking/active
     */
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

    /**
     * [GET] Trail lokasi satu pengiriman.
     * Endpoint: GET /api/admin-sppg/tracking/{scheduleId}/trail
     */
    public function trail(Request $request, int $scheduleId): JsonResponse
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin_logistik', 'admin_sppg', 'super_admin', 'courier']),
            403
        );

        $schedule = DeliverySchedule::findOrFail($scheduleId);

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
}
