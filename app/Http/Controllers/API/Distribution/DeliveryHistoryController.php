<?php

namespace App\Http\Controllers\Api\Distribution;

use App\Http\Controllers\Controller;
use App\Http\Resources\Distribution\DeliveryHistoryResource;
use App\Models\DeliveryHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ============================================================
 *  DELIVERY HISTORY CONTROLLER
 * ============================================================
 *
 * "PINTU KELUAR" – semua endpoint di sini bersifat READ ONLY.
 * Data masuk melalui DeliveryScheduleController::confirmDelivery()
 *
 * Base path: /api/distribution/histories
 * ============================================================
 */
class DeliveryHistoryController extends Controller
{
    // ─── [GET] Paginated list of delivery histories ───────────────────────────
    // PINTU KELUAR – FE history page
    public function index(Request $request): JsonResponse
    {
        $query = DeliveryHistory::with(['confirmedBy'])
            ->latest('departed_at');

        // Courier sees only their own history
        if ($request->user()->hasRole('courier')) {
            $courierId = $request->user()->employee?->id;
            $query->where('courier_id', $courierId);
        }

        // Filters
        if ($request->filled('courier_id')) {
            $query->where('courier_id', $request->courier_id);
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('departed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('departed_at', '<=', $request->date_to);
        }

        $histories = $query->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => DeliveryHistoryResource::collection($histories),
            'meta'    => [
                'current_page' => $histories->currentPage(),
                'last_page'    => $histories->lastPage(),
                'total'        => $histories->total(),
            ],
        ]);
    }

    // ─── [GET] Single history detail ──────────────────────────────────────────
    // PINTU KELUAR
    public function show(DeliveryHistory $history): JsonResponse
    {
        $history->load(['courier', 'school', 'confirmedBy', 'schedule']);

        return response()->json([
            'success' => true,
            'data'    => new DeliveryHistoryResource($history),
        ]);
    }

    // ─── [GET] Analytics summary ──────────────────────────────────────────────
    // PINTU KELUAR – used by Spatial & Analytics feature
    public function analytics(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin_logistik', 'admin_sppg', 'super_admin']),
            403
        );

        $from = $request->date('date_from', now()->startOfMonth());
        $to   = $request->date('date_to', now()->endOfMonth());

        $histories = DeliveryHistory::whereBetween('departed_at', [$from, $to])->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'period' => [
                    'from' => $from->toDateString(),
                    'to'   => $to->toDateString(),
                ],
                'total_deliveries'      => $histories->count(),
                'total_distance_km'     => round($histories->sum('distance_km'), 2),
                'avg_duration_minutes'  => round($histories->avg(fn($h) => $h->duration_minutes), 1),
                'deliveries_per_courier'=> $histories->groupBy('courier_name')->map->count()->sortDesc(),
                'deliveries_per_school' => $histories->groupBy('school_name')->map->count()->sortDesc(),
                'vehicle_breakdown'     => $histories->groupBy('vehicle_type')->map->count(),
            ],
        ]);
    }
}