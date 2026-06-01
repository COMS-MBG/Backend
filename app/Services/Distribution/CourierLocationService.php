<?php

namespace App\Services\Distribution;

use App\Events\Distribution\CourierLocationUpdated;
use App\Models\CourierLocation;
use App\Models\DeliverySchedule;

class CourierLocationService
{
    /**
     * Record a courier's GPS ping during an active delivery.
     * Broadcasts via Laravel Reverb so admin map updates in real-time.
     */
    public function recordLocation(DeliverySchedule $schedule, array $data): CourierLocation
    {
        abort_unless(
            $schedule->status === DeliverySchedule::STATUS_DELIVERING,
            422,
            'Location can only be recorded during an active delivery.'
        );

        $location = CourierLocation::create([
            'delivery_schedule_id' => $schedule->id,
            'courier_id'           => $schedule->courier_id,
            'latitude'             => $data['latitude'],
            'longitude'            => $data['longitude'],
            'speed_kmh'            => $data['speed_kmh']        ?? null,
            'heading_degrees'      => $data['heading_degrees']  ?? null,
            'accuracy_meters'      => $data['accuracy_meters']  ?? null,
            'recorded_at'          => now(),
        ]);

        // Append to route snapshot stored on the schedule
        $this->appendToRouteSnapshot($schedule, $data['latitude'], $data['longitude']);

        // Broadcast to admin dashboard via Reverb
        broadcast(new CourierLocationUpdated($schedule->id, $schedule->courier_id, $location))
            ->toOthers();

        return $location;
    }

    /**
     * Get the latest known location of every active courier.
     * BUG FIX: kolom School adalah 'nama', bukan 'name'.
     */
    public function getActiveCourierLocations(): array
    {
        $activeSchedules = DeliverySchedule::where('status', DeliverySchedule::STATUS_DELIVERING)
            ->with([
                'latestLocation',
                'courier:id,name',
                'school:id,nama,latitude,longitude',   // ← fix: pakai 'nama' bukan 'name'
            ])
            ->get();

        return $activeSchedules->map(function ($schedule) {
            $loc = $schedule->latestLocation;

            return [
                'schedule_id'  => $schedule->id,
                'courier_id'   => $schedule->courier_id,
                'courier_name' => $schedule->courier->name ?? '',
                'school'       => [
                    'id'        => $schedule->school->id,
                    'name'      => $schedule->school->nama      ?? '',    // ← fix
                    'latitude'  => $schedule->school->latitude,
                    'longitude' => $schedule->school->longitude,
                ],
                'current_location' => $loc ? [
                    'latitude'    => $loc->latitude,
                    'longitude'   => $loc->longitude,
                    'speed_kmh'   => $loc->speed_kmh,
                    'heading'     => $loc->heading_degrees,
                    'recorded_at' => $loc->recorded_at->toIso8601String(),
                ] : null,
                'route_snapshot' => $schedule->route_snapshot,
            ];
        })->toArray();
    }

    /**
     * Get full location trail for one delivery (for replay / route display).
     */
    public function getLocationTrail(DeliverySchedule $schedule): array
    {
        return $schedule->locations()
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude', 'speed_kmh', 'heading_degrees', 'recorded_at'])
            ->toArray();
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function appendToRouteSnapshot(DeliverySchedule $schedule, float $lat, float $lng): void
    {
        $snapshot = $schedule->route_snapshot ?? ['type' => 'LineString', 'coordinates' => []];
        $snapshot['coordinates'][] = [$lng, $lat]; // GeoJSON order: [lon, lat]
        $schedule->update(['route_snapshot' => $snapshot]);
    }
}
