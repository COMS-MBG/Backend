<?php

namespace App\Events\Distribution;

use App\Models\DeliverySchedule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast delivery status changes to admin dashboard and courier app.
 *
 * CHANNEL: presence-distribution.operations
 * EVENT NAME: distribution.status.updated
 *
 * FE subscribes: Echo.join('distribution.operations').listen('.distribution.status.updated', cb)
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
            // Also notify the specific courier
            new \Illuminate\Broadcasting\PrivateChannel("courier.{$this->schedule->courier_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'distribution.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'schedule_id'   => $this->schedule->id,
            'status'        => $this->schedule->status,
            'courier_id'    => $this->schedule->courier_id,
            'school_id'     => $this->schedule->school_id,
            'departed_at'   => $this->schedule->departed_at?->toIso8601String(),
            'arrived_at'    => $this->schedule->arrived_at?->toIso8601String(),
            'confirmed_at'  => $this->schedule->confirmed_at?->toIso8601String(),
        ];
    }
}