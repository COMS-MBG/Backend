<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Services\SPPG\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OperationalReportController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ReportService $reportService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:report.read', only: ['index']),
        ];
    }

    /**
     * GET /api/admin-sppg/reports/operational
     *
     * Query params:
     *   date_from   (Y-m-d, default: 7 days ago)
     *   date_to     (Y-m-d, default: today)
     *   type        (menu|stock|delivery, default: all)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'type'      => 'nullable|in:menu,stock,delivery',
        ]);

        $sppgId   = $request->user()->sppg_id;
        $dateFrom = $request->input('date_from', now()->subDays(6)->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());
        $type     = $request->input('type');

        $result = $this->reportService->getOperationalReport($sppgId, $dateFrom, $dateTo, $type);

        // Number the rows
        $numbered = $result['data']->map(function ($row, $index) {
            return array_merge(['no' => $index + 1], $row);
        })->values();

        return response()->json([
            'success' => true,
            'summary' => $result['summary'],
            'data'    => $numbered,
        ]);
    }
}
