<?php

namespace App\Services\Stock;

use App\Models\StockItem;
use App\Models\StockMinimum;
use App\Models\StockTransaction;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Partner;
use App\Exceptions\StockShortageException;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Get aggregate summary of stocks for an SPPG.
     */
    public function getSummary(int|string $sppgId): array
    {
        $ingredientIds = \App\Models\StockMinimum::where('sppg_id', $sppgId)->pluck('ingredient_id')
            ->merge(\App\Models\StockItem::where('sppg_id', $sppgId)->pluck('ingredient_id'))
            ->unique();

        $ingredients = Ingredient::whereIn('id', $ingredientIds)->get();
        
        $stockItems = StockItem::where('sppg_id', $sppgId)
            ->whereIn('status', ['available', 'low', 'expired'])
            ->get()
            ->groupBy('ingredient_id');

        $minimums = StockMinimum::where('sppg_id', $sppgId)
            ->get()
            ->keyBy('ingredient_id');

        $summary = [];

        foreach ($ingredients as $ing) {
            $batches = $stockItems->get($ing->id) ?? collect();
            $minSetting = $minimums->get($ing->id);

            // Filter active available/low batches
            $activeBatches = $batches->filter(function ($b) {
                $isExpired = $b->expiry_date && $b->expiry_date->isPast();
                return $b->status !== 'expired' && !$isExpired && $b->quantity > 0;
            });
            
            $totalQty = (float) $activeBatches->sum('quantity');
            $batchCount = $activeBatches->count();

            $unit = $minSetting?->unit ?? ($batches->first()?->unit ?? 'kg');
            $minimumQty = $minSetting ? (float) $minSetting->minimum_quantity : 0.0;

            // Check if there are expired batches
            $hasExpired = $batches->contains(function ($b) {
                return $b->status === 'expired' || ($b->expiry_date && $b->expiry_date->isPast());
            });

            $status = 'available';
            if ($totalQty <= 0) {
                $status = 'empty';
            } elseif ($totalQty < $minimumQty) {
                $status = 'low';
            }

            $summary[] = [
                'ingredient_id' => $ing->id,
                'ingredient_name' => $ing->name,
                'total_quantity' => round($totalQty, 3),
                'unit' => $unit,
                'minimum_quantity' => $minimumQty,
                'status' => $status,
                'has_expired' => $hasExpired,
                'batch_count' => $batchCount,
            ];
        }

        return $summary;
    }

    /**
     * Check if available stocks meet the requirements of a weekly menu.
     */
    public function checkMenuRequirements(int|string $sppgId, int|string $menuId): array
    {
        $menu = Menu::with('menuItems.recipe.recipeIngredients.ingredient')->findOrFail($menuId);
        
        // Sum total portions of partners registered to this SPPG
        $portionCount = (int) Partner::where('sppg_id', $sppgId)->sum('portion_count');

        // Accumulate needed ingredients in grams
        $neededGrams = [];
        $ingredientNames = [];

        foreach ($menu->menuItems as $item) {
            if (!$item->recipe) continue;

            foreach ($item->recipe->recipeIngredients as $ri) {
                $ingId = $ri->ingredient_id;
                $ingredientNames[$ingId] = $ri->ingredient?->name ?? 'Bahan Baku';
                
                $neededGrams[$ingId] = ($neededGrams[$ingId] ?? 0.0) + ($ri->weight_used * $portionCount);
            }
        }

        $shortages = [];

        foreach ($neededGrams as $ingId => $totalGrams) {
            $stockUnit = $this->getIngredientStockUnit($sppgId, $ingId);
            $neededInStockUnit = $this->convertGramsToUnit($totalGrams, $stockUnit);

            // Fetch available quantity (exclude expired and pending, quantity > 0)
            $availableStock = (float) StockItem::where('sppg_id', $sppgId)
                ->where('ingredient_id', $ingId)
                ->whereIn('status', ['available', 'low'])
                ->where(function($q) {
                    $q->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>=', now()->toDateString());
                })
                ->sum('quantity');

            if ($availableStock < $neededInStockUnit) {
                $shortage = $neededInStockUnit - $availableStock;
                $shortages[] = [
                    'ingredient_id' => $ingId,
                    'ingredient_name' => $ingredientNames[$ingId],
                    'needed' => round($neededInStockUnit, 3),
                    'available' => round($availableStock, 3),
                    'unit' => $stockUnit,
                    'shortage' => round($shortage, 3),
                ];
            }
        }

        return $shortages;
    }

    /**
     * Deduct stock for a menu using FIFO logic.
     */
    public function deductStockForMenu(int|string $sppgId, int|string $menuId, int|string $userId): void
    {
        $shortages = $this->checkMenuRequirements($sppgId, $menuId);
        if (!empty($shortages)) {
            throw new StockShortageException($shortages);
        }

        $menu = Menu::with('menuItems.recipe.recipeIngredients.ingredient')->findOrFail($menuId);
        $portionCount = (int) Partner::where('sppg_id', $sppgId)->sum('portion_count');

        // Accumulate needed ingredients in grams
        $neededGrams = [];
        foreach ($menu->menuItems as $item) {
            if (!$item->recipe) continue;

            foreach ($item->recipe->recipeIngredients as $ri) {
                $ingId = $ri->ingredient_id;
                $neededGrams[$ingId] = ($neededGrams[$ingId] ?? 0.0) + ($ri->weight_used * $portionCount);
            }
        }

        DB::transaction(function () use ($sppgId, $menuId, $userId, $neededGrams) {
            foreach ($neededGrams as $ingId => $totalGrams) {
                if ($totalGrams <= 0) continue;

                $stockUnit = $this->getIngredientStockUnit($sppgId, $ingId);
                $remainingNeeded = $this->convertGramsToUnit($totalGrams, $stockUnit);

                // Fetch available batches of this ingredient, ordered by purchase date (FIFO)
                $batches = StockItem::where('sppg_id', $sppgId)
                    ->where('ingredient_id', $ingId)
                    ->whereIn('status', ['available', 'low'])
                    ->where(function($q) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>=', now()->toDateString());
                    })
                    ->orderBy('purchase_date', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($remainingNeeded <= 0) break;

                    $qtyBefore = (float) $batch->quantity;
                    
                    if ($qtyBefore >= $remainingNeeded) {
                        $qtyAfter = $qtyBefore - $remainingNeeded;
                        $deducted = $remainingNeeded;
                        $remainingNeeded = 0.0;
                    } else {
                        $qtyAfter = 0.0;
                        $deducted = $qtyBefore;
                        $remainingNeeded -= $qtyBefore;
                    }

                    // Update batch
                    $batch->quantity = $qtyAfter;
                    if ($qtyAfter <= 0) {
                        $batch->status = 'empty';
                    }
                    $batch->save();

                    // Log transaction
                    StockTransaction::create([
                        'sppg_id' => $sppgId,
                        'stock_item_id' => $batch->id,
                        'ingredient_id' => $ingId,
                        'transaction_type' => 'out',
                        'quantity' => $deducted,
                        'quantity_before' => $qtyBefore,
                        'quantity_after' => $qtyAfter,
                        'reference_type' => 'menu_publish',
                        'reference_id' => $menuId,
                        'notes' => 'FIFO deduction for menu publish: ' . $menu->name,
                        'created_by' => $userId,
                    ]);
                }

                // Update aggregate batch statuses for this ingredient
                $this->updateBatchStatuses($sppgId, $ingId);
            }
        });
    }

    /**
     * Update statuses of active batches for an ingredient based on aggregate minimum stock.
     */
    public function updateBatchStatuses(int|string $sppgId, int|string $ingredientId): void
    {
        $minSetting = StockMinimum::where('sppg_id', $sppgId)->where('ingredient_id', $ingredientId)->first();
        $minimumQty = $minSetting ? (float) $minSetting->minimum_quantity : 0.0;

        // Fetch available/low batches
        $batches = StockItem::where('sppg_id', $sppgId)
            ->where('ingredient_id', $ingredientId)
            ->whereIn('status', ['available', 'low'])
            ->get();

        $totalQty = (float) $batches->sum('quantity');

        $newStatus = 'available';
        if ($totalQty <= 0) {
            $newStatus = 'empty';
        } elseif ($totalQty < $minimumQty) {
            $newStatus = 'low';
        }

        foreach ($batches as $batch) {
            if ($batch->quantity <= 0) {
                $batch->status = 'empty';
            } else {
                $batch->status = $newStatus;
            }
            $batch->save();
        }
    }

    /**
     * Get target stock unit for an ingredient.
     */
    public function getIngredientStockUnit(int|string $sppgId, int|string $ingredientId): string
    {
        $min = StockMinimum::where('sppg_id', $sppgId)->where('ingredient_id', $ingredientId)->first();
        if ($min) {
            return $min->unit;
        }
        
        $item = StockItem::where('sppg_id', $sppgId)->where('ingredient_id', $ingredientId)->first();
        if ($item) {
            return $item->unit;
        }

        return 'kg'; // Default
    }

    /**
     * Convert grams to target unit.
     */
    public function convertGramsToUnit(float $grams, string $targetUnit): float
    {
        $unit = strtolower($targetUnit);
        if ($unit === 'kg' || $unit === 'liter') {
            return $grams / 1000.0;
        }
        return $grams;
    }
}
