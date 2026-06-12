<?php

/*
|--------------------------------------------------------------------------
| Distribution Module Configuration
|--------------------------------------------------------------------------
|
| FILE LOCATION: config/distribution.php
|
| Set these in your .env file:
|   DISTRIBUTION_DEPOT_NAME="SPG Pusat"
|   DISTRIBUTION_DEPOT_LAT=-6.914744
|   DISTRIBUTION_DEPOT_LNG=107.609810
|   OSRM_BASE_URL=https://router.project-osrm.org
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | SPG Depot / Origin Point "A" for Spatial Map
    |--------------------------------------------------------------------------
    */
    'depot_name' => env('DISTRIBUTION_DEPOT_NAME', 'SPG Pusat'),
    'depot_lat'  => (float) env('DISTRIBUTION_DEPOT_LAT', -6.914744),  // Default: Bandung
    'depot_lng'  => (float) env('DISTRIBUTION_DEPOT_LNG', 107.609810),

    /*
    |--------------------------------------------------------------------------
    | OSRM Route Engine
    |--------------------------------------------------------------------------
    | For production, run your own OSRM instance with Indonesia OSM data.
    | Public instance has rate limits — fine for dev/staging.
    |
    | Self-hosted OSRM docker command:
    |   docker run -t -v $(pwd):/data ghcr.io/project-osrm/osrm-backend \
    |     osrm-extract -p /opt/car.lua /data/indonesia-latest.osm.pbf
    |   docker run -t -v $(pwd):/data ghcr.io/project-osrm/osrm-backend osrm-partition /data/indonesia-latest.osrm
    |   docker run -t -v $(pwd):/data ghcr.io/project-osrm/osrm-backend osrm-customize /data/indonesia-latest.osrm
    |   docker run -t -i -p 5000:5000 -v $(pwd):/data ghcr.io/project-osrm/osrm-backend \
    |     osrm-routed --algorithm mld /data/indonesia-latest.osrm
    |
    | Then set: OSRM_BASE_URL=http://localhost:5000
    */
    'osrm_base_url' => env('OSRM_BASE_URL', 'https://router.project-osrm.org'),

    /*
    |--------------------------------------------------------------------------
    | GPS Tracking
    |--------------------------------------------------------------------------
    */
    'gps_ping_interval_seconds' => (int) env('GPS_PING_INTERVAL', 10),

    /*
    |--------------------------------------------------------------------------
    | Vehicle Types
    |--------------------------------------------------------------------------
    */
    'vehicle_types' => [
        'motorcycle' => 'Motorcycle',
        'car'        => 'Car',
        'van'        => 'Van',
        'truck'      => 'Truck',
    ],

];