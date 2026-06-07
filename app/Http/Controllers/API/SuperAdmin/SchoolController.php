<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\School\StoreSchoolRequest;
use App\Http\Requests\School\UpdateSchoolRequest;
use App\Http\Resources\SchoolResource;
use App\Services\School\SchoolService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function __construct(private readonly SchoolService $schoolService) {}

    public function index(Request $request): JsonResponse
    {
        $schools = $this->schoolService->getAll(
            $request->only(['school_level', 'city', 'search', 'sppg_id', 'without_sppg']),
            $request->integer('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data'    => SchoolResource::collection($schools),
            'meta'    => [
                'current_page' => $schools->currentPage(),
                'last_page'    => $schools->lastPage(),
                'per_page'     => $schools->perPage(),
                'total'        => $schools->total(),
            ],
        ]);
    }

    public function store(StoreSchoolRequest $request): JsonResponse
    {
        $school = $this->schoolService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Sekolah mitra berhasil ditambahkan.',
            'data'    => new SchoolResource($school),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $school = $this->schoolService->findById($id);

        return response()->json([
            'success' => true,
            'data'    => new SchoolResource($school),
        ]);
    }

    public function update(UpdateSchoolRequest $request, string $id): JsonResponse
    {
        $school = $this->schoolService->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Sekolah mitra berhasil diperbarui.',
            'data'    => new SchoolResource($school),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->schoolService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Sekolah mitra berhasil dihapus.',
        ]);
    }
}