<?php

namespace App\Services\SPPG;

use App\Models\Menu;
use App\Models\MenuItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * SERVICE untuk fitur Perencanaan Menu.
 * Berisi logika: simpan menu, kalkulasi status, organisir per hari.
 */
class MenuService
{
    /**
     * Ambil semua perencanaan menu
     *
     * PINTU TARIK DATA: GET /api/menus
     */
    public function getAll(array $filters = [])
    {
        $query = Menu::with(['menuItems.recipe']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->latest('week_start')->paginate($perPage);
    }

    /**
     * Ambil detail satu menu beserta semua item harinya
     *
     * PINTU TARIK DATA: GET /api/menus/{id}
     */
    public function findById(int $id): Menu
    {
        $menu = Menu::with([
            'menuItems' => function ($q) {
                $q->with('recipe')->orderBy('day_of_week')->orderBy('order');
            }
        ])->findOrFail($id);

        // Refresh status berdasarkan tanggal hari ini
        $this->refreshStatus($menu);

        return $menu;
    }

    /**
     * Buat perencanaan menu baru.
     *
     * PINTU MASUK DATA: POST /api/menus
     *
     * Format $data:
     * [
     *   'name'       => 'Menu Minggu ke-15',
     *   'week_start' => '2025-04-07',   ← harus Senin
     *   'week_end'   => '2025-04-10',   ← harus Kamis
     *   'notes'      => '...',
     *   'items' => [
     *     [
     *       'day_of_week' => 1,            ← 1=Senin
     *       'menu_date'   => '2025-04-07',
     *       'meal_time'   => 'lunch',
     *       'recipe_id'   => 5,
     *       'order'       => 1,
     *     ],
     *     [
     *       'day_of_week' => 1,
     *       'menu_date'   => '2025-04-07',
     *       'meal_time'   => 'dinner',
     *       'recipe_id'   => 3,
     *       'order'       => 2,
     *     ],
     *     // dst untuk hari Selasa, Rabu, Kamis
     *   ]
     * ]
     */
    public function create(array $data): Menu
{
    return DB::transaction(function () use ($data) {

        $status = Menu::computeStatus($data['week_start']);

        $menu = Menu::create([
            'name'       => $data['name'],
            'week_start' => $data['week_start'],
            'week_end'   => $data['week_end'],
            'status'     => $status,
            'notes'      => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            MenuItem::create([
                'menu_id'     => $menu->id,
                'recipe_id'   => $item['recipe_id'],
                'day_of_week' => $item['day_of_week'],
                'menu_date'   => $item['menu_date'],
                'order'       => $item['order'] ?? 0,
            ]);
        }

        return $menu->load('menuItems.recipe');
    });
}
    /**
     * Update perencanaan menu
     *
     * PINTU MASUK DATA: PUT /api/menus/{id}
     */
    public function update(int $id, array $data): Menu
    {
        return DB::transaction(function () use ($id, $data) {
            $menu = Menu::findOrFail($id);

            $status = Menu::computeStatus($data['week_start'] ?? $menu->week_start);

            $menu->update([
                'name'       => $data['name'] ?? $menu->name,
                'week_start' => $data['week_start'] ?? $menu->week_start,
                'week_end'   => $data['week_end'] ?? $menu->week_end,
                'status'     => $status,
                'notes'      => $data['notes'] ?? $menu->notes,
            ]);

            // Jika ada items baru, hapus yang lama dan ganti
            if (isset($data['items'])) {
                $menu->menuItems()->delete();
                $this->saveMenuItems($menu, $data['items']);
            }

            return $menu->load('menuItems.recipe');
        });
    }

    /**
     * Hapus perencanaan menu
     */
    public function delete(int $id): bool
    {
        $menu = Menu::findOrFail($id);
        $menu->menuItems()->delete(); // Hapus items dulu
        return $menu->delete();
    }

    /**
     * Perbarui status semua menu (bisa dijadwalkan sebagai CRON job harian)
     * Dipanggil: php artisan schedule:run (jika disetup di Kernel.php)
     */
    public function refreshAllStatuses(): int
    {
        $menus   = Menu::whereNull('deleted_at')->get();
        $updated = 0;

        foreach ($menus as $menu) {
            $newStatus = Menu::computeStatus($menu->week_start);
            if ($menu->status !== $newStatus) {
                $menu->update(['status' => $newStatus]);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Ambil data menu yang dikelompokkan per hari untuk response FE
     * Output: ['monday' => [...items], 'tuesday' => [...], ...]
     */
    public function getMenuGroupedByDay(int $menuId): array
    {
        $menu = $this->findById($menuId);

        $grouped = [
            1 => ['label' => 'Senin',  'date' => null, 'items' => []],
            2 => ['label' => 'Selasa', 'date' => null, 'items' => []],
            3 => ['label' => 'Rabu',   'date' => null, 'items' => []],
            4 => ['label' => 'Kamis',  'date' => null, 'items' => []],
        ];

        foreach ($menu->menuItems as $item) {
            $day = $item->day_of_week;
            if (isset($grouped[$day])) {
                $grouped[$day]['date']    = $item->menu_date->format('Y-m-d');
                $grouped[$day]['items'][] = [
                    'id'              => $item->id,
                    'meal_time'       => $item->meal_time,
                    'meal_time_label' => $item->meal_time_label,
                    'order'           => $item->order,
                    'recipe'          => $item->recipe ? [
                        'id'            => $item->recipe->id,
                        'name'          => $item->recipe->name,
                        'total_calorie' => $item->recipe->total_calorie,
                        'total_protein' => $item->recipe->total_protein,
                    ] : null,
                ];
            }
        }

        return [
            'menu'    => $menu,
            'days'    => $grouped,
        ];
    }

    // =============================================
    // PRIVATE HELPERS
    // =============================================

    private function saveMenuItems(Menu $menu, array $items): void
    {
        foreach ($items as $item) {
            MenuItem::create([
                'menu_id'     => $menu->id,
                'recipe_id'   => $item['recipe_id'],
                'day_of_week' => $item['day_of_week'],
                'menu_date'   => $item['menu_date'],
                'meal_time' => null,
                'order'       => $item['order'] ?? 0,
            ]);
        }
    }

    private function refreshStatus(Menu $menu): void
    {
        $newStatus = Menu::computeStatus($menu->week_start);
        if ($menu->status !== $newStatus) {
            $menu->update(['status' => $newStatus]);
            $menu->status = $newStatus;
        }
    }
}