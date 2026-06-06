<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SPPG;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/super-admin/dashboard
     * Returns general statistics about SPPGs and partners.
     */
    public function index(Request $request): JsonResponse
    {
        $totalSppg = SPPG::where('status', '!=', 'deleted')->count();
        $totalSppgActive = SPPG::where('status', 'active')->count();
        $totalSppgInactive = SPPG::where('status', 'inactive')->count();
        $totalPartners = Partner::whereNotNull('sppg_id')->count();
        $totalDailyPortions = (int) Partner::whereNotNull('sppg_id')->sum('portion_count');

        return response()->json([
            'success' => true,
            'data' => [
                'total_sppg' => $totalSppg,
                'total_sppg_active' => $totalSppgActive,
                'total_sppg_inactive' => $totalSppgInactive,
                'total_partners' => $totalPartners,
                'total_daily_portions' => $totalDailyPortions,
            ],
        ]);
    }
}
