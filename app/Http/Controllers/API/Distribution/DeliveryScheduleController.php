<?php

namespace App\Http\Controllers\Api\Distribution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distribution\ConfirmDeliveryRequest;
use App\Http\Requests\Distribution\RejectDeliveryRequest;
use App\Http\Requests\Distribution\StoreDeliveryScheduleRequest;
use App\Http\Requests\Distribution\SubmitDeliveryProofRequest;
use App\Http\Requests\Distribution\UpdateDeliveryScheduleRequest;
use App\Http\Resources\Distribution\DeliveryHistoryResource;
use App\Http\Resources\Distribution\DeliveryScheduleResource;
use App\Models\DeliverySchedule;
use App\Services\Distribution\DeliveryScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ============================================================
 *  DELIVERY SCHEDULE CONTROLLER
 * ============================================================
 *
 * "PINTU MASUK"  = POST/PUT routes below  (data dari FE → DB)
 * "PINTU KELUAR" = GET routes below       (data dari DB → FE)
 *
 * Base path: /api/distribution/schedules
 * ============================================================
 */
class DeliveryScheduleController extends Controller
{
    public function __construct(private readonly DeliveryScheduleService $service)
    {
    }

    // ─── [GET] List all active schedules ─────────────────────────────────────
    // PINTU KELUAR – FE list view
    public function index(Request $request): JsonResponse
    {
        $query = DeliverySchedule::active()
            ->with(['courier', 'school', 'assignedBy', 'submittedBy', 'latestLocation'])
            ->latest();

        // Filter by courier (courier role sees only their own)
        if ($request->user()->hasRole('courier')) {
            $query->forCourier($request->user()->employee?->id ?? 0);
        }

        // Optional filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => DeliveryScheduleResource::collection($schedules),
            'meta'    => [
                'current_page' => $schedules->currentPage(),
                'last_page'    => $schedules->lastPage(),
                'total'        => $schedules->total(),
            ],
        ]);
    }

    // ─── [GET] Single schedule detail ────────────────────────────────────────
    // PINTU KELUAR
    public function show(DeliverySchedule $schedule): JsonResponse
    {
        $schedule->load(['courier', 'school', 'assignedBy', 'submittedBy', 'confirmedBy', 'latestLocation']);

        return response()->json([
            'success' => true,
            'data'    => new DeliveryScheduleResource($schedule),
        ]);
    }

    // ─── [POST] Admin Logistik creates a schedule ─────────────────────────────
    // PINTU MASUK
    public function store(StoreDeliveryScheduleRequest $request): JsonResponse
    {
        $schedule = $this->service->createSchedule($request->validated(), $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Delivery schedule created successfully.',
            'data'    => new DeliveryScheduleResource($schedule),
        ], 201);
    }

    // ─── [PUT] Admin Logistik updates draft schedule ──────────────────────────
    // PINTU MASUK
    public function update(UpdateDeliveryScheduleRequest $request, DeliverySchedule $schedule): JsonResponse
    {
        $schedule = $this->service->updateSchedule($schedule, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully.',
            'data'    => new DeliveryScheduleResource($schedule),
        ]);
    }

    // ─── [DELETE] Admin Logistik deletes an in_order schedule ─────────────────
    public function destroy(Request $request, DeliverySchedule $schedule): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin_logistik', 'super_admin']), 403);
        abort_unless($schedule->status === DeliverySchedule::STATUS_IN_ORDER, 422, 'Only in_order schedules can be deleted.');

        $schedule->delete();

        return response()->json(['success' => true, 'message' => 'Schedule deleted.']);
    }

    // ═══════════════════════════════════════════════════════════════
    //  WORKFLOW ACTIONS
    // ═══════════════════════════════════════════════════════════════

    // ─── [POST] Admin SPPG submits task to courier ────────────────────────────
    // PINTU MASUK → triggers Reverb broadcast to courier
    public function submitTask(Request $request, DeliverySchedule $schedule): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin_sppg', 'super_admin']), 403);

        $schedule = $this->service->submitTask($schedule, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Task submitted to courier.',
            'data'    => new DeliveryScheduleResource($schedule),
        ]);
    }

    // ─── [POST] Courier accepts task ─────────────────────────────────────────
    // PINTU MASUK
    public function acceptTask(Request $request, DeliverySchedule $schedule): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['courier', 'super_admin']), 403);

        $schedule = $this->service->acceptTask($schedule);

        return response()->json([
            'success' => true,
            'message' => 'Task accepted. Start delivering.',
            'data'    => new DeliveryScheduleResource($schedule),
        ]);
    }

    // ─── [POST] Courier rejects task + reason + optional photo ───────────────
    // PINTU MASUK
    public function rejectTask(RejectDeliveryRequest $request, DeliverySchedule $schedule): JsonResponse
    {
        $schedule = $this->service->rejectTask(
            $schedule,
            $request->rejection_reason,
            $request->file('rejection_photo')
        );

        return response()->json([
            'success' => true,
            'message' => 'Task rejected.',
            'data'    => new DeliveryScheduleResource($schedule),
        ]);
    }

    // ─── [POST] Courier submits delivery proof photo ─────────────────────────
    // PINTU MASUK
    public function submitProof(SubmitDeliveryProofRequest $request, DeliverySchedule $schedule): JsonResponse
    {
        $schedule = $this->service->submitDeliveryProof($schedule, $request->file('proof_photo'));

        return response()->json([
            'success' => true,
            'message' => 'Delivery proof submitted. Awaiting admin confirmation.',
            'data'    => new DeliveryScheduleResource($schedule),
        ]);
    }

    // ─── [POST] Courier resubmits proof after revision request ───────────────
    // PINTU MASUK
    public function resubmitProof(SubmitDeliveryProofRequest $request, DeliverySchedule $schedule): JsonResponse
    {
        $schedule = $this->service->resubmitProof($schedule, $request->file('proof_photo'));

        return response()->json([
            'success' => true,
            'message' => 'Proof resubmitted successfully.',
            'data'    => new DeliveryScheduleResource($schedule),
        ]);
    }

    // ─── [POST] Admin Logistik confirms delivery ──────────────────────────────
    // PINTU MASUK → archives to delivery_histories
    public function confirmDelivery(ConfirmDeliveryRequest $request, DeliverySchedule $schedule): JsonResponse
    {
        $history = $this->service->confirmDelivery(
            $schedule,
            $request->user()->id,
            $request->notes ?? ''
        );

        return response()->json([
            'success' => true,
            'message' => 'Delivery confirmed and archived to history.',
            'data'    => new DeliveryHistoryResource($history),
        ]);
    }

    // ─── [POST] Admin Logistik requests revision ──────────────────────────────
    // PINTU MASUK → sends notification to courier
    public function requestRevision(Request $request, DeliverySchedule $schedule): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin_logistik', 'super_admin']), 403);

        $request->validate(['notes' => 'required|string|min:5|max:500']);

        $schedule = $this->service->requestRevision($schedule, $request->user()->id, $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Revision requested. Courier will be notified.',
            'data'    => new DeliveryScheduleResource($schedule),
        ]);
    }

    // ─── [GET] Available couriers (employees) for assignment ─────────────────
    // PINTU KELUAR – used by admin logistik dropdown
    public function availableCouriers(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin_logistik', 'super_admin', 'admin_sppg']), 403);

        // Fetch from Employee model – adjust column names to your actual schema
        $couriers = \App\Models\Employee::query()
            ->where('role', 'courier')
            ->orWhereHas('user', fn($q) => $q->role('courier'))
            ->select(['id', 'name', 'full_name', 'employee_number'])
            ->orderBy('name')
            ->get()
            ->map(fn($e) => [
                'id'   => $e->id,
                'name' => $e->full_name ?? $e->name,
            ]);

        return response()->json(['success' => true, 'data' => $couriers]);
    }
}