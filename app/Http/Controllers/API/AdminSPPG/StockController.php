<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Models\StockItem;
use App\Models\StockMinimum;
use App\Models\StockTransaction;
use App\Models\Ingredient;
use App\Services\Stock\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class StockController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly StockService $stockService
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:stock.read', only: ['index', 'show', 'allTransactions', 'batchTransactions', 'checkMenu']),
            new Middleware('permission:stock.create', only: ['store']),
            new Middleware('permission:stock.update', only: ['update', 'updateMinimum']),
            new Middleware('permission:stock.delete', only: ['destroy']),
            new Middleware('permission:stock.approve', only: ['pendingApproval', 'approve', 'reject']),
        ];
    }

    /**
     * GET /api/admin-sppg/stocks
     * Ringkasan stok agregat per bahan baku
     */
    public function index(Request $request): JsonResponse
    {
        $sppgId = $request->user()->sppg_id;
        $summary = $this->stockService->getSummary($sppgId);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * GET /api/admin-sppg/stocks/{ingredient_id}
     * Detail + daftar batch per bahan baku
     */
    public function show(Request $request, int $ingredientId): JsonResponse
    {
        $sppgId = $request->user()->sppg_id;
        $ingredient = Ingredient::findOrFail($ingredientId);
        
        $batches = StockItem::where('sppg_id', $sppgId)
            ->where('ingredient_id', $ingredientId)
            ->orderBy('purchase_date', 'desc')
            ->get();

        $minimum = StockMinimum::where('sppg_id', $sppgId)
            ->where('ingredient_id', $ingredientId)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'ingredient' => [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                ],
                'minimum_quantity' => $minimum ? (float) $minimum->minimum_quantity : 0.0,
                'unit' => $minimum?->unit ?? ($batches->first()?->unit ?? 'kg'),
                'batches' => $batches,
            ],
        ]);
    }

    /**
     * POST /api/admin-sppg/stocks
     * Ajukan penambahan stok baru (status: pending)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0.001',
            'unit' => 'required|string|in:kg,liter,gram,ml,pcs',
            'price_per_unit' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:purchase_date',
            'supplier' => 'required|string|max:255',
            'storage_type' => 'required|string|in:dry,chilled,frozen',
            'storage_location' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'proof_document' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:2048',
        ]);

        $sppgId = $request->user()->sppg_id;
        $userId = $request->user()->id;

        $proofDocumentPath = null;
        if ($request->hasFile('proof_document')) {
            $proofDocumentPath = $request->file('proof_document')->store('proof_documents', 'public');
        }

        $stockItem = StockItem::create([
            'sppg_id' => $sppgId,
            'ingredient_id' => $request->ingredient_id,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'price_per_unit' => $request->price_per_unit,
            'purchase_date' => $request->purchase_date,
            'expiry_date' => $request->expiry_date,
            'supplier' => $request->supplier,
            'storage_type' => $request->storage_type,
            'storage_location' => $request->storage_location,
            'sku' => $request->sku,
            'notes' => $request->notes,
            'status' => 'pending',
            'proof_document' => $proofDocumentPath,
            'created_by' => $userId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan penambahan stok berhasil dibuat.',
            'data' => $stockItem,
        ], 201);
    }

    /**
     * PUT /api/admin-sppg/stocks/{id}
     * Edit batch (hanya sebelum diapprove / status: pending)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $sppgId = $request->user()->sppg_id;
        $stockItem = StockItem::where('sppg_id', $sppgId)->findOrFail($id);

        if ($stockItem->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengajuan stok berstatus pending yang dapat diedit.',
            ], 422);
        }

        $request->validate([
            'quantity' => 'sometimes|required|numeric|min:0.001',
            'unit' => 'sometimes|required|string|in:kg,liter,gram,ml,pcs',
            'price_per_unit' => 'sometimes|required|numeric|min:0',
            'purchase_date' => 'sometimes|required|date',
            'expiry_date' => 'sometimes|required|date|after_or_equal:purchase_date',
            'supplier' => 'sometimes|required|string|max:255',
            'storage_type' => 'sometimes|required|string|in:dry,chilled,frozen',
            'storage_location' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'proof_document' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:2048',
        ]);

        $updateData = $request->only([
            'quantity', 'unit', 'price_per_unit', 'purchase_date', 'expiry_date',
            'supplier', 'storage_type', 'storage_location', 'sku', 'notes'
        ]);

        if ($request->hasFile('proof_document')) {
            $updateData['proof_document'] = $request->file('proof_document')->store('proof_documents', 'public');
        }

        $stockItem->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan penambahan stok berhasil diperbarui.',
            'data' => $stockItem,
        ]);
    }

    /**
     * DELETE /api/admin-sppg/stocks/{id}
     * Soft delete batch (hanya jika masih pending)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $sppgId = $request->user()->sppg_id;
        $stockItem = StockItem::where('sppg_id', $sppgId)->findOrFail($id);

        if ($stockItem->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengajuan stok berstatus pending yang dapat dihapus.',
            ], 422);
        }

        $stockItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan penambahan stok berhasil dihapus.',
        ]);
    }

    /**
     * GET /api/admin-sppg/stocks/pending
     * Daftar pengajuan pending
     */
    public function pendingApproval(Request $request): JsonResponse
    {
        $sppgId = $request->user()->sppg_id;
        $pending = StockItem::with('ingredient', 'creator')
            ->where('sppg_id', $sppgId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pending,
        ]);
    }

    /**
     * POST /api/admin-sppg/stocks/{id}/approve
     * Approve -> status: available, generate batch_number
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $sppgId = $request->user()->sppg_id;
        $userId = $request->user()->id;
        
        $stockItem = StockItem::where('sppg_id', $sppgId)->findOrFail($id);

        if ($stockItem->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengajuan stok berstatus pending yang dapat diapprove.',
            ], 422);
        }

        DB::transaction(function () use ($sppgId, $userId, $stockItem) {
            $qtyBefore = (float) StockItem::where('sppg_id', $sppgId)
                ->where('ingredient_id', $stockItem->ingredient_id)
                ->whereIn('status', ['available', 'low'])
                ->sum('quantity');

            // Generate batch number format: BATCH-YYYYMMDD-XXX
            $dateStr = now()->format('Ymd');
            $prefix = "BATCH-{$dateStr}-";
            
            $lastBatch = StockItem::where('sppg_id', $sppgId)
                ->where('batch_number', 'like', "{$prefix}%")
                ->orderBy('batch_number', 'desc')
                ->first();

            $seq = 1;
            if ($lastBatch && preg_match('/-(\d+)$/', $lastBatch->batch_number, $matches)) {
                $seq = (int) $matches[1] + 1;
            }
            $batchNumber = $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);

            // Update stock item
            $stockItem->update([
                'batch_number' => $batchNumber,
                'status' => 'available', // will be adjusted by updateBatchStatuses
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            // Log transaction
            StockTransaction::create([
                'sppg_id' => $sppgId,
                'stock_item_id' => $stockItem->id,
                'ingredient_id' => $stockItem->ingredient_id,
                'transaction_type' => 'in',
                'quantity' => $stockItem->quantity,
                'quantity_before' => $qtyBefore,
                'quantity_after' => $qtyBefore + $stockItem->quantity,
                'reference_type' => 'purchase',
                'reference_id' => $stockItem->id,
                'notes' => 'Stock batch approval: ' . $batchNumber,
                'created_by' => $userId,
            ]);

            // Sync aggregate statuses for this ingredient
            $this->stockService->updateBatchStatuses($sppgId, $stockItem->ingredient_id);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan stok berhasil disetujui.',
            'data' => $stockItem->fresh(),
        ]);
    }

    /**
     * POST /api/admin-sppg/stocks/{id}/reject
     * Reject -> pengajuan ditolak
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $sppgId = $request->user()->sppg_id;
        $stockItem = StockItem::where('sppg_id', $sppgId)->findOrFail($id);

        if ($stockItem->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengajuan stok berstatus pending yang dapat ditolak.',
            ], 422);
        }

        $stockItem->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan stok berhasil ditolak.',
            'data' => $stockItem,
        ]);
    }

    /**
     * GET /api/admin-sppg/stocks/transactions
     * Semua riwayat transaksi SPPG
     */
    public function allTransactions(Request $request): JsonResponse
    {
        $sppgId = $request->user()->sppg_id;
        $transactions = StockTransaction::with('ingredient', 'stockItem', 'creator')
            ->where('sppg_id', $sppgId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin-sppg/stocks/{id}/transactions
     * Riwayat transaksi per batch
     */
    public function batchTransactions(Request $request, int $id): JsonResponse
    {
        $sppgId = $request->user()->sppg_id;
        // Verify batch ownership
        $stockItem = StockItem::where('sppg_id', $sppgId)->findOrFail($id);

        $transactions = StockTransaction::with('creator')
            ->where('stock_item_id', $stockItem->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * PUT /api/admin-sppg/stocks/minimum/{ingredient_id}
     * Set/update stok minimum per bahan baku
     */
    public function updateMinimum(Request $request, int $ingredientId): JsonResponse
    {
        $request->validate([
            'minimum_quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|in:kg,liter,gram,ml,pcs',
        ]);

        $sppgId = $request->user()->sppg_id;
        
        $minimum = StockMinimum::updateOrCreate(
            ['sppg_id' => $sppgId, 'ingredient_id' => $ingredientId],
            [
                'minimum_quantity' => $request->minimum_quantity,
                'unit' => $request->unit,
            ]
        );

        // Update active batch statuses for this ingredient
        $this->stockService->updateBatchStatuses($sppgId, $ingredientId);

        return response()->json([
            'success' => true,
            'message' => 'Stok minimum berhasil diperbarui.',
            'data' => $minimum,
        ]);
    }

    /**
     * GET /api/admin-sppg/stocks/check-menu/{menu_id}
     * Simulasi (non-blocking) untuk melihat kecukupan stok menu
     */
    public function checkMenu(Request $request, int $menuId): JsonResponse
    {
        $sppgId = $request->user()->sppg_id;
        $shortages = $this->stockService->checkMenuRequirements($sppgId, $menuId);

        return response()->json([
            'success' => true,
            'shortages' => $shortages,
            'sufficient' => empty($shortages),
        ]);
    }
}
