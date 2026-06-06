<?php

namespace App\Services\Distribution;

use App\Events\Distribution\CourierTaskSubmitted;
use App\Events\Distribution\DeliveryStatusUpdated;
use App\Models\DeliveryHistory;
use App\Models\DeliverySchedule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeliveryScheduleService
{
    // ─── Admin Logistik: create draft ────────────────────────────────────────

    public function createSchedule(array $data, int $adminId): DeliverySchedule
    {
        $schedule = DeliverySchedule::create([
            'courier_id'     => $data['courier_id'],
            'school_id'      => $data['school_id'],
            'assigned_by'    => $adminId,
            'vehicle_type'   => $data['vehicle_type'],
            'vehicle_plate'  => $data['vehicle_plate'] ?? null,
            'scheduled_at'   => $data['scheduled_at'],
            'delivery_notes' => $data['delivery_notes'] ?? null,
            'status'         => DeliverySchedule::STATUS_IN_ORDER,
        ]);

        return $schedule->load(['courier', 'school', 'assignedBy']);
    }

    public function updateSchedule(DeliverySchedule $schedule, array $data): DeliverySchedule
    {
        abort_unless($schedule->isEditable(), 422, 'Schedule cannot be edited in its current status.');

        $schedule->update([
            'courier_id'     => $data['courier_id']     ?? $schedule->courier_id,
            'school_id'      => $data['school_id']      ?? $schedule->school_id,
            'vehicle_type'   => $data['vehicle_type']   ?? $schedule->vehicle_type,
            'vehicle_plate'  => $data['vehicle_plate']  ?? $schedule->vehicle_plate,
            'scheduled_at'   => $data['scheduled_at']   ?? $schedule->scheduled_at,
            'delivery_notes' => $data['delivery_notes'] ?? $schedule->delivery_notes,
        ]);

        return $schedule->fresh(['courier', 'school', 'assignedBy']);
    }

    // ─── Admin SPPG: submit task to courier ──────────────────────────────────
    // BUG FIX: sebelumnya status di-set ulang ke STATUS_IN_ORDER (redundan).
    // Sekarang: tandai submitted_by, broadcast ke kurir, status tetap in_order
    // agar acceptTask() & rejectTask() bisa memproses dengan benar.

    public function submitTask(DeliverySchedule $schedule, int $adminSppgId): DeliverySchedule
    {
        abort_unless(
            $schedule->status === DeliverySchedule::STATUS_IN_ORDER,
            422,
            'Only in_order tasks can be submitted to a courier.'
        );

        $schedule->update([
            'submitted_by' => $adminSppgId,
        ]);

        // Broadcast ke kurir via Laravel Reverb
        broadcast(new CourierTaskSubmitted($schedule->fresh(['school', 'courier'])))->toOthers();

        return $schedule->fresh(['courier', 'school', 'submittedBy']);
    }

    // ─── Courier: accept task ─────────────────────────────────────────────────

    public function acceptTask(DeliverySchedule $schedule): DeliverySchedule
    {
        abort_unless(
            $schedule->status === DeliverySchedule::STATUS_IN_ORDER,
            422,
            'Task is not in a state that can be accepted.'
        );

        $schedule->update([
            'status'      => DeliverySchedule::STATUS_DELIVERING,
            'departed_at' => now(),
        ]);

        broadcast(new DeliveryStatusUpdated($schedule))->toOthers();

        return $schedule->fresh(['school']);
    }

    // ─── Courier: reject task ─────────────────────────────────────────────────

    public function rejectTask(
        DeliverySchedule $schedule,
        string           $reason,
        ?UploadedFile    $photo = null
    ): DeliverySchedule {
        abort_unless(
            $schedule->status === DeliverySchedule::STATUS_IN_ORDER,
            422,
            'Task cannot be rejected in its current status.'
        );

        $photoPath = null;
        if ($photo) {
            $photoPath = $photo->store('delivery/rejections', 'public');
        }

        $schedule->update([
            'status'               => DeliverySchedule::STATUS_REJECTED,
            'rejection_reason'     => $reason,
            'rejection_photo_path' => $photoPath,
            'rejected_at'          => now(),
        ]);

        broadcast(new DeliveryStatusUpdated($schedule))->toOthers();

        return $schedule->fresh();
    }

    // ─── Courier: submit delivery proof ──────────────────────────────────────

    public function submitDeliveryProof(
        DeliverySchedule $schedule,
        UploadedFile     $photo
    ): DeliverySchedule {
        abort_unless(
            $schedule->status === DeliverySchedule::STATUS_DELIVERING,
            422,
            'Can only submit proof while status is delivering.'
        );

        $photoPath = $photo->store('delivery/proofs', 'public');

        $schedule->update([
            'proof_photo_path'   => $photoPath,
            'proof_submitted_at' => now(),
            'arrived_at'         => now(),
            'status'             => DeliverySchedule::STATUS_DELIVERED,
        ]);

        broadcast(new DeliveryStatusUpdated($schedule))->toOthers();

        return $schedule->fresh(['school', 'courier']);
    }

    // ─── Admin Logistik: confirm delivery ────────────────────────────────────

    public function confirmDelivery(
        DeliverySchedule $schedule,
        int              $adminId,
        string           $notes = ''
    ): DeliveryHistory {
        abort_unless(
            $schedule->status === DeliverySchedule::STATUS_DELIVERED,
            422,
            'Only delivered tasks can be confirmed.'
        );

        return DB::transaction(function () use ($schedule, $adminId, $notes) {
            $schedule->load(['courier', 'school']);

            $schedule->update([
                'status'             => DeliverySchedule::STATUS_CONFIRMED,
                'confirmed_by'       => $adminId,
                'confirmed_at'       => now(),
                'confirmation_notes' => $notes,
            ]);

            // Create delivery history snapshot
            $history = DeliveryHistory::create([
                'delivery_schedule_id' => $schedule->id,
                'courier_id'           => $schedule->courier_id,
                'school_id'            => $schedule->school_id,
                'courier_name'         => $schedule->courier->name ?? '',
                'school_name'          => $schedule->school->name    ?? '',
                'school_address'       => $schedule->school->address ?? null,
                'vehicle_type'         => $schedule->vehicle_type,
                'vehicle_plate'        => $schedule->vehicle_plate,
                'departed_at'          => $schedule->departed_at,
                'arrived_at'           => $schedule->arrived_at,
                'proof_photo_path'     => $schedule->proof_photo_path,
                'route_snapshot'       => $schedule->route_snapshot,
                'distance_km'          => $this->calculateDistance($schedule),
                'confirmed_by'         => $adminId,
                'confirmed_at'         => now(),
                'notes'                => $notes,
            ]);

            broadcast(new DeliveryStatusUpdated($schedule))->toOthers();

            return $history->load(['courier', 'school', 'confirmedBy']);
        });
    }

    // ─── Admin Logistik: request revision ────────────────────────────────────

    public function requestRevision(
        DeliverySchedule $schedule,
        int              $adminId,
        string           $notes
    ): DeliverySchedule {
        abort_unless(
            $schedule->status === DeliverySchedule::STATUS_DELIVERED,
            422,
            'Revision can only be requested for delivered tasks.'
        );

        $schedule->update([
            'status'             => DeliverySchedule::STATUS_REVISION_REQUIRED,
            'confirmed_by'       => $adminId,
            'confirmation_notes' => $notes,
        ]);

        broadcast(new DeliveryStatusUpdated($schedule))->toOthers();

        return $schedule->fresh(['courier']);
    }

    // ─── Courier: resubmit after revision request ─────────────────────────────

    public function resubmitProof(
        DeliverySchedule $schedule,
        UploadedFile     $photo
    ): DeliverySchedule {
        abort_unless(
            $schedule->status === DeliverySchedule::STATUS_REVISION_REQUIRED,
            422,
            'Proof can only be resubmitted when revision is required.'
        );

        if ($schedule->proof_photo_path) {
            Storage::disk('public')->delete($schedule->proof_photo_path);
        }

        $photoPath = $photo->store('delivery/proofs', 'public');

        $schedule->update([
            'proof_photo_path'   => $photoPath,
            'proof_submitted_at' => now(),
            'status'             => DeliverySchedule::STATUS_DELIVERED,
        ]);

        broadcast(new DeliveryStatusUpdated($schedule))->toOthers();

        return $schedule->fresh();
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function calculateDistance(DeliverySchedule $schedule): ?float
    {
        $route = $schedule->route_snapshot;

        if (!$route || !isset($route['coordinates']) || count($route['coordinates']) < 2) {
            return null;
        }

        $totalKm = 0.0;
        $coords  = $route['coordinates'];

        for ($i = 0; $i < count($coords) - 1; $i++) {
            $totalKm += $this->haversine(
                $coords[$i][1],       // lat
                $coords[$i][0],       // lon
                $coords[$i + 1][1],
                $coords[$i + 1][0]
            );
        }

        return round($totalKm, 3);
    }

    /**
     * Haversine formula – returns distance in km.
     */
    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
