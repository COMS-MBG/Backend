<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nutrition\IngredientRequest;
use App\Http\Resources\IngredientResource;
use App\Services\SPPG\IngredientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; 

/**
 * CONTROLLER untuk Fitur Master Data Bahan Baku (Ingredient).
 * Analogi NestJS: IngredientController (*.controller.ts)
 *
 * Controller hanya bertugas:
 * 1. Nerima request
 * 2. Panggil Service
 * 3. Return response JSON
 *
 * SEMUA LOGIKA BISNIS ada di IngredientService, bukan di sini.
 */
class IngredientController extends Controller
{
    public function __construct(
        private readonly IngredientService $ingredientService
        // Analogi NestJS: constructor(private ingredientService: IngredientService) {}
    ) {}

    // =============================================
    // GET /api/ingredients
    // PINTU TARIK DATA: Ambil semua bahan baku
    // =============================================
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'per_page']);
        $ingredients = $this->ingredientService->getAll($filters);

        return response()->json([
            'success' => true,
            'message' => 'Daftar bahan baku berhasil diambil.',
            'data'    => IngredientResource::collection($ingredients->items()),
            // Info paginasi
            'meta' => [
                'current_page' => $ingredients->currentPage(),
                'last_page'    => $ingredients->lastPage(),
                'per_page'     => $ingredients->perPage(),
                'total'        => $ingredients->total(),
            ],
        ]);
    }

    // =============================================
    // GET /api/ingredients/dropdown
    // PINTU TARIK DATA: Daftar semua bahan untuk dropdown form resep
    // =============================================
    public function dropdown(): JsonResponse
    {
        $ingredients = $this->ingredientService->getAllForDropdown();

        return response()->json([
            'success' => true,
            'data'    => IngredientResource::collection($ingredients),
        ]);
    }

    // =============================================
    // GET /api/ingredients/{id}
    // PINTU TARIK DATA: Detail satu bahan baku
    // =============================================
    public function show(int $id): JsonResponse
    {
        try {
            $ingredient = $this->ingredientService->findById($id);

            return response()->json([
                'success' => true,
                'data'    => new IngredientResource($ingredient),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan.',
            ], 404);
        }
    }

    // =============================================
    // POST /api/ingredients
    // PINTU MASUK DATA: Simpan bahan baku baru
    // =============================================
    public function store(IngredientRequest $request): JsonResponse
    {
        // $request->validated() = data yang sudah lolos validasi
        $ingredient = $this->ingredientService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Bahan baku berhasil ditambahkan.',
            'data'    => new IngredientResource($ingredient),
        ], 201); // 201 = Created
    }

    // =============================================
    // PUT /api/ingredients/{id}
    // PINTU MASUK DATA: Update bahan baku
    // =============================================
    public function update(IngredientRequest $request, int $id): JsonResponse
    {
        try {
            $ingredient = $this->ingredientService->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Bahan baku berhasil diperbarui.',
                'data'    => new IngredientResource($ingredient),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bahan baku tidak ditemukan.',
            ], 404);
        }
    }

    // =============================================
    // DELETE /api/ingredients/{id}
    // Hapus bahan baku
    // =============================================
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->ingredientService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Bahan baku berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 409 ? 409 : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    // =============================================
    // POST /api/ingredients/calculate-nutrition
    // Preview kalkulasi nutrisi tanpa save (untuk live preview di FE)
    // FE memanggil ini setiap user ketik berat di form resep
    // =============================================
    public function calculateNutrition(Request $request): JsonResponse
    {
        $request->validate([
            'ingredient_id' => 'required|integer|exists:ingredients,id',
            'weight'        => 'required|numeric|min:0.1',
        ]);

        try {
            $result = $this->ingredientService->calculateNutritionPreview(
                $request->ingredient_id,
                $request->weight
            );

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}