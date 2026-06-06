<?php

namespace App\Services\SuperAdmin;

use App\Models\SPPG;
use App\Models\Partner;
use Illuminate\Support\Facades\Http;

class MapService
{
    /**
     * Calculate Haversine distance in km between two coordinate points.
     */
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371.0; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * asin(sqrt($a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Get route duration (minutes) and distance (meters) via OSRM proxy.
     */
    public function getRouteDurationAndDistance(float $latA, float $lonA, float $latB, float $lonB): ?array
    {
        $baseUrl = env('OSRM_BASE_URL', 'http://router.project-osrm.org');
        $url = "{$baseUrl}/route/v1/driving/{$lonA},{$latA};{$lonB},{$latB}?overview=false";

        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'COMS-MBG-SuperAdmin/1.0'])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['routes'][0])) {
                    $route = $data['routes'][0];
                    return [
                        'duration_minutes' => round($route['duration'] / 60.0, 1),
                        'distance_meters' => (float) $route['distance'],
                    ];
                }
            }
        } catch (\Exception $e) {
            // Ignore error
        }

        return null;
    }

    /**
     * Validate point status: green, yellow, or red based on proximity conflicts and takeover rules.
     */
    public function validatePoint(float $lat, float $lng, array $draftPartners): array
    {
        $pointStatus = 'green';
        $conflicts = [];

        // Check centroid distance to existing active SPPGs
        $existingSppgs = SPPG::where('status', 'active')->get();
        foreach ($existingSppgs as $sppg) {
            $dist = $this->calculateHaversineDistance($lat, $lng, $sppg->latitude, $sppg->longitude);
            if ($dist <= 5.0) {
                // If existing SPPG is overcapacity, we allow and flag it as yellow
                $isOvercapacity = $sppg->schools()->count() >= $sppg->capacity;
                if ($isOvercapacity) {
                    if ($pointStatus !== 'red') {
                        $pointStatus = 'yellow';
                    }
                    $conflicts[] = "Titik pengajuan berjarak {$dist}km (≤5km) dari SPPG {$sppg->name}, namun SPPG tersebut sudah melebihi kapasitas (overcapacity).";
                } else {
                    $pointStatus = 'red';
                    $conflicts[] = "Titik pengajuan berjarak {$dist}km (≤5km) dari SPPG {$sppg->name} yang aktif dan masih memiliki kapasitas kosong.";
                }
            }
        }

        // Check takeover rules for draft partners
        foreach ($draftPartners as $partner) {
            $existingPartner = null;
            if (!empty($partner['npsn'])) {
                $existingPartner = Partner::where('npsn', $partner['npsn'])->whereNotNull('sppg_id')->first();
            }
            if (!$existingPartner) {
                $existingPartner = Partner::whereNotNull('sppg_id')
                    ->get()
                    ->first(function ($p) use ($partner) {
                        return $this->calculateHaversineDistance($partner['latitude'], $partner['longitude'], $p->latitude, $p->longitude) < 0.05;
                    });
            }

            if ($existingPartner && $existingPartner->sppg) {
                $existingSppg = $existingPartner->sppg;
                $distToExisting = $this->calculateHaversineDistance($existingSppg->latitude, $existingSppg->longitude, $existingPartner->latitude, $existingPartner->longitude);
                
                // Estimate route duration via OSRM
                $route = $this->getRouteDurationAndDistance($existingSppg->latitude, $existingSppg->longitude, $existingPartner->latitude, $existingPartner->longitude);
                $durationToExisting = $route ? $route['duration_minutes'] : 999.0;

                if ($distToExisting <= 5.0 && $durationToExisting <= 30.0) {
                    $pointStatus = 'red';
                    $conflicts[] = "Mitra {$partner['school_name']} sudah dilayani oleh SPPG {$existingSppg->name} berjarak {$distToExisting}km (≤5km) dan waktu tempuh {$durationToExisting} menit (≤30 menit). Tidak dapat di-takeover.";
                } else {
                    // Check if new SPPG is closer than the existing one
                    $distToNew = $this->calculateHaversineDistance($lat, $lng, $partner['latitude'], $partner['longitude']);
                    if ($distToNew < $distToExisting) {
                        if ($pointStatus !== 'red') {
                            $pointStatus = 'yellow';
                        }
                        $conflicts[] = "Mitra {$partner['school_name']} dapat ditakeover dari SPPG {$existingSppg->name} karena SPPG baru lebih dekat ({$distToNew}km vs {$distToExisting}km).";
                    }
                }
            }
        }

        return [
            'status' => $pointStatus,
            'conflicts' => $conflicts,
        ];
    }

    /**
     * Recommend centroid shifting based on reachable draft partners.
     */
    public function suggestCentroidShift(float $lat, float $lng, array $draftPartners): ?array
    {
        $reachable = [];
        foreach ($draftPartners as $partner) {
            $dist = $this->calculateHaversineDistance($lat, $lng, $partner['latitude'], $partner['longitude']);
            if ($dist <= 5.0) {
                $reachable[] = $partner;
            }
        }

        if (empty($reachable)) {
            return null;
        }

        $sumLat = 0.0;
        $sumLng = 0.0;
        foreach ($reachable as $r) {
            $sumLat += $r['latitude'];
            $sumLng += $r['longitude'];
        }

        $centroidLat = $sumLat / count($reachable);
        $centroidLng = $sumLng / count($reachable);

        $shiftDist = $this->calculateHaversineDistance($lat, $lng, $centroidLat, $centroidLng);
        if ($shiftDist > 0.5) { // > 500 meters
            return [
                'latitude' => round($centroidLat, 8),
                'longitude' => round($centroidLng, 8),
                'distance_meters' => round($shiftDist * 1000.0, 0),
            ];
        }

        return null;
    }

    /**
     * Simple K-Means algorithm to generate system recommendations for placing SPPGs based on unserved schools.
     */
    public function getKMeansRecommendations(): array
    {
        $unserved = Partner::whereNull('sppg_id')->get();
        
        $takeoverCandidates = Partner::whereNotNull('sppg_id')
            ->with('sppg')
            ->get()
            ->filter(function($p) {
                if (!$p->sppg) return false;
                return $this->calculateHaversineDistance($p->sppg->latitude, $p->sppg->longitude, $p->latitude, $p->longitude) > 5.0;
            });

        $points = [];
        foreach ($unserved as $p) {
            $points[] = ['id' => $p->id, 'name' => $p->school_name, 'latitude' => $p->latitude, 'longitude' => $p->longitude];
        }
        foreach ($takeoverCandidates as $p) {
            $points[] = ['id' => $p->id, 'name' => $p->school_name, 'latitude' => $p->latitude, 'longitude' => $p->longitude];
        }

        if (empty($points)) {
            return [];
        }

        // K = school count / 200, min 1
        $k = max(1, (int) floor(count($points) / 200));

        // Initialize centroids randomly
        $centroids = [];
        $shuffled = $points;
        shuffle($shuffled);
        for ($i = 0; $i < min($k, count($shuffled)); $i++) {
            $centroids[] = [
                'latitude' => $shuffled[$i]['latitude'],
                'longitude' => $shuffled[$i]['longitude']
            ];
        }

        $maxIterations = 20;
        for ($iter = 0; $iter < $maxIterations; $iter++) {
            $clusters = array_fill(0, count($centroids), []);

            foreach ($points as $p) {
                $minDist = 999999.0;
                $minIndex = 0;
                foreach ($centroids as $index => $c) {
                    $dist = $this->calculateHaversineDistance($p['latitude'], $p['longitude'], $c['latitude'], $c['longitude']);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $minIndex = $index;
                    }
                }
                $clusters[$minIndex][] = $p;
            }

            $moved = false;
            foreach ($centroids as $index => &$c) {
                if (empty($clusters[$index])) continue;

                $sumLat = 0.0;
                $sumLng = 0.0;
                foreach ($clusters[$index] as $p) {
                    $sumLat += $p['latitude'];
                    $sumLng += $p['longitude'];
                }
                $newLat = $sumLat / count($clusters[$index]);
                $newLng = $sumLng / count($clusters[$index]);

                if (abs($c['latitude'] - $newLat) > 0.0001 || abs($c['longitude'] - $newLng) > 0.0001) {
                    $c['latitude'] = $newLat;
                    $c['longitude'] = $newLng;
                    $moved = true;
                }
            }

            if (!$moved) {
                break;
            }
        }

        $recommendations = [];
        foreach ($centroids as $c) {
            $servingSchools = [];
            foreach ($points as $p) {
                $dist = $this->calculateHaversineDistance($c['latitude'], $c['longitude'], $p['latitude'], $p['longitude']);
                if ($dist <= 5.0) {
                    $servingSchools[] = $p;
                }
            }

            if (count($servingSchools) >= 3) {
                $recommendations[] = [
                    'latitude' => round($c['latitude'], 8),
                    'longitude' => round($c['longitude'], 8),
                    'school_count' => count($servingSchools),
                    'schools' => $servingSchools,
                ];
            }
        }

        return $recommendations;
    }
}
