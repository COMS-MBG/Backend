<?php

namespace App\Events\Distribution;

use App\Models\CourierLocation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast GPS ping ke peta spasial admin.
 *
 * CHANNEL  : presence-distribution.map
 * EVENT    : distribution.courier.location
 *
 * HIGH-FREQUENCY: mobile app kirim ping setiap 5-10 detik saat mengantarkan.
 *
 * FE subscribe:
 *   Echo.join('distribution.map').listen('.distribution.courier.location', (data) => updateMarker(data))
 */
class CourierLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int             $scheduleId,
        public readonly int             $courierId,
        public readonly CourierLocation $location,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('distribution.map')];
    }

    public function broadcastAs(): string
    {
        return 'distribution.courier.location';
    }

    public function broadcastWith(): array
    {
        return [
            'schedule_id'     => $this->scheduleId,
            'courier_id'      => $this->courierId,
            'latitude'        => $this->location->latitude,
            'longitude'       => $this->location->longitude,
            'speed_kmh'       => $this->location->speed_kmh,
            'heading_degrees' => $this->location->heading_degrees,
            'recorded_at'     => $this->location->recorded_at->toIso8601String(),
        ];
    }
}
