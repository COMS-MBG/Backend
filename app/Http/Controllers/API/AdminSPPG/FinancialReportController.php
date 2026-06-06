<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use App\Services\SPPG\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FinancialReportController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ReportService $reportService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:report.read',   only: ['index', 'rates']),
            new Middleware('permission:report.update', only: ['updateRate']),
        ];
    }

    /**
     * GET /api/admin-sppg/reports/financial
     *
     * Query params:
     *   date_from   (Y-m-d, default: 7 days ago)
     *   date_to     (Y-m-d, default: today)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
        ]);

        $sppgId   = $request->user()->sppg_id;
        $dateFrom = $request->input('date_from', now()->subDays(6)->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());

        $result = $this->reportService->getFinancialDeliveryReport($sppgId, $dateFrom, $dateTo);

        $numbered = $result['data']->map(function ($row, $index) {
            return array_merge(['no' => $index + 1], $row);
        })->values();

        return response()->json([
            'success' => true,
            'summary' => $result['summary'],
            'data'    => $numbered,
        ]);
    }

    /**
     * GET /api/admin-sppg/reports/financial/rates
     * Return all configured shipping rates.
     */
    public function rates(): JsonResponse
    {
        $rates = ShippingRate::orderBy('vehicle_type')->get();

        return response()->json([
            'success' => true,
            'data'    => $rates,
        ]);
    }

    /**
     * PUT /api/admin-sppg/reports/financial/rates/{vehicleType}
     * Update rate per km for a vehicle type.
     */
    public function updateRate(Request $request, string $vehicleType): JsonResponse
    {
        $request->validate([
            'rate_per_km' => 'required|numeric|min:0',
            'is_active'   => 'nullable|boolean',
            'notes'       => 'nullable|string|max:255',
        ]);

        $rate = ShippingRate::where('vehicle_type', $vehicleType)->firstOrFail();
        $rate->update($request->only(['rate_per_km', 'is_active', 'notes']));

        return response()->json([
            'success' => true,
            'message' => 'Shipping rate updated.',
            'data'    => $rate->fresh(),
        ]);
    }
}
