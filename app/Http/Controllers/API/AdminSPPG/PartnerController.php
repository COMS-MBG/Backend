<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\ImportPartnerRequest;
use App\Http\Requests\Partner\StorePartnerRequest;
use App\Http\Requests\Partner\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Services\Partner\PartnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function __construct(private readonly PartnerService $partnerService) {}

    // ─── List ──────────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $partners = $this->partnerService->getAll(
            $request->only(['bentuk', 'status', 'kecamatan', 'kabupaten_kota', 'search']),
            $request->integer('per_page', 15),
        );

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

    // ─── Summary ───────────────────────────────────────────────────────────────

    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->partnerService->getSummary(),
        ]);
    }

    // ─── Show ──────────────────────────────────────────────────────────────────

    public function show(string $id): JsonResponse
    {
        $partner = $this->partnerService->findById($id);

        return response()->json([
            'success' => true,
            'data'    => new PartnerResource($partner),
        ]);
    }

    // ─── Store ─────────────────────────────────────────────────────────────────

    public function store(StorePartnerRequest $request): JsonResponse
    {
        $partner = $this->partnerService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Partner sekolah berhasil ditambahkan.',
            'data'    => new PartnerResource($partner),
        ], 201);
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    public function update(UpdatePartnerRequest $request, string $id): JsonResponse
    {
        $partner = $this->partnerService->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Partner sekolah berhasil diperbarui.',
            'data'    => new PartnerResource($partner),
        ]);
    }

    // ─── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(string $id): JsonResponse
    {
        $this->partnerService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Partner sekolah berhasil dihapus.',
        ]);
    }

    // ─── Import ────────────────────────────────────────────────────────────────

    public function import(ImportPartnerRequest $request): JsonResponse
    {
        $file   = $request->file('file');
        $result = $this->partnerService->importFromFile($file->getRealPath());

        if (empty($result['success'])) {
            return response()->json([
                'success'          => false,
                'message'          => $result['errors'][0] ?? 'Import gagal.',
                'errors'           => $result['errors'] ?? [],
                'missing_columns'  => $result['missing_columns'] ?? [],
                'detected_columns' => $result['detected_columns'] ?? [],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Import selesai: {$result['created']} ditambahkan, {$result['updated']} diperbarui, {$result['skipped']} dilewati.",
            'data'    => $result,
        ]);
    }
}
