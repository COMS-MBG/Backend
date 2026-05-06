<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nutrition\MenuRequest;
use App\Http\Resources\MenuResource;
use App\Services\SPPG\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CONTROLLER untuk Fitur Perencanaan Menu.
 */
class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $menuService
    ) {}

    // =============================================
    // GET /api/menus
    // PINTU TARIK DATA: Semua perencanaan menu
    // Query params: ?status=published&search=minggu&per_page=10
    // =============================================
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'per_page']);
        $menus   = $this->menuService->getAll($filters);

        return response()->json([
            'success' => true,
            'message' => 'Daftar perencanaan menu berhasil diambil.',
            'data'    => MenuResource::collection($menus->items()),
            'meta'    => [
                'current_page' => $menus->currentPage(),
                'last_page'    => $menus->lastPage(),
                'per_page'     => $menus->perPage(),
                'total'        => $menus->total(),
            ],
        ]);
    }

    // =============================================
    // GET /api/menus/{id}
    // PINTU TARIK DATA: Detail menu + semua resep per harinya
    // =============================================
    public function show(int $id): JsonResponse
    {
        try {
            $menu = $this->menuService->findById($id);

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

    // =============================================
    // GET /api/menus/{id}/grouped
    // PINTU TARIK DATA: Menu dikelompokkan per hari (untuk tampilan kalender FE)
    // =============================================
    public function showGrouped(int $id): JsonResponse
    {
        try {
            $result = $this->menuService->getMenuGroupedByDay($id);

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

    // =============================================
    // POST /api/menus
    // PINTU MASUK DATA: Buat perencanaan menu baru
    // Body JSON yang diharapkan:
    // {
    //   "name": "Menu Minggu ke-15",
    //   "week_start": "2025-04-07",
    //   "week_end": "2025-04-10",
    //   "items": [
    //     { "day_of_week": 1, "menu_date": "2025-04-07", "recipe_id": 1, "meal_time": "lunch" },
    //     { "day_of_week": 1, "menu_date": "2025-04-07", "recipe_id": 2, "meal_time": "dinner" },
    //     { "day_of_week": 2, "menu_date": "2025-04-08", "recipe_id": 3, "meal_time": "lunch" }
    //   ]
    // }
    // =============================================
    public function store(MenuRequest $request): JsonResponse
    {
        try {
            $menu = $this->menuService->create($request->validated());

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

    // =============================================
    // PUT /api/menus/{id}
    // PINTU MASUK DATA: Update perencanaan menu
    // =============================================
    public function update(MenuRequest $request, int $id): JsonResponse
    {
        try {
            $menu = $this->menuService->update($id, $request->validated());

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

    // =============================================
    // DELETE /api/menus/{id}
    // =============================================
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->menuService->delete($id);

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

    // =============================================
    // POST /api/menus/refresh-statuses
    // Update status semua menu (bisa dijadikan endpoint admin atau CRON)
    // =============================================
    public function refreshStatuses(): JsonResponse
    {
        $updated = $this->menuService->refreshAllStatuses();

        return response()->json([
            'success' => true,
            'message' => "{$updated} menu berhasil diperbarui statusnya.",
        ]);
    }
}