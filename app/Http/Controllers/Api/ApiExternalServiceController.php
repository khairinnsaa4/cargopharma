<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CurrencyApiService;
use App\Services\GeocodingService;
use App\Services\NewsFeedService;
use App\Services\WeatherApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiExternalServiceController extends Controller
{
    /**
     * Get real-time foreign currency exchange rates (ECB / Frankfurter API).
     */
    public function getCurrencyRates(Request $request, CurrencyApiService $currencyService): JsonResponse
    {
        $base = $request->input('base', 'USD');
        $data = $currencyService->getRates($base);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Live geocoding search using OpenStreetMap Nominatim.
     */
    public function getGeocode(Request $request, GeocodingService $geocodingService): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $results = $geocodingService->search($request->input('q'));

        return response()->json([
            'success' => true,
            'query' => $request->input('q'),
            'count' => count($results),
            'data' => $results,
        ]);
    }

    /**
     * Get real-time cold-chain & humanitarian logistics advisories (ReliefWeb API).
     */
    public function getNewsFeed(NewsFeedService $newsService): JsonResponse
    {
        $advisories = $newsService->getAdvisories();

        return response()->json([
            'success' => true,
            'count' => count($advisories),
            'data' => $advisories,
        ]);
    }

    /**
     * Get real-time atmospheric weather for coordinates (OpenWeather API).
     */
    public function getWeather(Request $request, WeatherApiService $weatherService): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $weather = $weatherService->getWeatherByCoordinates(
            (float) $request->input('lat'),
            (float) $request->input('lng')
        );

        return response()->json([
            'success' => true,
            'data' => $weather,
        ]);
    }
}
