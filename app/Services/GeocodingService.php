<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    protected string $nominatimUrl;

    public function __construct()
    {
        $this->nominatimUrl = 'https://nominatim.openstreetmap.org/search';
    }

    /**
     * Search coordinates by location name or keyword.
     * Uses OpenStreetMap Nominatim with graceful fallback for cold-chain hubs.
     *
     * @return array<int, array{name: string, display_name: string, lat: float, lng: float, type: string, source: string}>
     */
    public function search(string $query): array
    {
        $cleanQuery = trim($query);
        if (empty($cleanQuery)) {
            return [];
        }

        $cacheKey = 'geocode_search_'.md5(strtolower($cleanQuery));

        return Cache::remember($cacheKey, 86400, function () use ($cleanQuery): array {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'CargoTrace-PharmaLogistics/1.0 (contact: info@cargotrace.com)',
                    'Accept' => 'application/json',
                ])
                    ->timeout(4)
                    ->get($this->nominatimUrl, [
                        'q' => $cleanQuery,
                        'format' => 'json',
                        'limit' => 5,
                        'addressdetails' => 1,
                    ]);

                if ($response->successful()) {
                    $results = $response->json();
                    if (! empty($results) && is_array($results)) {
                        $formatted = [];
                        foreach ($results as $item) {
                            $formatted[] = [
                                'name' => $item['name'] ?? $cleanQuery,
                                'display_name' => $item['display_name'] ?? $cleanQuery,
                                'lat' => (float) $item['lat'],
                                'lng' => (float) $item['lon'],
                                'type' => $item['type'] ?? 'place',
                                'source' => 'LIVE_NOMINATIM_OSM',
                            ];
                        }

                        return $formatted;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Nominatim geocoding exception for '{$cleanQuery}': ".$e->getMessage());
            }

            // Fallback dictionary for popular logistics and airport hubs
            return $this->getDictionaryFallback($cleanQuery);
        });
    }

    /**
     * Pre-compiled geographical dictionary for biopharmaceutical logistics hubs.
     *
     * @return array<int, array{name: string, display_name: string, lat: float, lng: float, type: string, source: string}>
     */
    protected function getDictionaryFallback(string $query): array
    {
        $q = strtolower($query);

        $hubs = [
            'bandung' => [
                'name' => 'PT Bio Farma Plant Pasteur, Bandung',
                'display_name' => 'Jl. Pasteur No.28, Bandung, West Java, Indonesia',
                'lat' => -6.8996,
                'lng' => 107.5997,
                'type' => 'pharma_plant',
            ],
            'jakarta' => [
                'name' => 'Soekarno-Hatta International Airport (CGK)',
                'display_name' => 'Tangerang, Banten, Greater Jakarta, Indonesia',
                'lat' => -6.1275,
                'lng' => 106.6537,
                'type' => 'airport',
            ],
            'cgk' => [
                'name' => 'Soekarno-Hatta International Airport (CGK)',
                'display_name' => 'Tangerang, Banten, Greater Jakarta, Indonesia',
                'lat' => -6.1275,
                'lng' => 106.6537,
                'type' => 'airport',
            ],
            'dubai' => [
                'name' => 'Dubai International Airport Cargo Mega Terminal (DXB)',
                'display_name' => 'Dubai International Airport, Dubai, United Arab Emirates',
                'lat' => 25.2532,
                'lng' => 55.3657,
                'type' => 'transit_hub',
            ],
            'dxb' => [
                'name' => 'Dubai International Airport (DXB)',
                'display_name' => 'Dubai International Airport, Dubai, United Arab Emirates',
                'lat' => 25.2532,
                'lng' => 55.3657,
                'type' => 'transit_hub',
            ],
            'abuja' => [
                'name' => 'Nnamdi Azikiwe International Airport (ABV)',
                'display_name' => 'Federal Capital Territory, Abuja, Nigeria',
                'lat' => 9.0065,
                'lng' => 7.2631,
                'type' => 'destination_airport',
            ],
            'abv' => [
                'name' => 'Nnamdi Azikiwe International Airport (ABV)',
                'display_name' => 'Federal Capital Territory, Abuja, Nigeria',
                'lat' => 9.0065,
                'lng' => 7.2631,
                'type' => 'destination_airport',
            ],
            'singapore' => [
                'name' => 'Singapore Changi Airport (SIN)',
                'display_name' => 'Changi, Singapore',
                'lat' => 1.3644,
                'lng' => 103.9915,
                'type' => 'airport',
            ],
            'amsterdam' => [
                'name' => 'Amsterdam Airport Schiphol (AMS)',
                'display_name' => 'Haarlemmermeer, North Holland, Netherlands',
                'lat' => 52.3105,
                'lng' => 4.7683,
                'type' => 'airport',
            ],
            'frankfurt' => [
                'name' => 'Frankfurt Airport CargoCity (FRA)',
                'display_name' => 'Frankfurt am Main, Hesse, Germany',
                'lat' => 50.0379,
                'lng' => 8.5622,
                'type' => 'airport',
            ],
            'doha' => [
                'name' => 'Hamad International Airport (DOH)',
                'display_name' => 'Doha, Qatar',
                'lat' => 25.2731,
                'lng' => 51.6081,
                'type' => 'transit_hub',
            ],
        ];

        foreach ($hubs as $key => $hub) {
            if (str_contains($q, $key)) {
                return [[
                    'name' => $hub['name'],
                    'display_name' => $hub['display_name'],
                    'lat' => $hub['lat'],
                    'lng' => $hub['lng'],
                    'type' => $hub['type'],
                    'source' => 'LOGISTICS_DIRECTORY_FALLBACK',
                ]];
            }
        }

        // Generic fallback around equator/Bandung if query has no match
        return [[
            'name' => ucwords($query),
            'display_name' => ucwords($query).' (Estimated Coordinates)',
            'lat' => -6.9039,
            'lng' => 107.6186,
            'type' => 'approximate',
            'source' => 'ESTIMATED_DEFAULT',
        ]];
    }
}
