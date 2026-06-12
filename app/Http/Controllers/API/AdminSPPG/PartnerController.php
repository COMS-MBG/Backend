<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\ImportPartnerRequest;
use App\Http\Requests\Partner\StorePartnerRequest;
use App\Http\Requests\Partner\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Services\SPPG\PartnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function __construct(private readonly PartnerService $partnerService) {}

    public function index(Request $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        $filters = $request->only(['school_type', 'ownership_status', 'district', 'city', 'search']);
        $filters['per_page'] = $request->integer('per_page', 15);

        $partners = $this->partnerService->getAll($sppgId, $filters);

        return response()->json([
            'success' => true,
            'data'    => PartnerResource::collection($partners),
            'meta'    => [
                'current_page' => $partners->currentPage(),
                'last_page'    => $partners->lastPage(),
                'total'        => $partners->total(),
                'per_page'     => $partners->perPage(),
            ],
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        return response()->json([
            'success' => true,
            'data'    => $this->partnerService->getSummary($sppgId),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $partner = $this->partnerService->findById($sppgId, (int) $id);
            return response()->json([
                'success' => true,
                'data'    => new PartnerResource($partner),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Partner tidak ditemukan.',
            ], 404);
        }
    }

    public function store(StorePartnerRequest $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        $partner = $this->partnerService->create($sppgId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Partner school created successfully.',
            'data'    => new PartnerResource($partner),
        ], 201);
    }

    public function update(UpdatePartnerRequest $request, string $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $partner = $this->partnerService->update($sppgId, (int) $id, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Partner school updated successfully.',
                'data'    => new PartnerResource($partner),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Partner tidak ditemukan.',
            ], 404);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $this->partnerService->delete($sppgId, (int) $id);
            return response()->json([
                'success' => true,
                'message' => 'Partner school deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Partner tidak ditemukan.',
            ], 404);
        }
    }

    public function import(ImportPartnerRequest $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        $records = $request->input('records', []); 
        
        $count = $this->partnerService->importFromFile($sppgId, $records);
        return response()->json(['success' => true, 'message' => "Berhasil mengimpor {$count} partner."]);
    }
}