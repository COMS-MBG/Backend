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

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'kota', 'search']);
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

    public function store(StoreSPPGRequest $request): JsonResponse
    {
        $sppg = $this->sppgService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'SPPG berhasil ditambahkan.',
            'data'    => new SPPGResource($sppg),
        ], 201);
    }

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
            'message' => 'SPPG berhasil diperbarui.',
            'data'    => new SPPGResource($sppg),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->sppgService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'SPPG berhasil dihapus.',
        ]);
    }

    public function assignSchool(Request $request, string $sppgId): JsonResponse
    {
        $request->validate(['school_id' => 'required|uuid|exists:schools,id']);
        $this->sppgService->assignSchool($sppgId, $request->school_id);

        return response()->json([
            'success' => true,
            'message' => 'Sekolah berhasil ditambahkan ke SPPG.',
        ]);
    }

    public function detachSchool(string $sppgId, string $schoolId): JsonResponse
    {
        $this->sppgService->detachSchool($sppgId, $schoolId);

        return response()->json([
            'success' => true,
            'message' => 'Sekolah berhasil dilepas dari SPPG.',
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