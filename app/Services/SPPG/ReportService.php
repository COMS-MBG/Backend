<?php

namespace App\Services\SPPG;

use App\Models\Menu;
use App\Models\StockItem;
use App\Models\StockTransaction;
use App\Models\DeliveryHistory;
use App\Models\ShippingRate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // ─── Activity type constants ──────────────────────────────────────────────

    const TYPE_MENU     = 'menu';
    const TYPE_STOCK    = 'stock';
    const TYPE_DELIVERY = 'delivery';

    // ─── Operational Report ───────────────────────────────────────────────────

    /**
     * Build operational report data for a given SPPG.
     *
     * @param int    $sppgId
     * @param string $dateFrom  Y-m-d
     * @param string $dateTo    Y-m-d
     * @param string|null $type Filter by activity type: menu|stock|delivery|null (all)
     * @return array { data: Collection, summary: array }
     */
    public function getOperationalReport(
        int $sppgId,
        string $dateFrom,
        string $dateTo,
        ?string $type = null
    ): array {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to   = Carbon::parse($dateTo)->endOfDay();

        $rows = collect();

        // ── Menu Activities ───────────────────────────────────────────────────
        if ($type === null || $type === self::TYPE_MENU) {
            $menus = Menu::where('sppg_id', $sppgId)
                ->whereBetween('created_at', [$from, $to])
                ->select('id', 'sppg_id', 'name', 'week_start', 'week_end', 'status', 'created_at', 'updated_at')
                ->get();

            foreach ($menus as $menu) {
                $rows->push([
                    'type'          => self::TYPE_MENU,
                    'activity_name' => 'Menu Creation',
                    'description'   => $menu->name . ' (Week: ' . $menu->week_start->format('d M Y') . ')',
                    'started_at'    => $menu->created_at,
                    'completed_at'  => $menu->updated_at,
                    'reference_id'  => $menu->id,
                    'status'        => $menu->status,
                ]);
            }
        }

        // ── Stock Activities ──────────────────────────────────────────────────
        if ($type === null || $type === self::TYPE_STOCK) {
            $stockItems = StockItem::where('sppg_id', $sppgId)
                ->whereBetween('created_at', [$from, $to])
                ->with('ingredient:id,name')
                ->select('id', 'ingredient_id', 'quantity', 'unit', 'status', 'created_at', 'approved_at')
                ->get();

            foreach ($stockItems as $item) {
                $rows->push([
                    'type'          => self::TYPE_STOCK,
                    'activity_name' => 'Stock Addition',
                    'description'   => ($item->ingredient->name ?? '-') . ' +' . $item->quantity . ' ' . $item->unit,
                    'started_at'    => $item->created_at,
                    'completed_at'  => $item->approved_at ?? $item->created_at,
                    'reference_id'  => $item->id,
                    'status'        => $item->status,
                ]);
            }

            $stockTxns = StockTransaction::where('sppg_id', $sppgId)
                ->where('transaction_type', 'usage')
                ->whereBetween('created_at', [$from, $to])
                ->with('ingredient:id,name')
                ->select('id', 'ingredient_id', 'quantity', 'transaction_type', 'notes', 'created_at')
                ->get();

            foreach ($stockTxns as $txn) {
                $rows->push([
                    'type'          => self::TYPE_STOCK,
                    'activity_name' => 'Stock Usage',
                    'description'   => ($txn->ingredient->name ?? '-') . ' -' . $txn->quantity,
                    'started_at'    => $txn->created_at,
                    'completed_at'  => $txn->created_at,
                    'reference_id'  => $txn->id,
                    'status'        => 'done',
                ]);
            }
        }

        // ── Delivery Activities ───────────────────────────────────────────────
        if ($type === null || $type === self::TYPE_DELIVERY) {
            // We join delivery_histories to delivery_schedules to filter by sppg_id
            // via the courier (employee.sppg_id)
            $deliveries = DeliveryHistory::whereHas('schedule', function ($q) use ($sppgId) {
                    $q->whereHas('courier', fn($q2) => $q2->where('sppg_id', $sppgId));
                })
                ->whereBetween('departed_at', [$from, $to])
                ->with([
                    'schedule:id,vehicle_type',
                    'school:id,name',
                ])
                ->select('id', 'delivery_schedule_id', 'school_id', 'school_name', 'departed_at', 'arrived_at', 'distance_km', 'vehicle_type')
                ->get();

            foreach ($deliveries as $delivery) {
                $rows->push([
                    'type'          => self::TYPE_DELIVERY,
                    'activity_name' => 'Menu Delivery',
                    'description'   => 'To: ' . ($delivery->school_name ?? $delivery->school?->name ?? '-'),
                    'started_at'    => $delivery->departed_at,
                    'completed_at'  => $delivery->arrived_at,
                    'reference_id'  => $delivery->id,
                    'status'        => $delivery->arrived_at ? 'delivered' : 'in_progress',
                    'distance_km'   => $delivery->distance_km,
                    'vehicle_type'  => $delivery->vehicle_type ?? $delivery->schedule?->vehicle_type,
                ]);
            }
        }

        // Sort by started_at desc
        $sorted = $rows->sortByDesc('started_at')->values();

        $summary = [
            'total'           => $sorted->count(),
            'menu_count'      => $sorted->where('type', self::TYPE_MENU)->count(),
            'stock_count'     => $sorted->where('type', self::TYPE_STOCK)->count(),
            'delivery_count'  => $sorted->where('type', self::TYPE_DELIVERY)->count(),
            'date_from'       => $dateFrom,
            'date_to'         => $dateTo,
        ];

        return [
            'data'    => $sorted,
            'summary' => $summary,
        ];
    }

    // ─── Financial Report (Phase 1 — Delivery Cost) ───────────────────────────

    /**
     * Build delivery cost financial report for a given SPPG.
     *
     * @param int    $sppgId
     * @param string $dateFrom  Y-m-d
     * @param string $dateTo    Y-m-d
     * @return array { data: Collection, summary: array }
     */
    public function getFinancialDeliveryReport(
        int $sppgId,
        string $dateFrom,
        string $dateTo
    ): array {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to   = Carbon::parse($dateTo)->endOfDay();

        // Fetch all active shipping rates once
        $rates = ShippingRate::active()->pluck('rate_per_km', 'vehicle_type');

        $deliveries = DeliveryHistory::whereHas('schedule', function ($q) use ($sppgId) {
                $q->whereHas('courier', fn($q2) => $q2->where('sppg_id', $sppgId));
            })
            ->whereBetween('departed_at', [$from, $to])
            ->with('schedule:id,vehicle_type')
            ->select('id', 'delivery_schedule_id', 'school_name', 'courier_name', 'departed_at', 'arrived_at', 'distance_km', 'vehicle_type', 'vehicle_plate')
            ->get();

        $rows = $deliveries->map(function ($delivery) use ($rates) {
            $vehicleType = $delivery->vehicle_type
                ?? $delivery->schedule?->vehicle_type
                ?? 'motorcycle';

            $distanceKm = (float) ($delivery->distance_km ?? 0);
            $ratePerKm  = (float) ($rates[$vehicleType] ?? 0);
            $cost       = round($distanceKm * $ratePerKm, 0);

            return [
                'id'           => $delivery->id,
                'date'         => $delivery->departed_at?->toDateString(),
                'school_name'  => $delivery->school_name,
                'courier_name' => $delivery->courier_name,
                'vehicle_type' => $vehicleType,
                'vehicle_plate'=> $delivery->vehicle_plate,
                'distance_km'  => $distanceKm,
                'rate_per_km'  => $ratePerKm,
                'total_cost'   => $cost,
                'departed_at'  => $delivery->departed_at,
                'arrived_at'   => $delivery->arrived_at,
            ];
        });

        $totalCost     = $rows->sum('total_cost');
        $totalDistance = $rows->sum('distance_km');

        $summary = [
            'total_deliveries' => $rows->count(),
            'total_distance_km'=> round($totalDistance, 2),
            'total_cost_idr'   => (int) $totalCost,
            'date_from'        => $dateFrom,
            'date_to'          => $dateTo,
            'rates_used'       => $rates,
        ];

        return [
            'data'    => $rows->sortByDesc('date')->values(),
            'summary' => $summary,
        ];
    }
}
