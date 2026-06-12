<?php

namespace App\Services\SPPG;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

class MenuService
{
    public function getAll(int $sppgId, array $filters = [])
    {
        $query = Menu::with(['menuItems.recipe'])->where('sppg_id', $sppgId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $perPage = $filters['per_page'] ?? 15;
        return $query->latest('week_start')->paginate($perPage);
    }

    public function findByIdForSppg(int $sppgId, int $id): Menu
    {
        $menu = Menu::with([
            'menuItems' => function ($q) {
                $q->with('recipe')->orderBy('day_of_week')->orderBy('order');
            }
        ])->where('sppg_id', $sppgId)->findOrFail($id);

        $this->refreshStatus($menu);
        return $menu;
    }

    public function create(int $sppgId, array $data): Menu
    {
        return DB::transaction(function () use ($sppgId, $data) {
            $status = Menu::computeStatus($data['week_start']);
            
            $menu = Menu::create([
                'sppg_id'    => $sppgId,
                'name'       => $data['name'],
                'week_start' => $data['week_start'],
                'week_end'   => $data['week_end'],
                'status'     => $status,
                'notes'      => $data['notes'] ?? null,
            ]);

            $this->saveMenuItems($menu, $data['items'], $sppgId);

            return $menu->load('menuItems.recipe');
        });
    }

    public function update(int $sppgId, int $id, array $data): Menu
    {
        return DB::transaction(function () use ($sppgId, $id, $data) {
            $menu = Menu::where('sppg_id', $sppgId)->findOrFail($id);
            $status = Menu::computeStatus($data['week_start'] ?? $menu->week_start);

            $menu->update([
                'name'       => $data['name'] ?? $menu->name,
                'week_start' => $data['week_start'] ?? $menu->week_start,
                'week_end'   => $data['week_end'] ?? $menu->week_end,
                'status'     => $status,
                'notes'      => $data['notes'] ?? $menu->notes,
            ]);

            if (isset($data['items'])) {
                $menu->menuItems()->delete();
                $this->saveMenuItems($menu, $data['items'], $sppgId);
            }

            return $menu->load('menuItems.recipe');
        });
    }

    public function delete(int $sppgId, int $id): bool
    {
        $menu = Menu::where('sppg_id', $sppgId)->findOrFail($id);
        $menu->menuItems()->delete();
        return $menu->delete();
    }

    public function getMenuGroupedByDay(int $sppgId, int $menuId): array
    {
        $menu = $this->findByIdForSppg($sppgId, $menuId);
        
        $grouped = [
            1 => ['label' => 'Senin',  'date' => null, 'items' => []],
            2 => ['label' => 'Selasa', 'date' => null, 'items' => []],
            3 => ['label' => 'Rabu',   'date' => null, 'items' => []],
            4 => ['label' => 'Kamis',  'date' => null, 'items' => []],
            5 => ['label' => 'Jumat',  'date' => null, 'items' => []],
            6 => ['label' => 'Sabtu',  'date' => null, 'items' => []],
            7 => ['label' => 'Minggu', 'date' => null, 'items' => []],
        ];

        foreach ($menu->menuItems as $item) {
            $day = $item->day_of_week;
            if (isset($grouped[$day])) {
                $grouped[$day]['date']    = $item->menu_date ? $item->menu_date->format('Y-m-d') : null;
                $grouped[$day]['items'][] = [
                    'id'              => $item->id,
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
            'menu' => $menu,
            'days' => $grouped,
        ];
    }

    public function publish(int $sppgId, int $id): Menu
    {
        $menu = $this->findByIdForSppg($sppgId, $id);
        $menu->update(['status' => 'published']);
        return $menu;
    }

    private function saveMenuItems(Menu $menu, array $items, int $sppgId): void
    {
        foreach ($items as $item) {
            Recipe::where('sppg_id', $sppgId)->findOrFail($item['recipe_id']);

            MenuItem::create([
                'menu_id'     => $menu->id,
                'recipe_id'   => $item['recipe_id'],
                'day_of_week' => $item['day_of_week'],
                'menu_date'   => $item['menu_date'],
                'order'       => $item['order'] ?? 0,
            ]);
        }
    }

    private function refreshStatus(Menu $menu): void
    {
        if (in_array($menu->status, ['published', 'archived'])) {
            return;
        }
        $newStatus = Menu::computeStatus($menu->week_start);
        if ($menu->status !== $newStatus) {
            $menu->update(['status' => $newStatus]);
            $menu->status = $newStatus;
        }
    }
}