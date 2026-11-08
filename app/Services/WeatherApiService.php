<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherApiService
{
    /**
     * Get real-time weather by latitude and longitude using Open-Meteo (Free, No API Key, Real-Time).
     *
     * @return array{
     *     source: string,
     *     city: string,
     *     country: string,
     *     continent: string,
     *     temp: float,
     *     feels_like: float,
     *     humidity: int,
     *     condition: string,
     *     description: string,
     *     wind_speed: float,
     *     risk_level: string,
     *     risk_badge: string,
     *     cold_chain_guidance: string,
     *     icon: string
     * }
     */
    public function getWeatherByCoordinates(float $lat, float $lon, ?string $cityName = null, ?string $countryName = null, ?string $continentName = null): array
    {
        $cacheKey = 'openmeteo_wx_'.round($lat, 2).'_'.round($lon, 2);

        return Cache::remember($cacheKey, 900, function () use ($lat, $lon, $cityName, $countryName, $continentName): array {
            try {
                $response = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m',
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $current = $json['current'] ?? [];

                    $temp = (float) ($current['temperature_2m'] ?? 25.0);
                    $feelsLike = (float) ($current['apparent_temperature'] ?? $temp);
                    $humidity = (int) ($current['relative_humidity_2m'] ?? 60);
                    $windSpeed = (float) ($current['wind_speed_10m'] ?? 0.0);
                    $weatherCode = (int) ($current['weather_code'] ?? 0);

                    $decoded = $this->decodeWeatherCode($weatherCode);
                    $risk = $this->assessColdChainRisk($temp);

                    return [
                        'source' => 'LIVE_OPEN_METEO',
                        'city' => $cityName ?? 'Coordinates ('.$lat.', '.$lon.')',
                        'country' => $countryName ?? 'Global',
                        'continent' => $continentName ?? 'Global',
                        'temp' => $temp,
                        'feels_like' => $feelsLike,
                        'humidity' => $humidity,
                        'condition' => $decoded['condition'],
                        'description' => $decoded['description'],
                        'wind_speed' => $windSpeed,
                        'is_extreme_heat' => ($temp >= 36.0),
                        'risk_level' => $risk['level'],
                        'risk_badge' => $risk['badge'],
                        'cold_chain_guidance' => $risk['guidance'],
                        'icon' => $decoded['icon'],
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("Open-Meteo Weather API failed for [{$lat}, {$lon}]: ".$e->getMessage());
            }

            return $this->getSimulatedWeatherData($lat, $lon, $cityName, $countryName, $continentName);
        });
    }

    /**
     * Get real-time weather across all major continental logistics hubs & vaccine destinations.
     *
     * @return array<int, array{
     *     city: string,
     *     hub_code: string,
     *     country: string,
     *     continent: string,
     *     flag: string,
     *     temp: float,
     *     feels_like: float,
     *     humidity: int,
     *     condition: string,
     *     wind_speed: float,
     *     risk_level: string,
     *     risk_badge: string,
     *     cold_chain_guidance: string,
     *     icon: string
     * }>
     */
    public function getGlobalHubsWeather(): array
    {
        $hubs = [
            // Asia
            ['city' => 'Bandung (Bio Farma HQ)', 'hub_code' => 'BDO', 'country' => 'Indonesia', 'continent' => 'Asia', 'flag' => '🇮🇩', 'lat' => -6.8915, 'lon' => 107.5983],
            ['city' => 'Jakarta (Soekarno-Hatta)', 'hub_code' => 'CGK', 'country' => 'Indonesia', 'continent' => 'Asia', 'flag' => '🇮🇩', 'lat' => -6.1275, 'lon' => 106.6537],
            ['city' => 'Singapore (Changi Hub)', 'hub_code' => 'SIN', 'country' => 'Singapore', 'continent' => 'Asia', 'flag' => '🇸🇬', 'lat' => 1.3644, 'lon' => 103.9915],
            ['city' => 'Tokyo (Narita Gateway)', 'hub_code' => 'NRT', 'country' => 'Jepang', 'continent' => 'Asia', 'flag' => '🇯🇵', 'lat' => 35.7720, 'lon' => 140.3929],

            // Middle East
            ['city' => 'Dubai (SkyPharma Mega Hub)', 'hub_code' => 'DXB', 'country' => 'Uni Emirat Arab', 'continent' => 'Middle East', 'flag' => '🇦🇪', 'lat' => 25.2532, 'lon' => 55.3657],
            ['city' => 'Doha (Hamad Int. Pharma)', 'hub_code' => 'DOH', 'country' => 'Qatar', 'continent' => 'Middle East', 'flag' => '🇶🇦', 'lat' => 25.2731, 'lon' => 51.6081],
            ['city' => 'Riyadh (King Khalid Int.)', 'hub_code' => 'RUH', 'country' => 'Arab Saudi', 'continent' => 'Middle East', 'flag' => '🇸🇦', 'lat' => 24.9576, 'lon' => 46.6988],

            // Africa (Key Vaccine Destinations)
            ['city' => 'Abuja (Nnamdi Azikiwe)', 'hub_code' => 'ABV', 'country' => 'Nigeria', 'continent' => 'Africa', 'flag' => '🇳🇬', 'lat' => 9.0065, 'lon' => 7.2631],
            ['city' => 'Lagos (Murtala Muhammed)', 'hub_code' => 'LOS', 'country' => 'Nigeria', 'continent' => 'Africa', 'flag' => '🇳🇬', 'lat' => 6.5774, 'lon' => 3.3212],
            ['city' => 'Nairobi (Jomo Kenyatta)', 'hub_code' => 'NBO', 'country' => 'Kenya', 'continent' => 'Africa', 'flag' => '🇰🇪', 'lat' => -1.3192, 'lon' => 36.9275],
            ['city' => 'Johannesburg (O.R. Tambo)', 'hub_code' => 'JNB', 'country' => 'Afrika Selatan', 'continent' => 'Africa', 'flag' => '🇿🇦', 'lat' => -26.1367, 'lon' => 28.2411],
            ['city' => 'Cairo (Cairo International)', 'hub_code' => 'CAI', 'country' => 'Mesir', 'continent' => 'Africa', 'flag' => '🇪🇬', 'lat' => 30.1219, 'lon' => 31.4056],

            // Europe
            ['city' => 'Copenhagen (UNICEF Supply)', 'hub_code' => 'CPH', 'country' => 'Denmark', 'continent' => 'Europe', 'flag' => '🇩🇰', 'lat' => 55.6180, 'lon' => 12.6508],
            ['city' => 'Geneva (WHO Headquarters)', 'hub_code' => 'GVA', 'country' => 'Swiss', 'continent' => 'Europe', 'flag' => '🇨🇭', 'lat' => 46.2370, 'lon' => 6.1092],
            ['city' => 'Amsterdam (Schiphol Cargo)', 'hub_code' => 'AMS', 'country' => 'Belanda', 'continent' => 'Europe', 'flag' => '🇳🇱', 'lat' => 52.3105, 'lon' => 4.7683],

            // Americas
            ['city' => 'New York (JFK Air Cargo)', 'hub_code' => 'JFK', 'country' => 'Amerika Serikat', 'continent' => 'Americas', 'flag' => '🇺🇸', 'lat' => 40.6413, 'lon' => -73.7781],
            ['city' => 'São Paulo (Guarulhos Cargo)', 'hub_code' => 'GRU', 'country' => 'Brasil', 'continent' => 'Americas', 'flag' => '🇧🇷', 'lat' => -23.4356, 'lon' => -46.4731],
        ];

        $results = [];
        foreach ($hubs as $h) {
            $wx = $this->getWeatherByCoordinates($h['lat'], $h['lon'], $h['city'], $h['country'], $h['continent']);
            $results[] = array_merge($h, [
                'temp' => $wx['temp'],
                'feels_like' => $wx['feels_like'],
                'humidity' => $wx['humidity'],
                'condition' => $wx['condition'],
                'wind_speed' => $wx['wind_speed'],
                'risk_level' => $wx['risk_level'],
                'risk_badge' => $wx['risk_badge'],
                'cold_chain_guidance' => $wx['cold_chain_guidance'],
                'icon' => $wx['icon'],
            ]);
        }

        return $results;
    }

    /**
     * Cold-chain risk assessment based on ambient temperature.
     */
    protected function assessColdChainRisk(float $temp): array
    {
        if ($temp >= 36.0) {
            return [
                'level' => 'CRITICAL_HEAT',
                'badge' => 'bg-rose-100 text-rose-800 border-rose-300',
                'guidance' => 'BAHAYA PANAS TARMAC: Suhu >36°C dapat merusak cool pack PCM! Wajib transfer kontainer berpendingin aktif.',
            ];
        }

        if ($temp >= 28.0) {
            return [
                'level' => 'ELEVATED_HEAT',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-300',
                'guidance' => 'WASPADA PANAS: Paparan sinar matahari langsung di bandara dapat menaikkan suhu isolasi. Minimalisir transit di aspal.',
            ];
        }

        if ($temp < 2.0) {
            return [
                'level' => 'FREEZE_RISK',
                'badge' => 'bg-cyan-100 text-cyan-800 border-cyan-300',
                'guidance' => 'RISIKO BEKU: Suhu <2°C dapat membekukan vaksin cair teradsorpsi (DTP/HepB). Aktifkan pemanas selimut kargo.',
            ];
        }

        return [
            'level' => 'SAFE_AMBIENT',
            'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'guidance' => 'KONDISI NOMINAL: Suhu ambien aman untuk penanganan standar rantai dingin (+2°C s/d +8°C).',
        ];
    }

    /**
     * WMO Weather interpretation codes.
     */
    protected function decodeWeatherCode(int $code): array
    {
        return match ($code) {
            0 => ['condition' => 'Cerah (Clear Sky)', 'description' => 'Langit cerah tanpa awan', 'icon' => 'sun'],
            1, 2 => ['condition' => 'Cerah Berawan', 'description' => 'Sebagian besar cerah dengan sedikit awan', 'icon' => 'cloud-sun'],
            3 => ['condition' => 'Mendung (Overcast)', 'description' => 'Langit tertutup awan mendung', 'icon' => 'cloud'],
            45, 48 => ['condition' => 'Berkabut (Fog)', 'description' => 'Kabut tebal, potensi delay jadwal penerbangan', 'icon' => 'cloud-fog'],
            51, 53, 55 => ['condition' => 'Gerimis (Drizzle)', 'description' => 'Hujan gerimis ringan', 'icon' => 'cloud-drizzle'],
            61, 63, 65 => ['condition' => 'Hujan (Rain)', 'description' => 'Hujan air, amankan palet dari kelembapan', 'icon' => 'cloud-rain'],
            71, 73, 75 => ['condition' => 'Salju (Snow)', 'description' => 'Hujan salju beku, waspada pembekuan kargo', 'icon' => 'cloud-snow'],
            80, 81, 82 => ['condition' => 'Hujan Deras', 'description' => 'Hujan lebat disertai angin', 'icon' => 'cloud-rain'],
            95, 96, 99 => ['condition' => 'Badai Petir (Thunderstorm)', 'description' => 'Badai petir ekstrem, ground handling dihentikan', 'icon' => 'cloud-lightning'],
            default => ['condition' => 'Normal', 'description' => 'Kondisi cuaca terkendali', 'icon' => 'cloud-sun'],
        };
    }

    /**
     * Realistic simulated weather fallback.
     */
    protected function getSimulatedWeatherData(float $lat, float $lon, ?string $cityName, ?string $countryName, ?string $continentName): array
    {
        // Estimate based on latitude
        $temp = 28.0;
        if ($lat > 20.0 && $lat < 30.0 && $lon > 45.0 && $lon < 65.0) {
            $temp = 38.5; // Middle East
        } elseif ($lat > 50.0) {
            $temp = 8.5; // Northern Europe
        } elseif ($lat > -8.0 && $lat < 10.0) {
            $temp = 30.5; // Equatorial Africa / Asia
        }

        $risk = $this->assessColdChainRisk($temp);

        return [
            'source' => 'SIMULATED_CLIMATE',
            'city' => $cityName ?? 'Global Node',
            'country' => $countryName ?? 'International',
            'continent' => $continentName ?? 'Global',
            'temp' => $temp,
            'feels_like' => $temp + 2.0,
            'humidity' => 55,
            'condition' => 'Cerah Terkendali',
            'description' => 'Estimasi iklim geografis standar',
            'wind_speed' => 12.0,
            'is_extreme_heat' => ($temp >= 36.0),
            'risk_level' => $risk['level'],
            'risk_badge' => $risk['badge'],
            'cold_chain_guidance' => $risk['guidance'],
            'icon' => 'sun',
        ];
    }
}
