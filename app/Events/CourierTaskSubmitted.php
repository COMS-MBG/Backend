<?php

namespace App\Events;

use App\Models\DeliverySchedule;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast via Laravel Reverb to notify a specific courier of a new task.
 *
 * CHANNEL: private-courier.{courier_id}
 * EVENT NAME: distribution.task.submitted
 *
 * REVERB SETUP:
 *   1. php artisan reverb:install
 *   2. Set REVERB_APP_ID, REVERB_APP_KEY, REVERB_APP_SECRET in .env
 *   3. php artisan reverb:start  (dev) or use supervisor in production
 *
 * FE subscribes: Echo.private(`courier.${courierId}`).listen('.distribution.task.submitted', cb)
 */
class CourierTaskSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly DeliverySchedule $schedule)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("courier.{$this->schedule->courier_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'distribution.task.submitted';
    }

    public function broadcastWith(): array
    {
        return [
            'schedule_id'    => $this->schedule->id,
            'school_name'    => $this->schedule->school->name ?? '',
            'school_address' => $this->schedule->school->address ?? '',
            'vehicle_type'   => $this->schedule->vehicle_type,
            'vehicle_plate'  => $this->schedule->vehicle_plate,
            'scheduled_at'   => $this->schedule->scheduled_at?->toIso8601String(),
            'notes'          => $this->schedule->delivery_notes,
        ];
    }
}