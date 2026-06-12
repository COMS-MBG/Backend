<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nutrition\RecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Services\SPPG\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function __construct(private readonly RecipeService $recipeService) {}

    public function index(Request $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        $filters = $request->only(['search', 'per_page']);
        $recipes = $this->recipeService->getAll($sppgId, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Daftar resep berhasil diambil.',
            'data'    => RecipeResource::collection($recipes->items()),
            'meta'    => [
                'current_page' => $recipes->currentPage(),
                'last_page'    => $recipes->lastPage(),
                'per_page'     => $recipes->perPage(),
                'total'        => $recipes->total(),
            ],
        ]);
    }

    public function dropdown(Request $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        $recipes = $this->recipeService->getAllForDropdown($sppgId);

        return response()->json([
            'success' => true,
            'data'    => $recipes,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $recipe = $this->recipeService->findById($sppgId, $id);

            return response()->json([
                'success' => true,
                'data'    => new RecipeResource($recipe),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resep tidak ditemukan.',
            ], 404);
        }
    }

    public function store(RecipeRequest $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $recipe = $this->recipeService->create($sppgId, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Resep berhasil dibuat.',
                'data'    => new RecipeResource($recipe),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan resep: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(RecipeRequest $request, int $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $recipe = $this->recipeService->update($sppgId, $id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Resep berhasil diperbarui.',
                'data'    => new RecipeResource($recipe),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resep tidak ditemukan.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui resep: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $this->recipeService->delete($sppgId, $id);

            return response()->json([
                'success' => true,
                'message' => 'Resep berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 409 ? 409 : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }
}