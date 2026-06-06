<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Distribution\CourierLocationService;
use Illuminate\Http\JsonResponse;
use App\Models\School;

class DistributionMapController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sppgId = $request->user()->sppg_id
        ?? $request->user()->employee?->sppg_id;

        if (!$sppgId) {
            return response()->json([
                'message' => 'Anda tidak terhubung dengan SPPG manapun.',
            ], 403);
        }

        // Ambil semua sekolah yang terhubung dengan SPPG ini
        $schools = School::with([
            'distributions' => function ($query) {
                // Filter berdasarkan tanggal hari ini
                $query->whereDate('scheduled_at', today())
                    ->orderBy('departed_at', 'asc'); // Urutkan berdasarkan waktu keberangkatan
            },
            'distributions.courier'
        ])
            ->where('sppg_id', $sppgId)
            ->get();

        // Format data agar mudah digunakan di frontend (Leaflet + markers)
        $markers = $schools->map(function ($school) {
            $distribution = $school->distributions->first(); // Ambil distribusi pertama untuk hari ini

            return [
                'id' => $school->id,
                'name' => $school->name,
                'address' => $school->address,
                'phone' => $school->phone,
                'latitude' => $school->latitude,
                'longitude' => $school->longitude,
                'type' => 'school', // Untuk identifikasi marker di frontend
                'distribution' => $distribution
                    ? [
                        'id' => $distribution->id,
                        'route_id' => $distribution->id,
                        'route_name' => 'Rute ' . ($school->name ?? ''),
                        'departure_time' => $distribution->departed_at?->toIso8601String(),
                        'est_arrival_time' => $distribution->scheduled_at?->toIso8601String(),
                        'package_count' => $school->student_count ?? 0,
                        'status' => $distribution->status,
                        'partner' => $distribution->courier
                            ? [
                                'id' => $distribution->courier->id,
                                'name' => $distribution->courier->name,
                                'phone' => $distribution->courier->phone,
                            ]
                            : null,
                    ]
                    : null,
            ];
        });

        // Hitung statistik
        $totalSchools = $schools->count();
        $totalPackages = $schools->sum(fn($school) => $school->distributions->isNotEmpty() ? ($school->student_count ?? 0) : 0);
        $deliveredCount = $schools->sum(fn($school) => $school->distributions->whereIn('status', ['delivered', 'confirmed'])->sum(fn() => $school->student_count ?? 0));
        $remainingCount = $schools->sum(fn($school) => $school->distributions->whereNotIn('status', ['delivered', 'confirmed'])->sum(fn() => $school->student_count ?? 0));

        return response()->json([
            'markers' => $markers,
            'summary' => [
                'total_schools' => $totalSchools,
                'total_packages' => $totalPackages,
                'delivered_count' => $deliveredCount,
                'remaining_count' => $remainingCount,
            ],
            'message' => 'Distribution map data for today',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
