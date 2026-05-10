<?php

namespace App\Services\Distribution;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Route Optimization Service
 *
 * FILE LOCATION: app/Services/Distribution/RouteOptimizationService.php
 *
 * ALGORITHM:
 *   1. Nearest-Neighbour Heuristic (TSP approximation) → determines visit order
 *   2. OSRM (Open Source Routing Machine) public API  → actual road-snapped route + ETA
 *
 * ML EXTENSION NOTE:
 *   If you want to plug in a trained ML model for route prediction:
 *   - Train the model separately (Python / scikit-learn / PyTorch)
 *   - Expose it as a microservice (FastAPI recommended)
 *   - Replace `nearestNeighbourTsp()` call with an HTTP call to that service
 *   - Suggested file: app/Services/Distribution/MlRouteClient.php
 *
 * OSRM SELF-HOSTING:
 *   For production, host your own OSRM instance with Indonesia OSM data.
 *   docker run -t -v $(pwd):/data ghcr.io/project-osrm/osrm-backend osrm-extract ...
 *   Set OSRM_BASE_URL in .env (default: https://router.project-osrm.org)
 */
class RouteOptimizationService
{
    private string $osrmBase;

    public function __construct()
    {
        $this->osrmBase = config('distribution.osrm_base_url', 'https://router.project-osrm.org');
    }

    /**
     * Optimize delivery order and get road-snapped route.
     *
     * @param  array  $origin  ['lat' => float, 'lng' => float]  – SPG/depot location
     * @param  array  $waypoints  [['lat'=>float,'lng'=>float,'school_id'=>int,'name'=>str], ...]
     * @return array  ['ordered_waypoints'=>[...], 'geojson'=>LineString, 'total_distance_km'=>float, 'total_duration_min'=>float]
     */
    public function optimize(array $origin, array $waypoints): array
    {
        if (count($waypoints) === 0) {
            return ['ordered_waypoints' => [], 'geojson' => null, 'total_distance_km' => 0, 'total_duration_min' => 0];
        }

        // Step 1 – determine optimal visit order (nearest-neighbour TSP)
        $orderedWaypoints = $this->nearestNeighbourTsp($origin, $waypoints);

        // Step 2 – get road-snapped polyline from OSRM
        $routeData = $this->fetchOsrmRoute($origin, $orderedWaypoints);

        return array_merge(['ordered_waypoints' => $orderedWaypoints], $routeData);
    }

    // ─── TSP: Nearest-Neighbour Heuristic ────────────────────────────────────

    /**
     * Greedy nearest-neighbour starting from origin.
     * Time complexity: O(n²) — fine for typical delivery batch size (≤ 30 schools).
     */
    private function nearestNeighbourTsp(array $origin, array $waypoints): array
    {
        $unvisited = $waypoints;
        $ordered   = [];
        $current   = $origin;

        while (!empty($unvisited)) {
            $nearestIdx  = null;
            $nearestDist = PHP_FLOAT_MAX;

            foreach ($unvisited as $idx => $wp) {
                $d = $this->haversine($current['lat'], $current['lng'], $wp['lat'], $wp['lng']);
                if ($d < $nearestDist) {
                    $nearestDist = $d;
                    $nearestIdx  = $idx;
                }
            }

            $ordered[] = $unvisited[$nearestIdx];
            $current   = $unvisited[$nearestIdx];
            array_splice($unvisited, $nearestIdx, 1);
        }

        return $ordered;
    }

    // ─── OSRM route fetch ────────────────────────────────────────────────────

    private function fetchOsrmRoute(array $origin, array $waypoints): array
    {
        // Build coordinate string: lon,lat;lon,lat;...
        $coords = array_map(fn($p) => "{$p['lng']},{$p['lat']}", array_merge([$origin], $waypoints));
        $coordStr = implode(';', $coords);

        $url = "{$this->osrmBase}/route/v1/driving/{$coordStr}";

        try {
            $response = Http::timeout(10)->get($url, [
                'overview'    => 'full',
                'geometries'  => 'geojson',
                'steps'       => 'false',
                'annotations' => 'false',
            ]);

            if (!$response->successful()) {
                Log::warning('OSRM route fetch failed', ['status' => $response->status()]);
                return $this->fallbackRoute($origin, $waypoints);
            }

            $data  = $response->json();
            $route = $data['routes'][0] ?? null;

            if (!$route) {
                return $this->fallbackRoute($origin, $waypoints);
            }

            return [
                'geojson'            => $route['geometry'],       // GeoJSON LineString
                'total_distance_km'  => round($route['distance'] / 1000, 3),
                'total_duration_min' => round($route['duration'] / 60, 1),
            ];
        } catch (\Exception $e) {
            Log::error('OSRM exception: ' . $e->getMessage());
            return $this->fallbackRoute($origin, $waypoints);
        }
    }

    /**
     * Fallback: build straight-line GeoJSON + haversine distance if OSRM is unavailable.
     */
    private function fallbackRoute(array $origin, array $waypoints): array
    {
        $points = array_merge([$origin], $waypoints);
        $coords = array_map(fn($p) => [$p['lng'], $p['lat']], $points);

        $totalKm = 0.0;
        for ($i = 0; $i < count($points) - 1; $i++) {
            $totalKm += $this->haversine(
                $points[$i]['lat'], $points[$i]['lng'],
                $points[$i + 1]['lat'], $points[$i + 1]['lng']
            );
        }

        return [
            'geojson' => [
                'type'        => 'LineString',
                'coordinates' => $coords,
            ],
            'total_distance_km'  => round($totalKm, 3),
            'total_duration_min' => round(($totalKm / 30) * 60, 1), // assume 30 km/h
        ];
    }

    // ─── Haversine ───────────────────────────────────────────────────────────

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R    = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}