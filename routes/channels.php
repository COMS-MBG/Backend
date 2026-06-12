<?php

use App\Models\DeliverySchedule;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Distribution Module – Broadcast Channel Definitions
|--------------------------------------------------------------------------
|
| FILE LOCATION: routes/channels.php  (merge into your existing channels.php)
|
| Laravel Reverb Setup Commands:
|   php artisan reverb:install
|   php artisan reverb:start            # dev
|   php artisan queue:work              # for queued broadcasts
|
| Required .env keys:
|   REVERB_APP_ID=your-app-id
|   REVERB_APP_KEY=your-app-key
|   REVERB_APP_SECRET=your-app-secret
|   REVERB_HOST=localhost
|   REVERB_PORT=8080
|   REVERB_SCHEME=http
|
| FE (Laravel Echo) setup:
|   import Echo from 'laravel-echo';
|   import Pusher from 'pusher-js';
|   window.Pusher = Pusher;
|   window.Echo = new Echo({
|     broadcaster: 'reverb',
|     key: import.meta.env.VITE_REVERB_APP_KEY,
|     wsHost: import.meta.env.VITE_REVERB_HOST,
|     wsPort: import.meta.env.VITE_REVERB_PORT,
|     forceTLS: false,
|     enabledTransports: ['ws', 'wss'],
|   });
*/

// Private channel – courier-specific notifications
// Event fired: distribution.task.submitted, distribution.status.updated
Broadcast::channel('courier.{courierId}', function ($user, int $courierId) {
    // Allow if the authenticated user is that courier
    return $user->employee?->id === $courierId
        || $user->hasAnyRole(['admin_logistik', 'admin_sppg', 'super_admin']);
});

// Presence channel – admin operations dashboard
// Events: distribution.status.updated
Broadcast::channel('distribution.operations', function ($user) {
    if ($user->hasAnyRole(['admin_logistik', 'admin_sppg', 'super_admin', 'courier'])) {
        return ['id' => $user->id, 'name' => $user->name, 'role' => $user->getRoleNames()->first()];
    }
});

// Presence channel – spatial map (admin real-time tracking)
// Events: distribution.courier.location
Broadcast::channel('distribution.map', function ($user) {
    if ($user->hasAnyRole(['admin_logistik', 'admin_sppg', 'super_admin', 'courier'])) {
        return ['id' => $user->id, 'name' => $user->name];
    }
});