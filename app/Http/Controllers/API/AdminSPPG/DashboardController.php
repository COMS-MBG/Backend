<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Models\DeliveryHistory;
use App\Models\DeliverySchedule;
use App\Models\Employee;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * DashboardController – AdminSPPG
 *
 * Statistik ringkas untuk halaman dashboard admin SPPG.
 * Fokus pada distribusi: jadwal aktif, riwayat, kurir.
 *
 * Endpoint: GET /api/admin-sppg/dashboard
 */
class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // ── Statistik Jadwal Pengiriman ──────────────────────────────────────
        $scheduleStats = [
            'in_order'         => DeliverySchedule::where('status', 'in_order')->count(),
            'delivering'       => DeliverySchedule::where('status', 'delivering')->count(),
            'delivered'        => DeliverySchedule::where('status', 'delivered')->count(),
            'revision_required'=> DeliverySchedule::where('status', 'revision_required')->count(),
            'confirmed'        => DeliverySchedule::where('status', 'confirmed')->count(),
            'rejected'         => DeliverySchedule::where('status', 'rejected')->count(),
        ];

        // ── Statistik Riwayat Bulan Ini ──────────────────────────────────────
        $historyThisMonth = DeliveryHistory::whereBetween('departed_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])->get();

        $historyStats = [
            'total_deliveries'     => $historyThisMonth->count(),
            'total_distance_km'    => round($historyThisMonth->sum('distance_km'), 2),
            'avg_duration_minutes' => round($historyThisMonth->avg(fn($h) => $h->duration_minutes), 1),
        ];

        // ── Kurir Aktif (sedang mengantarkan) ────────────────────────────────
        $activeCourierCount = DeliverySchedule::where('status', 'delivering')
            ->distinct('courier_id')
            ->count('courier_id');

        // ── Jadwal Butuh Perhatian (delivered belum dikonfirmasi) ─────────────
        $pendingConfirmation = DeliverySchedule::where('status', 'delivered')
            ->with(['courier', 'school'])
            ->latest('arrived_at')
            ->take(5)
            ->get()
            ->map(fn($s) => [
                'id'           => $s->id,
                'courier_name' => $s->courier?->name,
                'school_name'  => $s->school?->nama,
                'arrived_at'   => $s->arrived_at?->toIso8601String(),
            ]);

        // ── Statistik Sumber Daya ─────────────────────────────────────────────
        $resourceStats = [
            'total_couriers' => Employee::where('position', 'kurir')
                ->orWhereHas('role', fn($q) => $q->where('slug', 'kurir'))
                ->where('status', 'active')
                ->count(),
            'total_schools'  => School::count(),
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'schedules'           => $scheduleStats,
                'history_this_month'  => $historyStats,
                'active_couriers'     => $activeCourierCount,
                'pending_confirmation'=> $pendingConfirmation,
                'resources'           => $resourceStats,
                'generated_at'        => now()->toIso8601String(),
            ],
        ]);
    }
}
