<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RoutingService
{
    protected string $osrmUrl;

    public function __construct()
    {
        $this->osrmUrl = config('services.routing.osrm_url', 'https://router.project-osrm.org/route/v1/driving');
    }

    /**
     * Get realistic driving route along highways using OSRM (Open Source Routing Machine).
     *
     * @return array{
     *     coordinates: array<int, array{0: float, 1: float}>,
     *     distance_km: float,
     *     duration_minutes: float,
     *     mode: string,
     *     source: string
     * }
     */
    public function getRoadRoute(float $lat1, float $lng1, float $lat2, float $lng2): array
    {
        $cacheKey = 'osrm_route_'.round($lat1, 3).'_'.round($lng1, 3).'_'.round($lat2, 3).'_'.round($lng2, 3);

        return Cache::remember($cacheKey, 86400, function () use ($lat1, $lng1, $lat2, $lng2): array {
            try {
                // OSRM coordinates order is {longitude},{latitude}
                $url = "{$this->osrmUrl}/{$lng1},{$lat1};{$lng2},{$lat2}?overview=full&geometries=geojson";
                $response = Http::timeout(1)->get($url);

                if ($response->successful()) {
                    $json = $response->json();
                    $routes = $json['routes'] ?? [];
                    if (! empty($routes)) {
                        $primary = $routes[0];
                        $geoCoords = $primary['geometry']['coordinates'] ?? [];

                        // Convert [lng, lat] from GeoJSON to Leaflet [lat, lng]
                        $leafletCoords = array_map(function ($pt) {
                            return [(float) $pt[1], (float) $pt[0]];
                        }, $geoCoords);

                        $distKm = round(($primary['distance'] ?? 0) / 1000.0, 1);
                        $durationMin = round(($primary['duration'] ?? 0) / 60.0, 1);

                        return [
                            'coordinates' => $leafletCoords,
                            'distance_km' => $distKm,
                            'duration_minutes' => $durationMin,
                            'mode' => 'LAND_HIGHWAY',
                            'source' => 'LIVE_OSRM_ROUTING',
                        ];
                    }
                }
            } catch (Exception $e) {
                Log::debug("OSRM routing request failed for [{$lat1},{$lng1}] -> [{$lat2},{$lng2}]: ".$e->getMessage());
            }

            // Fallback interpolation for land
            $distKm = $this->calculateHaversineDistance($lat1, $lng1, $lat2, $lng2);
            $points = $this->generateInterpolatedPath($lat1, $lng1, $lat2, $lng2, 12);

            return [
                'coordinates' => $points,
                'distance_km' => round($distKm * 1.25, 1), // Highway winding factor ~1.25x
                'duration_minutes' => round(($distKm * 1.25 / 60.0) * 60, 1), // Avg 60 km/h
                'mode' => 'LAND_HIGHWAY',
                'source' => 'INTERPOLATED_ROAD_FALLBACK',
            ];
        });
    }

    /**
     * Generate realistic Great Circle (Geodesic Arc) curved aviation flight path.
     * Accurately models spherical earth navigation on 2D map projections.
     *
     * @return array{
     *     coordinates: array<int, array{0: float, 1: float}>,
     *     distance_km: float,
     *     flight_duration_hours: float,
     *     mode: string,
     *     source: string
     * }
     */
    public function getAirRoute(float $lat1, float $lng1, float $lat2, float $lng2, int $numPoints = 30): array
    {
        $distKm = $this->calculateHaversineDistance($lat1, $lng1, $lat2, $lng2);
        $coords = $this->calculateGreatCircleArc($lat1, $lng1, $lat2, $lng2, $numPoints);

        // Average commercial freighter cruising speed ~850 km/h + 45 min takeoff/landing
        $hours = round(($distKm / 850.0) + 0.75, 1);

        return [
            'coordinates' => $coords,
            'distance_km' => round($distKm, 1),
            'flight_duration_hours' => $hours,
            'mode' => 'AIR_GREAT_CIRCLE',
            'source' => 'GEODESIC_ARC_ALGORITHM',
        ];
    }

    /**
     * Generate sea corridor navigation bypassing landmasses.
     *
     * @return array{
     *     coordinates: array<int, array{0: float, 1: float}>,
     *     distance_km: float,
     *     transit_days: float,
     *     mode: string,
     *     source: string
     * }
     */
    public function getSeaRoute(float $lat1, float $lng1, float $lat2, float $lng2): array
    {
        // Strategic maritime checkpoints (Malacca, Indian Ocean, Bab-el-Mandeb, Suez)
        $seaWaypoints = [
            [$lat1, $lng1],
        ];

        // If journey from Indonesia (-10 to 6, 95 to 140) to Middle East / Europe / Africa
        if ($lng1 > 90.0 && $lng2 < 60.0) {
            $seaWaypoints[] = [5.5, 95.3];   // Northern Tip of Sumatra (Malacca Exit)
            $seaWaypoints[] = [6.0, 80.0];   // South of Sri Lanka
            $seaWaypoints[] = [12.0, 52.0];  // Gulf of Aden Entrance
        }

        $seaWaypoints[] = [$lat2, $lng2];

        // Smooth interpolate between sea waypoints
        $fullPath = [];
        for ($i = 0; $i < count($seaWaypoints) - 1; $i++) {
            $seg = $this->calculateGreatCircleArc(
                $seaWaypoints[$i][0],
                $seaWaypoints[$i][1],
                $seaWaypoints[$i + 1][0],
                $seaWaypoints[$i + 1][1],
                10
            );
            $fullPath = array_merge($fullPath, $seg);
        }

        $distKm = $this->calculateHaversineDistance($lat1, $lng1, $lat2, $lng2) * 1.35;
        $transitDays = round($distKm / (19 * 1.852 * 24), 1); // 19 knots avg container speed

        return [
            'coordinates' => $fullPath,
            'distance_km' => round($distKm, 1),
            'transit_days' => max(1.0, $transitDays),
            'mode' => 'MARITIME_SEA_LANE',
            'source' => 'MARITIME_CORRIDOR_NAVIGATION',
        ];
    }

    /**
     * Compute Great Circle arc points using Spherical Linear Interpolation (Slerp).
     */
    protected function calculateGreatCircleArc(float $lat1, float $lng1, float $lat2, float $lng2, int $numPoints = 25): array
    {
        $p1 = deg2rad($lat1);
        $l1 = deg2rad($lng1);
        $p2 = deg2rad($lat2);
        $l2 = deg2rad($lng2);

        // Angular distance
        $d = 2 * asin(sqrt(pow(sin(($p1 - $p2) / 2), 2) + cos($p1) * cos($p2) * pow(sin(($l1 - $l2) / 2), 2)));

        if ($d < 0.0001) {
            return [[$lat1, $lng1], [$lat2, $lng2]];
        }

        $points = [];
        for ($i = 0; $i <= $numPoints; $i++) {
            $f = $i / (float) $numPoints;
            $A = sin((1 - $f) * $d) / sin($d);
            $B = sin($f * $d) / sin($d);

            $x = $A * cos($p1) * cos($l1) + $B * cos($p2) * cos($l2);
            $y = $A * cos($p1) * sin($l1) + $B * cos($p2) * sin($l2);
            $z = $A * sin($p1) + $B * sin($p2);

            $lat = rad2deg(atan2($z, sqrt(pow($x, 2) + pow($y, 2))));
            $lng = rad2deg(atan2($y, $x));

            $points[] = [round($lat, 5), round($lng, 5)];
        }

        return $points;
    }

    /**
     * Haversine distance in kilometers.
     */
    protected function calculateHaversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    /**
     * Simple linear interpolation fallback.
     */
    protected function generateInterpolatedPath(float $lat1, float $lng1, float $lat2, float $lng2, int $steps = 10): array
    {
        $points = [];
        for ($i = 0; $i <= $steps; $i++) {
            $frac = $i / (float) $steps;
            $lat = $lat1 + ($lat2 - $lat1) * $frac;
            $lng = $lng1 + ($lng2 - $lng1) * $frac;
            $points[] = [round($lat, 5), round($lng, 5)];
        }

        return $points;
    }
}
