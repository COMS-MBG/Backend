<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddressValidationService
{
    /**
     * Validasi alamat dengan Nominatim
     * Return: lat, lng, display_name, confidence score, alternatives
     */
    public function validateAndSuggest(string $address, string $city = ''): array
    {
        $fullAddress = $address;
        if (!empty($city) && stripos($address, $city) === false) {
            $fullAddress = "{$address}, {$city}";
        }

        $url = 'https://nominatim.openstreetmap.org/search?q='
            . urlencode($fullAddress)
            . '&format=json&limit=5&addressdetails=1';

        try {
            $res = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'COMS-MBG/1.0', 'Accept' => 'application/json'])
                ->get($url);

            if ($res->successful()) {
                $results = $res->json();

                if (empty($results)) {
                    return [
                        'valid'       => false,
                        'message'     => 'Alamat tidak ditemukan. Periksa format: Jl. Nama No. X, Kecamatan, Kota',
                        'confidence'  => 0,
                    ];
                }

                $best = $results[0];

                return [
                    'valid'         => true,
                    'message'       => 'Alamat valid.',
                    'lat'           => (float) $best['lat'],
                    'lng'           => (float) $best['lon'],
                    'display_name'  => $best['display_name'],
                    'confidence'    => $this->calculateConfidence($best),
                    'alternatives'  => array_map(fn($r) => [
                        'display_name' => $r['display_name'],
                        'lat'          => (float) $r['lat'],
                        'lng'          => (float) $r['lon'],
                    ], array_slice($results, 1, 2)),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Address validation error: ' . $e->getMessage());
        }

        return [
            'valid'      => false,
            'message'    => 'Gagal validasi alamat ke server.',
            'confidence' => 0,
        ];
    }

    /**
     * Format alamat lengkap untuk geocoding
     */
    public function formatForGeocoding(array $addressParts): string
    {
        return implode(', ', array_filter([
            $addressParts['address']  ?? null,
            $addressParts['district'] ?? null,
            $addressParts['city']     ?? null,
            $addressParts['province'] ?? null,
        ]));
    }

    /**
     * Hitung confidence score (0-100)
     */
    private function calculateConfidence(array $result): int
    {
        $score = 50;
        $importance = (float) ($result['importance'] ?? 0.5);
        $score += (int) ($importance * 30);

        if (in_array($result['type'] ?? '', ['road', 'street', 'residential'])) {
            $score += 15;
        }

        return min(100, $score);
    }
}