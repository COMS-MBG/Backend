<?php

namespace App\Http\Controllers\API\Distribution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distribution\ConfirmDeliveryRequest;
use App\Http\Requests\Distribution\RejectDeliveryRequest;
use App\Http\Requests\Distribution\StoreDeliveryScheduleRequest;
use App\Http\Requests\Distribution\SubmitDeliveryProofRequest;
use App\Http\Requests\Distribution\UpdateDeliveryScheduleRequest;
use App\Http\Resources\Distribution\DeliveryHistoryResource;
use App\Http\Resources\Distribution\DeliveryScheduleResource;
use App\Models\DeliverySchedule;
use App\Models\Employee;
use App\Services\Distribution\DeliveryScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ============================================================
 *  DELIVERY SCHEDULE CONTROLLER
 * ============================================================
 *
 * "PINTU MASUK"  = POST/PUT routes (data dari FE → DB)
 * "PINTU KELUAR" = GET routes       (data dari DB → FE)
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

        // Kurir hanya melihat jadwal miliknya sendiri
        if ($request->user()->hasAnyRole(['courier'])) {
            $query->forCourier($request->user()->employee?->id ?? 0);
        }

        // Filter opsional by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter opsional by courier
        if ($request->filled('courier_id') && !$request->user()->hasAnyRole(['courier'])) {
            $query->where('courier_id', $request->courier_id);
        }

        // Filter opsional by school
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
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

    // ─── [POST] Admin Logistik membuat jadwal ─────────────────────────────────
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

    // ─── [PUT] Admin Logistik update jadwal draft ──────────────────────────────
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

    // ─── [DELETE] Admin Logistik hapus jadwal in_order ─────────────────────────
    public function destroy(Request $request, DeliverySchedule $schedule): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['logistics_admin', 'super_admin']), 403);
        abort_unless($schedule->status === DeliverySchedule::STATUS_IN_ORDER, 422, 'Only in_order schedules can be deleted.');

        $schedule->delete();

        return response()->json(['success' => true, 'message' => 'Schedule deleted.']);
    }

    // ═══════════════════════════════════════════════════════════════
    //  WORKFLOW ACTIONS
    // ═══════════════════════════════════════════════════════════════

    // ─── [POST] Admin SPPG submit tugas ke kurir ─────────────────────────────
    // PINTU MASUK → trigger Reverb broadcast ke kurir
    public function submitTask(Request $request, DeliverySchedule $schedule): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['sppg_admin', 'super_admin']), 403);

        $schedule = $this->service->submitTask($schedule, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Task submitted to courier.',
            'data'    => new DeliveryScheduleResource($schedule),
        ]);
    }

    // ─── [POST] Kurir menerima tugas ─────────────────────────────────────────
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

    // ─── [POST] Kurir menolak tugas + alasan + foto opsional ─────────────────
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

    // ─── [POST] Kurir submit foto bukti pengiriman ────────────────────────────
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

    // ─── [POST] Kurir resubmit bukti setelah diminta revisi ──────────────────
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

    // ─── [POST] Admin Logistik konfirmasi pengiriman ──────────────────────────
    // PINTU MASUK → arsip ke delivery_histories
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

    // ─── [POST] Admin Logistik minta revisi bukti ─────────────────────────────
    // PINTU MASUK → notifikasi ke kurir
    public function requestRevision(Request $request, DeliverySchedule $schedule): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['logistics_admin', 'super_admin']), 403);

        $request->validate(['notes' => 'required|string|min:5|max:500']);

        $schedule = $this->service->requestRevision($schedule, $request->user()->id, $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Revision requested. Courier will be notified.',
            'data'    => new DeliveryScheduleResource($schedule),
        ]);
    }

    // ─── [GET] Daftar kurir tersedia untuk dipilih admin logistik ─────────────
    // PINTU KELUAR – dropdown di FE saat membuat jadwal
    // BUG FIX: query lama pakai kolom 'role' yang tidak ada, orWhereHas tidak valid.
    // Sekarang: cukup filter by position='kurir' atau role slug='kurir'
    public function availableCouriers(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['logistics_admin', 'super_admin', 'sppg_admin']), 403);

        $couriers = Employee::query()
            ->where(function ($q) {
                // Filter by structural position
                $q->where('position', 'courier')
                  // OR by RBAC role slug
                  ->orWhereHas('role', fn($rq) => $rq->where('slug', 'courier'));
            })
            ->where('status', 'active')
            ->whereNotNull('user_id') // harus punya akun agar bisa menerima notifikasi
            ->select(['id', 'name', 'phone', 'position'])
            ->orderBy('name')
            ->get()
            ->map(fn($e) => [
                'id'       => $e->id,
                'name'     => $e->name,
                'phone'    => $e->phone,
                'position' => $e->position,
            ]);

        return response()->json(['success' => true, 'data' => $couriers]);
    }
}