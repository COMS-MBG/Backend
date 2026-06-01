<?php

namespace App\Events\Distribution;

use App\Models\DeliverySchedule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast perubahan status pengiriman ke admin dashboard dan kurir.
 *
 * CHANNELS:
 *   presence-distribution.operations → admin operations dashboard
 *   private-courier.{courier_id}     → notifikasi ke kurir terkait
 *
 * EVENT: distribution.status.updated
 *
 * FE subscribe:
 *   Echo.join('distribution.operations').listen('.distribution.status.updated', cb)
 */
class DeliveryStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly DeliverySchedule $schedule)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('distribution.operations'),
            // Juga notifikasi ke kurir terkait secara personal
            new PrivateChannel("courier.{$this->schedule->courier_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'distribution.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'schedule_id'  => $this->schedule->id,
            'status'       => $this->schedule->status,
            'courier_id'   => $this->schedule->courier_id,
            'school_id'    => $this->schedule->school_id,
            'departed_at'  => $this->schedule->departed_at?->toIso8601String(),
            'arrived_at'   => $this->schedule->arrived_at?->toIso8601String(),
            'confirmed_at' => $this->schedule->confirmed_at?->toIso8601String(),
        ];
    }
}
