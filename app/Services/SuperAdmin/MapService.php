<?php

namespace App\Services\SuperAdmin;

use App\Models\SPPG;
use App\Models\Partner;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MapService
{
    private string $osrmBase;

    public function __construct()
    {
        $this->osrmBase = env('OSRM_BASE_URL', 'http://router.project-osrm.org');
    }

    // ─── Haversine (fallback jarak lurus) ───────────────────────────────────────
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r    = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return round($r * 2 * asin(sqrt($a)), 2);
    }

    // ─── OSRM: jarak jalan + durasi ─────────────────────────────────────────────
    // Return: ['distance_km' => float, 'duration_minutes' => float] atau null
    public function getRouteDurationAndDistance(float $latA, float $lonA, float $latB, float $lonB): ?array
    {
        // Koordinat sama persis → skip HTTP call
        if (abs($latA - $latB) < 0.00001 && abs($lonA - $lonB) < 0.00001) {
            return ['distance_km' => 0.0, 'duration_minutes' => 0.0];
        }

        $url = "{$this->osrmBase}/route/v1/driving/{$lonA},{$latA};{$lonB},{$latB}?overview=false";

        try {
            $res = Http::timeout(6)
                ->withHeaders(['User-Agent' => 'COMS-MBG-SuperAdmin/1.0'])
                ->get($url);

            if ($res->successful()) {
                $data = $res->json();
                if (!empty($data['routes'][0])) {
                    $route = $data['routes'][0];
                    return [
                        'distance_km'      => round($route['distance'] / 1000.0, 2),
                        'duration_minutes' => round($route['duration'] / 60.0, 1),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('OSRM error: ' . $e->getMessage());
        }

        return null;
    }

    // ─── Wrapper: ambil jarak jalan, fallback Haversine ─────────────────────────
    private function getRoadDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $route = $this->getRouteDurationAndDistance($lat1, $lon1, $lat2, $lon2);
        return $route ? $route['distance_km'] : $this->calculateHaversineDistance($lat1, $lon1, $lat2, $lon2);
    }

    private function getRoadDurationMin(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $route = $this->getRouteDurationAndDistance($lat1, $lon1, $lat2, $lon2);
        return $route ? $route['duration_minutes'] : 999.0;
    }

    // ─── Estimasi durasi dari jarak Haversine ────────────────────────────────────
    private function estimateDurationFromHaversine(float $distKm): float
    {
        // Asumsi rata-rata 10 km/jam di jalan kota → 6 menit per km
        return round($distKm * 6.0, 1);
    }

    // ─── Validasi titik pengajuan ────────────────────────────────────────────────
    public function validatePoint(float $lat, float $lng, array $draftPartners): array
    {
        Log::info('[MapService] validatePoint started', compact('lat', 'lng'));
        $status    = 'green';
        $conflicts = [];

        // 1. Cek overlap centroid ke SPPG aktif (pakai Haversine)
        $activeSppgs = SPPG::where('status', 'active')
            ->select('id', 'name', 'latitude', 'longitude', 'capacity')
            ->withCount('partners')
            ->get();

        foreach ($activeSppgs as $sppg) {
            $distKm = $this->calculateHaversineDistance($lat, $lng, $sppg->latitude, $sppg->longitude);
            if ($distKm <= 5.0) {
                $overcapacity = $sppg->partners_count >= ($sppg->capacity ?? 9999);
                if ($overcapacity) {
                    if ($status !== 'red') $status = 'yellow';
                    $conflicts[] = "Overlap {$distKm}km dengan SPPG {$sppg->name} (overcapacity).";
                } else {
                    $status      = 'red';
                    $conflicts[] = "Overlap {$distKm}km dengan SPPG {$sppg->name} yang masih memiliki kapasitas.";
                }
            }
        }

        // 2. Cek takeover rule per mitra draft (pakai Haversine)
        foreach ($draftPartners as $partner) {
            if (empty($partner['latitude']) || empty($partner['longitude'])) continue;

            $existing = null;
            if (!empty($partner['npsn'])) {
                $existing = Partner::where('npsn', $partner['npsn'])->whereNotNull('sppg_id')->first();
            }
            if (!$existing) {
                $existing = Partner::whereNotNull('sppg_id')->get()->first(
                    fn($p) => $this->calculateHaversineDistance(
                        $partner['latitude'], $partner['longitude'], $p->latitude, $p->longitude
                    ) < 0.05
                );
            }

            if ($existing?->sppg) {
                $existSppg   = $existing->sppg;
                $distExist   = $this->calculateHaversineDistance($existSppg->latitude, $existSppg->longitude, $existing->latitude, $existing->longitude);
                $durExist    = $this->estimateDurationFromHaversine($distExist);

                if ($distExist <= 5.0 && $durExist <= 30.0) {
                    $status      = 'red';
                    $conflicts[] = "Mitra {$partner['school_name']} tidak bisa di-takeover dari {$existSppg->name} ({$distExist}km, {$durExist}mnt).";
                } else {
                    $distNew = $this->calculateHaversineDistance($lat, $lng, $partner['latitude'], $partner['longitude']);
                    if ($distNew < $distExist) {
                        if ($status !== 'red') $status = 'yellow';
                        $conflicts[] = "Mitra {$partner['school_name']} bisa di-takeover ({$distNew}km vs {$distExist}km dari {$existSppg->name}).";
                    }
                }
            }
        }

        Log::info('[MapService] validatePoint completed', ['status' => $status, 'conflicts_count' => count($conflicts)]);
        return ['status' => $status, 'conflicts' => $conflicts];
    }

    // ─── Saran geser titik ke centroid optimal (A → A.1) ────────────────────────
    public function suggestCentroidShift(float $lat, float $lng, array $draftPartners): ?array
    {
        $reachable = array_filter($draftPartners, function ($p) use ($lat, $lng) {
            if (empty($p['latitude']) || empty($p['longitude'])) return false;
            return $this->calculateHaversineDistance($lat, $lng, $p['latitude'], $p['longitude']) <= 5.0;
        });

        if (empty($reachable)) return null;

        $sumLat = array_sum(array_column($reachable, 'latitude'));
        $sumLng = array_sum(array_column($reachable, 'longitude'));
        $count  = count($reachable);

        $newLat = $sumLat / $count;
        $newLng = $sumLng / $count;

        $shiftKm = $this->calculateHaversineDistance($lat, $lng, $newLat, $newLng);
        if ($shiftKm > 0.5) {
            return [
                'latitude'        => round($newLat, 8),
                'longitude'       => round($newLng, 8),
                'distance_meters' => round($shiftKm * 1000, 0),
            ];
        }

        return null;
    }

    // ─── Rekomendasi mitra untuk titik SPPG baru ────────────────────────────────
    // Pertimbangkan kapasitas SPPG vs kebutuhan porsi mitra.
    // Jika portion_count tidak tersedia, fallback ke jarak + durasi saja.
    public function recommendPartnersForPoint(float $lat, float $lng, int $capacity = 3000): array
    {
        Log::info('[MapService] recommendPartnersForPoint started', compact('lat', 'lng', 'capacity'));

        $candidates = Partner::whereNull('sppg_id')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id', 'school_name', 'npsn', 'school_type', 'ownership_status', 'address',
                     'latitude', 'longitude', 'district', 'city', 'portion_count', 'sppg_id')
            ->get()
            ->map(function ($p) use ($lat, $lng) {
                $distKm  = $this->calculateHaversineDistance($lat, $lng, $p->latitude, $p->longitude);
                $durMin  = $this->estimateDurationFromHaversine($distKm);
                return [
                    'id'            => $p->id,
                    'school_name'   => $p->school_name,
                    'npsn'          => $p->npsn,
                    'level'         => $p->school_type       ?? null,
                    'school_status' => $p->ownership_status  ?? null,
                    'address'       => $p->address           ?? null,
                    'latitude'      => $p->latitude,
                    'longitude'     => $p->longitude,
                    'district'      => $p->district,
                    'city'          => $p->city,
                    'portion_count' => $p->portion_count ?? 0,
                    'distance_km'   => $distKm,
                    'duration_min'  => $durMin,
                    'data_source'   => 'database',
                ];
            })
            ->filter(fn($p) => $p['distance_km'] <= 5.0 && $p['duration_min'] <= 30.0)
            ->sortBy('distance_km')
            ->values();

        // Pilih mitra sampai kapasitas terpenuhi
        $selected    = [];
        $totalPorsi  = 0;
        $hasPortions = $candidates->sum('portion_count') > 0;

        foreach ($candidates as $c) {
            if ($hasPortions) {
                if ($totalPorsi + $c['portion_count'] > $capacity) continue;
                $totalPorsi += $c['portion_count'];
            }
            $selected[] = $c;
            // Tanpa data porsi: ambil maksimal 4
            if (!$hasPortions && count($selected) >= 4) break;
        }

        Log::info('[MapService] recommendPartnersForPoint completed', ['candidates' => $candidates->count(), 'selected' => count($selected)]);
        return $selected;
    }

    // ─── K-Means rekomendasi pembangunan SPPG ───────────────────────────────────
    public function getKMeansRecommendations(): array
    {
        Log::info('[MapService] KMeans recommendation generation started');

        $unserved = Partner::whereNull('sppg_id')
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->select('id', 'school_name', 'latitude', 'longitude', 'sppg_id')
            ->get();

        $takeover = Partner::whereNotNull('sppg_id')
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->select('id', 'school_name', 'latitude', 'longitude', 'sppg_id')
            ->with('sppg:id,latitude,longitude')
            ->get()
            ->filter(fn($p) => $p->sppg &&
                $this->calculateHaversineDistance($p->sppg->latitude, $p->sppg->longitude, $p->latitude, $p->longitude) > 5.0
            );

        $points = collect($unserved)->concat($takeover)->map(fn($p) => [
            'id'        => $p->id,
            'name'      => $p->school_name,
            'latitude'  => $p->latitude,
            'longitude' => $p->longitude,
        ])->values()->all();

        Log::info('[MapService] Schools loaded for KMeans', [
            'unserved' => $unserved->count(),
            'takeover' => $takeover->count(),
            'total_points' => count($points),
        ]);

        if (empty($points)) return [];

        // K heuristic: 1 cluster per ~5 sekolah, minimal 2, maksimal 10
        $k         = max(2, min(10, (int) ceil(count($points) / 5)));
        $centroids = collect($points)->shuffle()->take($k)->map(fn($p) => [
            'latitude'  => $p['latitude'],
            'longitude' => $p['longitude'],
        ])->values()->all();

        for ($iter = 0; $iter < 20; $iter++) {
            $clusters = array_fill(0, count($centroids), []);
            foreach ($points as $p) {
                $best = 0;
                $bestDist = PHP_FLOAT_MAX;
                foreach ($centroids as $i => $c) {
                    $d = $this->calculateHaversineDistance($p['latitude'], $p['longitude'], $c['latitude'], $c['longitude']);
                    if ($d < $bestDist) { $bestDist = $d; $best = $i; }
                }
                $clusters[$best][] = $p;
            }
            $moved = false;
            foreach ($centroids as $i => &$c) {
                if (empty($clusters[$i])) continue;
                $newLat = array_sum(array_column($clusters[$i], 'latitude'))  / count($clusters[$i]);
                $newLng = array_sum(array_column($clusters[$i], 'longitude')) / count($clusters[$i]);
                if (abs($c['latitude'] - $newLat) > 0.0001 || abs($c['longitude'] - $newLng) > 0.0001) {
                    $c['latitude'] = $newLat; $c['longitude'] = $newLng; $moved = true;
                }
            }
            unset($c);
            if (!$moved) break;
        }

        // Hapus centroid kosong (cluster tanpa anggota)
        $centroids = array_values(array_filter($centroids, fn($c, $i) => !empty($clusters[$i]), ARRAY_FILTER_USE_BOTH));

        $results = [];
        foreach ($centroids as $idx => $c) {
            // Gunakan Haversine untuk semua filter — tanpa OSRM
            $serving = [];
            foreach ($points as $p) {
                $distKm = $this->calculateHaversineDistance($c['latitude'], $c['longitude'], $p['latitude'], $p['longitude']);
                if ($distKm > 5.0) continue;

                $durMin = $this->estimateDurationFromHaversine($distKm);
                if ($durMin <= 30.0) {
                    $serving[] = array_merge($p, ['distance_km' => $distKm, 'duration_min' => $durMin]);
                }
            }

            // Minimal 2 sekolah untuk menghasilkan rekomendasi
            if (count($serving) >= 2) {
                $results[] = [
                    'latitude'     => round($c['latitude'], 8),
                    'longitude'    => round($c['longitude'], 8),
                    'school_count' => count($serving),
                    'schools'      => $serving,
                ];
            }
        }

        Log::info('[MapService] KMeans recommendations generated', ['count' => count($results)]);
        return $results;
    }

    // ─── Helper: semua data sekolah/mitra untuk layer peta ──────────────────────
    public function getSchoolsLayerData(): array
    {
        Log::info('[MapService] getSchoolsLayerData started');

        $activeSppgs = SPPG::where('status', 'active')
            ->select('id', 'name', 'latitude', 'longitude', 'capacity')
            ->withCount('partners')
            ->get()
            ->keyBy('id');

        $schools = Partner::select('id','school_name','npsn','school_type','ownership_status',
                'district','city','latitude','longitude','portion_count','sppg_id')
            ->get()
            ->map(function ($p) use ($activeSppgs) {
                $status   = 'unserved';
                $sppgName = null;
                $distKm   = null;

                if ($p->sppg_id && $activeSppgs->has($p->sppg_id)) {
                    $sppg   = $activeSppgs->get($p->sppg_id);
                    $distKm = $this->calculateHaversineDistance($p->latitude, $p->longitude, $sppg->latitude, $sppg->longitude);

                    // Kuning jika jarak Haversine > 5km (takeover candidate)
                    $status   = $distKm <= 5.0 ? 'served' : 'takeover_candidate';
                    $sppgName = $sppg->name;
                }

                return [
                    'id'               => $p->id,
                    'school_name'      => $p->school_name,
                    'npsn'             => $p->npsn,
                    'school_type'      => $p->school_type,
                    'ownership_status' => $p->ownership_status,
                    'district'         => $p->district,
                    'city'             => $p->city,
                    'latitude'         => $p->latitude,
                    'longitude'        => $p->longitude,
                    'portion_count'    => $p->portion_count,
                    'sppg_id'          => $p->sppg_id,
                    'sppg_name'        => $sppgName,
                    'status'           => $status,
                    'road_distance_km' => $distKm,
                ];
            })
            ->values()
            ->all();

        Log::info('[MapService] getSchoolsLayerData completed', ['count' => count($schools)]);
        return $schools;
    }
}