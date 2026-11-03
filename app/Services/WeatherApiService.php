<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherApiService
{
    protected ?string $apiKey;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openweather.key', env('OPENWEATHER_API_KEY'));
        $this->baseUrl = 'https://api.openweathermap.org/data/2.5';
    }

    /**
     * Get real-time weather by latitude and longitude.
     * Handled with graceful fallback if external API is unreachable or key is unset.
     */
    public function getWeatherByCoordinates(float $lat, float $lon): array
    {
        if (empty($this->apiKey)) {
            return $this->getSimulatedWeatherData($lat, $lon, 'API Key not configured');
        }

        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $this->apiKey,
                'units' => 'metric',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'source' => 'LIVE_API',
                    'city' => $data['name'] ?? 'Unknown Location',
                    'temp' => (float) ($data['main']['temp'] ?? 25.0),
                    'feels_like' => (float) ($data['main']['feels_like'] ?? 25.0),
                    'humidity' => (int) ($data['main']['humidity'] ?? 60),
                    'condition' => $data['weather'][0]['main'] ?? 'Clear',
                    'description' => $data['weather'][0]['description'] ?? 'clear sky',
                    'wind_speed' => (float) ($data['wind']['speed'] ?? 0.0),
                    'is_extreme_heat' => ($data['main']['temp'] ?? 25.0) >= 38.0,
                ];
            }

            Log::warning("OpenWeather API failed with status {$response->status()}: {$response->body()}");

            return $this->getSimulatedWeatherData($lat, $lon, 'Live API Error '.$response->status());
        } catch (\Throwable $e) {
            Log::error("Weather API Connection Exception: {$e->getMessage()}");

            return $this->getSimulatedWeatherData($lat, $lon, 'Network Timeout/Exception');
        }
    }

    /**
     * Fallback for realistic temperature based on coordinate latitude (e.g. Dubai heat vs Bandung temperate).
     */
    protected function getSimulatedWeatherData(float $lat, float $lon, string $reason): array
    {
        // Dubai / Middle East Hub (~25 lat)
        if ($lat > 20.0 && $lat < 30.0 && $lon > 45.0 && $lon < 65.0) {
            $temp = 41.5;
            $condition = 'Extreme Heat';
            $city = 'Dubai Int Airport (DXB)';
        } elseif ($lat < -5.0 && $lat > -8.0 && $lon > 105.0 && $lon < 110.0) {
            // Bandung / Jakarta (~ -6.9 lat)
            $temp = 26.5;
            $condition = 'Partly Cloudy';
            $city = 'Bandung / CGK Region';
        } else {
            // Abuja, Nigeria (~9.0 lat)
            $temp = 32.0;
            $condition = 'Sunny';
            $city = 'Abuja Region (Nigeria)';
        }

        return [
            'source' => 'SIMULATED_FALLBACK',
            'city' => $city,
            'temp' => $temp,
            'feels_like' => $temp + 2.0,
            'humidity' => 45,
            'condition' => $condition,
            'description' => "Atmospheric reading active ({$reason})",
            'wind_speed' => 12.5,
            'is_extreme_heat' => $temp >= 38.0,
        ];
    }
}
