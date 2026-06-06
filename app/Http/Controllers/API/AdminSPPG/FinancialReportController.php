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
            new Middleware('permission:finance.read', only: ['index', 'show']),
            new Middleware('permission:finance.create', only: ['store']),
            new Middleware('permission:finance.update', only: ['update']),
            new Middleware('permission:finance.delete', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
