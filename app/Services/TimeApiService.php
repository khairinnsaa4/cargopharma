<?php

namespace App\Services;

use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TimeApiService
{
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.timezone.url', 'https://timeapi.io/api/time/current/coordinate');
    }

    /**
     * Get real-time local time and timezone for given latitude and longitude.
     * Caches for 300 seconds, with robust mathematical timezone fallback.
     *
     * @return array{
     *     time: string,
     *     date: string,
     *     time_zone: string,
     *     utc_offset: string,
     *     abbreviation: string,
     *     day_of_week: string,
     *     source: string
     * }
     */
    public function getTimeForCoordinates(float $lat, float $lon): array
    {
        $cacheKey = 'cargopharma_tz_'.round($lat, 2).'_'.round($lon, 2);

        return Cache::remember($cacheKey, 300, function () use ($lat, $lon): array {
            try {
                $response = Http::timeout(1)->get($this->apiUrl, [
                    'latitude' => $lat,
                    'longitude' => $lon,
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (! empty($json['timeZone'])) {
                        return [
                            'time' => $json['time'] ?? date('H:i'),
                            'date' => $json['date'] ?? date('d M Y'),
                            'time_zone' => $json['timeZone'],
                            'utc_offset' => $json['currentUtcOffset']['seconds'] ?? 0 ? sprintf('%+03d:%02d', intdiv($json['currentUtcOffset']['seconds'], 3600), abs($json['currentUtcOffset']['seconds'] % 3600) / 60) : '+00:00',
                            'abbreviation' => $json['standardUtcOffset']['abbreviation'] ?? 'UTC',
                            'day_of_week' => $json['dayOfWeek'] ?? date('l'),
                            'source' => 'LIVE_TIMEAPI',
                        ];
                    }
                }
            } catch (Exception $e) {
                Log::debug("TimeAPI request failed for [{$lat}, {$lon}], using geometric fallback: ".$e->getMessage());
            }

            return $this->calculateFallbackTimezone($lat, $lon);
        });
    }

    /**
     * Get world clock times for key international pharmaceutical cargo hubs.
     *
     * @return array<int, array{hub: string, city: string, country: string, flag: string, code: string, tz: string, time: string, date: string, offset: string, desc: string}>
     */
    public function getGlobalHubsTime(): array
    {
        $hubs = [
            ['hub' => 'Bio Farma Production HQ', 'city' => 'Bandung', 'country' => 'Indonesia', 'flag' => '🇮🇩', 'code' => 'BDO', 'tz' => 'Asia/Jakarta', 'desc' => 'Pusat Produksi Vaksin WHO'],
            ['hub' => 'JAS Cargo Hub Soetta', 'city' => 'Jakarta / Tangerang', 'country' => 'Indonesia', 'flag' => '🇮🇩', 'code' => 'CGK', 'tz' => 'Asia/Jakarta', 'desc' => 'Gerbang Ekspor Farmasi'],
            ['hub' => 'Changi Cargo Mega Hub', 'city' => 'Singapura', 'country' => 'Singapura', 'flag' => '🇸🇬', 'code' => 'SIN', 'tz' => 'Asia/Singapore', 'desc' => 'Hub Farmasi Asia Tenggara'],
            ['hub' => 'Emirates SkyPharma DWC', 'city' => 'Dubai', 'country' => 'UAE', 'flag' => '🇦🇪', 'code' => 'DXB', 'tz' => 'Asia/Dubai', 'desc' => 'Hub Transit Rantai Dingin Utama'],
            ['hub' => 'NPHCDA National Vaccine Store', 'city' => 'Abuja', 'country' => 'Nigeria', 'flag' => '🇳🇬', 'code' => 'ABV', 'tz' => 'Africa/Lagos', 'desc' => 'Fasilitas Distribusi Afrika Barat'],
            ['hub' => 'Port of Rotterdam Logistics', 'city' => 'Rotterdam', 'country' => 'Belanda', 'flag' => '🇳🇱', 'code' => 'RTM', 'tz' => 'Europe/Amsterdam', 'desc' => 'Hub Kontainer Laut Maritim Eropa'],
            ['hub' => 'Lufthansa Cargo Cool Center', 'city' => 'Frankfurt', 'country' => 'Jerman', 'flag' => '🇩🇪', 'code' => 'FRA', 'tz' => 'Europe/Berlin', 'desc' => 'Hub Farmasi Eropa Tengah'],
            ['hub' => 'WHO / UN Health Logistics HQ', 'city' => 'Jenewa', 'country' => 'Swiss', 'flag' => '🇨🇭', 'code' => 'GVA', 'tz' => 'Europe/Zurich', 'desc' => 'Pusat Koordinasi Kesehatan PBB'],
        ];

        $results = [];
        foreach ($hubs as $h) {
            try {
                $tzObj = new DateTimeZone($h['tz']);
                $dt = new DateTime('now', $tzObj);
                $offsetSec = $tzObj->getOffset($dt);
                $offsetStr = sprintf('UTC%+03d:%02d', intdiv($offsetSec, 3600), abs($offsetSec % 3600) / 60);

                $results[] = array_merge($h, [
                    'time' => $dt->format('H:i:s'),
                    'time_short' => $dt->format('H:i'),
                    'date' => $dt->format('d M Y'),
                    'offset' => $offsetStr,
                ]);
            } catch (Exception) {
                // Fallback to UTC
                $results[] = array_merge($h, [
                    'time' => gmdate('H:i:s'),
                    'time_short' => gmdate('H:i'),
                    'date' => gmdate('d M Y'),
                    'offset' => 'UTC+00:00',
                ]);
            }
        }

        return $results;
    }

    /**
     * Fallback geometric timezone estimator based on world coordinates.
     */
    protected function calculateFallbackTimezone(float $lat, float $lon): array
    {
        $timezoneName = 'UTC';

        // Indonesia bounding box
        if ($lat >= -11.0 && $lat <= 6.0 && $lon >= 95.0 && $lon <= 141.0) {
            if ($lon < 110.0) {
                $timezoneName = 'Asia/Jakarta'; // WIB (UTC+7)
            } elseif ($lon < 120.0) {
                $timezoneName = 'Asia/Makassar'; // WITA (UTC+8)
            } else {
                $timezoneName = 'Asia/Jayapura'; // WIT (UTC+9)
            }
        } elseif ($lat >= 22.0 && $lat <= 26.0 && $lon >= 51.0 && $lon <= 56.5) {
            $timezoneName = 'Asia/Dubai'; // GST (UTC+4)
        } elseif ($lat >= 4.0 && $lat <= 14.0 && $lon >= 2.5 && $lon <= 15.0) {
            $timezoneName = 'Africa/Lagos'; // WAT (UTC+1)
        } elseif ($lat >= 50.0 && $lat <= 54.0 && $lon >= 3.0 && $lon <= 7.5) {
            $timezoneName = 'Europe/Amsterdam'; // CET (UTC+1)
        } else {
            // General 15-degree longitudinal estimate
            $offsetHours = (int) round($lon / 15.0);
            $timezoneName = timezone_name_from_abbr('', $offsetHours * 3600, 0) ?: 'UTC';
        }

        try {
            $tzObj = new DateTimeZone($timezoneName);
            $dt = new DateTime('now', $tzObj);
            $offsetSec = $tzObj->getOffset($dt);

            return [
                'time' => $dt->format('H:i'),
                'date' => $dt->format('d M Y'),
                'time_zone' => $timezoneName,
                'utc_offset' => sprintf('%+03d:%02d', intdiv($offsetSec, 3600), abs($offsetSec % 3600) / 60),
                'abbreviation' => $dt->format('T'),
                'day_of_week' => $dt->format('l'),
                'source' => 'LOCAL_PRECISION_ESTIMATE',
            ];
        } catch (Exception) {
            return [
                'time' => gmdate('H:i'),
                'date' => gmdate('d M Y'),
                'time_zone' => 'UTC',
                'utc_offset' => '+00:00',
                'abbreviation' => 'UTC',
                'day_of_week' => gmdate('l'),
                'source' => 'UTC_FALLBACK',
            ];
        }
    }
}
