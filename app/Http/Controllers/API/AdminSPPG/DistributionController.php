<?php

namespace App\Http\Controllers\API\AdminSPPG;

use App\Http\Controllers\Controller;
use App\Http\Resources\Distribution\DeliveryScheduleResource;
use App\Models\DeliverySchedule;
use App\Services\Distribution\DeliveryScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * DistributionController – AdminSPPG namespace
 *
 * Menyajikan data jadwal distribusi untuk panel admin SPPG.
 * Endpoint ini merupakan tampilan dari sudut pandang admin SPPG;
 * workflow lengkap ada di routes/distribution.php.
 *
 * Base URL: /api/admin-sppg/distributions
 */
class DistributionController extends Controller implements HasMiddleware
{
    public function __construct(private readonly DeliveryScheduleService $service)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:distribution.read',   only: ['index', 'show']),
            new Middleware('permission:distribution.create', only: ['store']),
            new Middleware('permission:distribution.update', only: ['update']),
            new Middleware('permission:distribution.delete', only: ['destroy']),
        ];
    }

    /**
     * [GET] Daftar jadwal pengiriman (active).
     * Admin SPPG melihat semua jadwal; kurir hanya miliknya.
     */
    public function index(Request $request): JsonResponse
    {
        // Scope isolasi by SPPG — delivery_schedules tidak punya sppg_id langsung,
        // sehingga kita filter melalui relasi school
        $sppgId = $request->attributes->get('sppg_id');

        $query = DeliverySchedule::with(['courier', 'school', 'assignedBy', 'submittedBy', 'latestLocation'])
            ->whereHas('school', fn ($q) => $q->where('sppg_id', $sppgId))
            ->latest();

        // Kurir hanya melihat jadwal miliknya sendiri
        if ($request->user()->hasAnyRole(['courier'])) {
            $query->forCourier($request->user()->employee?->id ?? 0);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: tampilkan semua status kecuali yang sudah confirmed/rejected lama
            $query->whereNotIn('status', []);
        }

        if ($request->filled('courier_id')) {
            $query->where('courier_id', $request->courier_id);
        }

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

    /**
     * [GET] Detail satu jadwal.
     */
    public function show(Request $request, DeliverySchedule $schedule): JsonResponse
    {
        // Pastikan jadwal ini memang milik SPPG yang sedang login
        $sppgId = $request->attributes->get('sppg_id');
        abort_unless(
            $schedule->school?->sppg_id === $sppgId,
            403,
            'Jadwal ini bukan milik SPPG Anda.'
        );

        $schedule->load(['courier', 'school', 'assignedBy', 'submittedBy', 'confirmedBy', 'latestLocation']);

        return response()->json([
            'success' => true,
            'data'    => new DeliveryScheduleResource($schedule),
        ]);
    }

    /**
     * [POST] Admin SPPG submit tugas ke kurir.
     * Memicu Reverb broadcast ke kurir.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'schedule_id' => ['required', 'integer', 'exists:delivery_schedules,id'],
        ]);

        $sppgId  = $request->attributes->get('sppg_id');
        $schedule = DeliverySchedule::findOrFail($request->schedule_id);

        // Verifikasi jadwal milik SPPG ini
        abort_unless(
            $schedule->school?->sppg_id === $sppgId,
            403,
            'Jadwal ini bukan milik SPPG Anda.'
        );

        $schedule = $this->service->submitTask($schedule, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Task submitted to courier.',
            'data'    => new DeliveryScheduleResource($schedule),
        ]);
    }

    /**
     * [PUT] Tidak digunakan di SPPG flow – hanya admin_logistik yang bisa update jadwal.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Schedule updates must be done via admin logistik.',
        ], 403);
    }

    /**
     * [DELETE] Tidak digunakan di SPPG flow.
     */
    public function destroy(string $id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Schedule deletion must be done via admin logistik.',
        ], 403);
    }
}
