<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nutrition\RecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Services\SPPG\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CONTROLLER untuk Fitur Master Resep.
 * Endpoint CRUD + dropdown untuk dipakai di Perencanaan Menu.
 */
class RecipeController extends Controller
{
    public function __construct(
        private readonly RecipeService $recipeService
    ) {}

    // =============================================
    // GET /api/recipes
    // PINTU TARIK DATA: Semua resep + info nutrisinya
    // =============================================
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'per_page']);
        $recipes = $this->recipeService->getAll($filters);

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

    // =============================================
    // GET /api/recipes/dropdown
    // PINTU TARIK DATA: Semua resep untuk dropdown form Perencanaan Menu
    // =============================================
    public function dropdown(): JsonResponse
    {
        $recipes = $this->recipeService->getAllForDropdown();

        return response()->json([
            'success' => true,
            'data'    => $recipes,
        ]);
    }

    // =============================================
    // GET /api/recipes/{id}
    // PINTU TARIK DATA: Detail resep + semua bahannya
    // =============================================
    public function show(int $id): JsonResponse
    {
        try {
            $recipe = $this->recipeService->findById($id);

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

    // =============================================
    // POST /api/recipes
    // PINTU MASUK DATA: Simpan resep baru + kalkulasi nutrisi otomatis
    // Body JSON yang diharapkan:
    // {
    //   "name": "Ayam Bakar Keto",
    //   "target_calorie": 3200,
    //   "ingredients": [
    //     { "ingredient_id": 1, "weight_used": 500 },
    //     { "ingredient_id": 2, "weight_used": 200 }
    //   ]
    // }
    // =============================================
    public function store(RecipeRequest $request): JsonResponse
    {
        try {
            $recipe = $this->recipeService->create($request->validated());

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

    // =============================================
    // PUT /api/recipes/{id}
    // PINTU MASUK DATA: Update resep + recalculate nutrisi
    // =============================================
    public function update(RecipeRequest $request, int $id): JsonResponse
    {
        try {
            $recipe = $this->recipeService->update($id, $request->validated());

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

    // =============================================
    // DELETE /api/recipes/{id}
    // =============================================
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->recipeService->delete($id);

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