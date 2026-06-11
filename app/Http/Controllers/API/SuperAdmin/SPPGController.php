<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SPPG\StoreSPPGRequest;
use App\Http\Requests\SPPG\UpdateSPPGRequest;
use App\Http\Resources\SPPGResource;
use App\Services\SPPG\SPPGCapacityService;
use App\Services\SPPG\SPPGService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SPPGController extends Controller
{
    public function __construct(
        private readonly SPPGService         $sppgService,
        private readonly SPPGCapacityService $capacityService,
    ) {}

    // ─── Daftar SPPG ─────────────────────────────────────────────────────────

    /**
     * GET /api/super-admin/sppg
     * List all SPPGs with summary stats.
     * Filters: status, city, district, search, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'city', 'district', 'search']);
        $sppgs   = $this->sppgService->getAll($filters, $request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => SPPGResource::collection($sppgs),
            'meta'    => [
                'current_page' => $sppgs->currentPage(),
                'last_page'    => $sppgs->lastPage(),
                'per_page'     => $sppgs->perPage(),
                'total'        => $sppgs->total(),
            ],
            'stats'   => $this->sppgService->getSummaryStats(),
        ]);
    }

    /**
     * POST /api/super-admin/sppg
     */
    public function store(\App\Http\Requests\SPPG\RegisterSppgRequest $request, \App\Services\SPPG\SppgRegistrationService $registrationService): JsonResponse
    {
        $sppg = $registrationService->register($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'SPPG registered successfully.',
            'data'    => new SPPGResource($sppg),
        ], 201);
    }

    /**
     * GET /api/super-admin/sppg/{id}
     * Detail SPPG — includes capacity breakdown.
     */
    public function show(string $id): JsonResponse
    {
        $sppg = $this->sppgService->findById($id);

        return response()->json([
            'success'  => true,
            'data'     => new SPPGResource($sppg),
            'capacity' => $this->capacityService->getCapacityStatus($sppg),
        ]);
    }

    public function update(UpdateSPPGRequest $request, string $id): JsonResponse
    {
        $sppg = $this->sppgService->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'SPPG updated successfully.',
            'data'    => new SPPGResource($sppg),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        \Illuminate\Support\Facades\DB::transaction(function() use ($id) {
            $this->sppgService->delete($id);
            \App\Models\User::where('sppg_id', $id)->update(['is_active' => false]);
            \App\Models\Partner::where('sppg_id', $id)->update(['sppg_id' => null]);
        });

        return response()->json([
            'success' => true,
            'message' => 'SPPG deleted successfully.',
        ]);
    }

    public function deactivate(string $id): JsonResponse
    {
        \Illuminate\Support\Facades\DB::transaction(function() use ($id) {
            $sppg = \App\Models\SPPG::findOrFail($id);
            $sppg->status = 'inactive';
            $sppg->save();
            \App\Models\User::where('sppg_id', $id)->update(['is_active' => false]);
        });

        return response()->json([
            'success' => true,
            'message' => 'SPPG deactivated successfully.',
        ]);
    }

    public function activate(string $id): JsonResponse
    {
        \Illuminate\Support\Facades\DB::transaction(function() use ($id) {
            $sppg = \App\Models\SPPG::findOrFail($id);
            $sppg->status = 'active';
            $sppg->save();
            \App\Models\User::where('sppg_id', $id)->update(['is_active' => true]);
        });

        return response()->json([
            'success' => true,
            'message' => 'SPPG activated successfully.',
        ]);
    }

    // ─── Region Dropdown Endpoints ────────────────────────────────────────────

    /**
     * GET /api/super-admin/sppg/regions/cities
     * Return distinct cities that have at least one SPPG — for the first dropdown.
     */
    public function regionCities(): JsonResponse
    {
        $cities = $this->sppgService->getAvailableCities();

        return response()->json([
            'success' => true,
            'data'    => $cities,
        ]);
    }

    /**
     * GET /api/super-admin/sppg/regions/districts?city=Kota+Malang
     * Return districts in the selected city — for the dependent second dropdown.
     */
    public function regionDistricts(Request $request): JsonResponse
    {
        $request->validate(['city' => 'required|string']);

        $districts = $this->sppgService->getAvailableDistricts($request->input('city'));

        return response()->json([
            'success' => true,
            'data'    => $districts,
        ]);
    }

    // ─── Detail Tabs ──────────────────────────────────────────────────────────

    /**
     * GET /api/super-admin/sppg/{id}/partners
     * Tab Mitra — show all partner schools under this SPPG with distance info.
     */
    public function partners(string $id, \App\Services\SuperAdmin\MapService $mapService): JsonResponse
    {
        $sppg     = \App\Models\SPPG::findOrFail($id);
        $partners = $this->sppgService->getPartners($id);

        $data = $partners->map(function ($p) use ($sppg, $mapService) {
            $dist  = $mapService->calculateHaversineDistance($sppg->latitude, $sppg->longitude, $p->latitude, $p->longitude);
<<<<<<< Updated upstream
            
            // Cache OSRM route calculations to prevent blocking single-threaded serve connection queues
            $cacheKey = "route_" . md5("{$sppg->latitude}_{$sppg->longitude}_{$p->latitude}_{$p->longitude}");
            $route = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addDays(30), function () use ($mapService, $sppg, $p) {
                return $mapService->getRouteDurationAndDistance($sppg->latitude, $sppg->longitude, $p->latitude, $p->longitude);
            });
=======
            $route = $mapService->getRouteDurationAndDistance($sppg->latitude, $sppg->longitude, $p->latitude, $p->longitude);
>>>>>>> Stashed changes

            return [
                'id'                => $p->id,
                'school_name'       => $p->school_name,
                'npsn'              => $p->npsn,
                'school_type'       => $p->school_type,
                'ownership_status'  => $p->ownership_status,
                'address'           => $p->address,
                'district'          => $p->district,
                'city'              => $p->city,
                'latitude'          => $p->latitude,
                'longitude'         => $p->longitude,
                'portion_count'     => $p->portion_count,
                'distance_km'       => $dist,
                'estimated_minutes' => $route ? $route['duration_minutes'] : null,
                'distance_status'   => $dist <= 5.0 ? 'safe' : 'review',
            ];
        });

        return response()->json([
            'success'       => true,
            'sppg_id'       => $sppg->id,
            'sppg_name'     => $sppg->name,
            'total_partners'=> $data->count(),
            'total_portion' => $data->sum('portion_count'),
            'data'          => $data->values(),
        ]);
    }

    /**
     * GET /api/super-admin/sppg/{id}/menus
     * Tab Menu — all menus grouped by period with recipes per day.
     * Order: published → scheduled → planned → archived
     */
    public function menus(string $id): JsonResponse
    {
        // Verify SPPG exists
        \App\Models\SPPG::findOrFail($id);

        $periods = $this->sppgService->getMenusGrouped($id);

        return response()->json([
            'success'      => true,
            'sppg_id'      => $id,
            'total_periods'=> count($periods),
            'data'         => $periods,
        ]);
    }

    public function assignSchool(Request $request, string $sppgId): JsonResponse
    {
        $request->validate(['school_id' => 'required|integer|exists:schools,id']);
        $this->sppgService->assignSchool($sppgId, $request->school_id);

        return response()->json([
            'success' => true,
            'message' => 'School assigned to SPPG successfully.',
        ]);
    }

    public function detachSchool(string $sppgId, string $schoolId): JsonResponse
    {
        $this->sppgService->detachSchool($sppgId, $schoolId);

        return response()->json([
            'success' => true,
            'message' => 'School detached from SPPG successfully.',
        ]);
    }

    public function capacityOverview(): JsonResponse
    {
        $overcapacity = $this->capacityService->getOvercapacitySppgs();

        return response()->json([
            'success' => true,
            'data'    => SPPGResource::collection($overcapacity),
            'total'   => $overcapacity->count(),
        ]);
    }
}