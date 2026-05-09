<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Services\Employee\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $employeeService
    ) {}

    public function index(Request $request, string $sppgId): JsonResponse
    {
        $perPage = max((int) $request->get('per_page', 15), 1);

        $employees = $this->employeeService->getAll(
            $sppgId,
            $request->only([
                'jabatan',
                'status',
                'search'
            ]),
            $perPage
        );

        return response()->json([
            'success' => true,
            'data'    => EmployeeResource::collection($employees),
            'meta'    => [
                'current_page' => $employees->currentPage(),
                'last_page'    => $employees->lastPage(),
                'total'        => $employees->total(),
            ],
        ]);
    }

    public function store(
        StoreEmployeeRequest $request,
        string $sppgId
    ): JsonResponse {

        $employee = $this->employeeService->create(
            $request->validated(),
            $sppgId
        );

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil ditambahkan.',
            'data'    => new EmployeeResource($employee),
        ], 201);
    }

    public function show(string $sppgId, string $id): JsonResponse
    {
        $employee = $this->employeeService->findById(
            $id,
            $sppgId
        );

        return response()->json([
            'success' => true,
            'data'    => new EmployeeResource($employee),
        ]);
    }

    public function update(
        UpdateEmployeeRequest $request,
        string $sppgId,
        string $id
    ): JsonResponse {

        $employee = $this->employeeService->update(
            $id,
            $request->validated(),
            $sppgId
        );

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil diperbarui.',
            'data'    => new EmployeeResource($employee),
        ]);
    }

    public function destroy(
        string $sppgId,
        string $id
    ): JsonResponse {

        $this->employeeService->delete($id, $sppgId);

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil dihapus.',
        ]);
    }
}