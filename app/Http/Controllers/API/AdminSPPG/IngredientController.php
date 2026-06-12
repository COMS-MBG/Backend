<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nutrition\IngredientRequest;
use App\Http\Resources\IngredientResource;
use App\Services\SPPG\IngredientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; 
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * CONTROLLER untuk Fitur Master Data Bahan Baku (Ingredient).
 */
class IngredientController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly IngredientService $ingredientService
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:ingredients.read', only: ['index', 'show', 'dropdown', 'calculateNutrition']),
            new Middleware('permission:ingredients.create', only: ['store']),
            new Middleware('permission:ingredients.update', only: ['update']),
            new Middleware('permission:ingredients.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        $filters = $request->only(['search', 'per_page']);
        $ingredients = $this->ingredientService->getAll($sppgId, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Daftar bahan baku berhasil diambil.',
            'data'    => IngredientResource::collection($ingredients->items()),
            'meta' => [
                'current_page' => $ingredients->currentPage(),
                'last_page'    => $ingredients->lastPage(),
                'per_page'     => $ingredients->perPage(),
                'total'        => $ingredients->total(),
            ],
        ]);
    }

    public function dropdown(Request $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        $ingredients = $this->ingredientService->getAllForDropdown($sppgId);

        return response()->json([
            'success' => true,
            'data'    => IngredientResource::collection($ingredients),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $ingredient = $this->ingredientService->findById($sppgId, $id);

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

    public function store(IngredientRequest $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        $ingredient = $this->ingredientService->create($sppgId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Bahan baku berhasil ditambahkan.',
            'data'    => new IngredientResource($ingredient),
        ], 201);
    }

    public function update(IngredientRequest $request, int $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $ingredient = $this->ingredientService->update($sppgId, $id, $request->validated());

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

    public function destroy(Request $request, int $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $this->ingredientService->delete($sppgId, $id);

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

    public function calculateNutrition(Request $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        $request->validate([
            'ingredient_id' => 'required|integer',
            'weight'        => 'required|numeric|min:0.1',
        ]);

        try {
            $result = $this->ingredientService->calculateNutritionPreview(
                $sppgId,
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
                'message' => 'Bahan baku tidak ditemukan atau error kalkulasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}