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

    private function sppgId(): string
    {
        return auth('api')->user()->sppg_id;
    }

    public function index(Request $request): JsonResponse
    {
        $schools = $this->schoolService->getAll(
            $request->only(['jenjang', 'search']),
            $request->integer('per_page', 15),
            $this->sppgId()
        );

        return response()->json([
            'success' => true,
            'data'    => SchoolResource::collection($schools),
            'meta'    => [
                'current_page' => $schools->currentPage(),
                'last_page'    => $schools->lastPage(),
                'total'        => $schools->total(),
            ],
        ]);
    }

    public function store(StoreSchoolRequest $request): JsonResponse
    {
        $data           = $request->validated();
        $data['sppg_id'] = $this->sppgId(); // paksa ke SPPG sendiri

        $school = $this->schoolService->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Sekolah mitra berhasil ditambahkan.',
            'data'    => new SchoolResource($school),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $school = $this->schoolService->findById($id, $this->sppgId());

        return response()->json([
            'success' => true,
            'data'    => new SchoolResource($school),
        ]);
    }

    public function update(UpdateSchoolRequest $request, string $id): JsonResponse
    {
        $school = $this->schoolService->update($id, $request->validated(), $this->sppgId());

        return response()->json([
            'success' => true,
            'message' => 'Sekolah mitra berhasil diperbarui.',
            'data'    => new SchoolResource($school),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->schoolService->delete($id, $this->sppgId());

        return response()->json([
            'success' => true,
            'message' => 'Sekolah mitra berhasil dihapus.',
        ]);
    }
}