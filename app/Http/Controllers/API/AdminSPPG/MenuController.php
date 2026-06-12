<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nutrition\MenuRequest;
use App\Http\Resources\MenuResource;
use App\Services\SPPG\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(private readonly MenuService $menuService) {}

    public function index(Request $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        $filters = $request->only(['search', 'status', 'per_page']);
        $menus   = $this->menuService->getAll($sppgId, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Menu planning list fetched successfully.',
            'data'    => MenuResource::collection($menus->items()),
            'meta'    => [
                'current_page' => $menus->currentPage(),
                'last_page'    => $menus->lastPage(),
                'per_page'     => $menus->perPage(),
                'total'        => $menus->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $menu = $this->menuService->findByIdForSppg($sppgId, $id);

            return response()->json([
                'success' => true,
                'data'    => new MenuResource($menu),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Perencanaan menu tidak ditemukan.',
            ], 404);
        }
    }

    public function showGrouped(Request $request, int $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $result = $this->menuService->getMenuGroupedByDay($sppgId, $id);

            return response()->json([
                'success' => true,
                'data'    => [
                    'menu' => new MenuResource($result['menu']),
                    'days' => $result['days'],
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Perencanaan menu tidak ditemukan.',
            ], 404);
        }
    }

    public function store(MenuRequest $request): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $menu = $this->menuService->create($sppgId, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Perencanaan menu berhasil dibuat.',
                'data'    => new MenuResource($menu),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat perencanaan menu: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(MenuRequest $request, int $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $menu = $this->menuService->update($sppgId, $id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Perencanaan menu berhasil diperbarui.',
                'data'    => new MenuResource($menu),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Perencanaan menu tidak ditemukan.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui menu: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        try {
            $this->menuService->delete($sppgId, $id);

            return response()->json([
                'success' => true,
                'message' => 'Perencanaan menu berhasil dihapus.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Perencanaan menu tidak ditemukan.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function publish(Request $request, int $id, \App\Services\Stock\StockService $stockService): JsonResponse
    {
        $sppgId = $request->attributes->get('sppg_id');
        $userId = $request->user()->id;

        try {
            $menu = $this->menuService->findByIdForSppg($sppgId, $id);

            if (in_array($menu->status, ['published', 'archived'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu ini sudah dipublikasikan atau diarsipkan.',
                ], 422);
            }

            $stockService->deductStockForMenu($sppgId, $id, $userId);
            
            $publishedMenu = $this->menuService->publish($sppgId, $id);

            return response()->json([
                'success' => true,
                'message' => 'Perencanaan menu berhasil dipublikasikan.',
                'data'    => new MenuResource($publishedMenu),
            ]);
        } catch (\App\Exceptions\StockShortageException $e) {
            throw $e;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Perencanaan menu tidak ditemukan.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mempublikasikan menu: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function refreshStatuses(): JsonResponse
    {
        // Tetap menggunakan helper Service jika perlu
        $updated = $this->menuService->refreshAllStatuses();

        return response()->json([
            'success' => true,
            'message' => "{$updated} menu berhasil diperbarui statusnya.",
        ]);
    }
}