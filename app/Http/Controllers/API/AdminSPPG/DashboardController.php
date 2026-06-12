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
    private function getSppgId(Request $request): int
    {
        $sppgId = $request->user()->sppg_id ?? $request->user()->employee?->sppg_id;
        abort_if(!$sppgId, 403, 'Anda tidak terhubung dengan SPPG manapun.');
        return (int) $sppgId;
    }

    public function index(Request $request): JsonResponse
    {
        $sppgId = $this->getSppgId($request);

        // ── Statistik Jadwal Pengiriman ──────────────────────────────────────
        $scheduleStats = [
            'in_order'         => DeliverySchedule::whereHas('school', fn($q) => $q->where('sppg_id', $sppgId))->where('status', 'in_order')->count(),
            'delivering'       => DeliverySchedule::whereHas('school', fn($q) => $q->where('sppg_id', $sppgId))->where('status', 'delivering')->count(),
            'delivered'        => DeliverySchedule::whereHas('school', fn($q) => $q->where('sppg_id', $sppgId))->where('status', 'delivered')->count(),
            'revision_required'=> DeliverySchedule::whereHas('school', fn($q) => $q->where('sppg_id', $sppgId))->where('status', 'revision_required')->count(),
            'confirmed'        => DeliverySchedule::whereHas('school', fn($q) => $q->where('sppg_id', $sppgId))->where('status', 'confirmed')->count(),
            'rejected'         => DeliverySchedule::whereHas('school', fn($q) => $q->where('sppg_id', $sppgId))->where('status', 'rejected')->count(),
        ];

        // ── Statistik Riwayat Bulan Ini ──────────────────────────────────────

        $historyThisMonth = DeliveryHistory::whereHas('school', fn($q) => $q->where('sppg_id', $sppgId))
            ->whereBetween('departed_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])->get();

        $historyStats = [
            'total_deliveries'     => $historyThisMonth->count(),
            'total_distance_km'    => round($historyThisMonth->sum('distance_km'), 2),
            'avg_duration_minutes' => round($historyThisMonth->avg(fn($h) => $h->duration_minutes), 1),
        ];

        // ── Kurir Aktif (sedang mengantarkan) ────────────────────────────────
        $activeCourierCount = DeliverySchedule::whereHas('school', fn($q) => $q->where('sppg_id', $sppgId))
            ->where('status', 'delivering')
            ->distinct('courier_id')
            ->count('courier_id');

        // ── Jadwal Butuh Perhatian (delivered belum dikonfirmasi) ─────────────
        $pendingConfirmation = DeliverySchedule::whereHas('school', fn($q) => $q->where('sppg_id', $sppgId))
            ->where('status', 'delivered')
            ->with(['courier', 'school'])
            ->latest('arrived_at')
            ->take(5)
            ->get()
            ->map(fn($s) => [
                'id'           => $s->id,
                'courier_name' => $s->courier?->name,
                'school_name'  => $s->school?->name,
                'arrived_at'   => $s->arrived_at?->toIso8601String(),
            ]);

        // ── Statistik Sumber Daya ─────────────────────────────────────────────
        $resourceStats = [
            'total_couriers' => Employee::where('sppg_id', $sppgId)
                ->where(function ($q) {
                    $q->where('position', 'courier')
                      ->orWhereHas('role', fn($roleQuery) => $roleQuery->where('slug', 'courier'));
                })
                ->where('status', 'active')
                ->count(),
            'total_schools'  => School::where('sppg_id', $sppgId)->count(),
        ];

        // ── Staff Completeness Check ──
        $ahliGiziRegistered = Employee::where('sppg_id', $sppgId)
            ->whereHas('role', fn($q) => $q->where('slug', 'nutritionist'))
            ->exists();

        $adminLogistikRegistered = Employee::where('sppg_id', $sppgId)
            ->whereHas('role', fn($q) => $q->where('slug', 'logistics_admin'))
            ->exists();

        $isComplete = $ahliGiziRegistered && $adminLogistikRegistered;

        // Fetch stock alerts
        $stockService = app(\App\Services\Stock\StockService::class);
        $stockSummary = $stockService->getSummary($sppgId);
        $stockAlerts = collect($stockSummary)->filter(function ($item) {
            return $item['status'] === 'low' || $item['status'] === 'empty' || $item['has_expired'];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data'    => [
                'schedules'           => $scheduleStats,
                'history_this_month'  => $historyStats,
                'active_couriers'     => $activeCourierCount,
                'pending_confirmation'=> $pendingConfirmation,
                'resources'           => $resourceStats,
                'staff_completeness'  => [
                    'nutritionist_registered'   => $ahliGiziRegistered,
                    'logistics_admin_registered' => $adminLogistikRegistered,
                    'is_complete'               => $isComplete,
                ],
                'stock_alerts'        => $stockAlerts,
                'generated_at'        => now()->toIso8601String(),
            ],
        ]);
    }
}
