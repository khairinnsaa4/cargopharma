<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AviationService;
use App\Services\CurrencyApiService;
use App\Services\GeocodingService;
use App\Services\NewsFeedService;
use App\Services\RoutingService;
use App\Services\SeaportService;
use App\Services\TimeApiService;
use App\Services\WeatherApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiExternalServiceController extends Controller
{
    /**
     * Get real-time foreign currency exchange rates (ECB / Open Exchange Rates API).
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
     * Get real-time atmospheric weather for coordinates (Open-Meteo API).
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

    /**
     * Get real-time local time and timezone for coordinates.
     */
    public function getTime(Request $request, TimeApiService $timeService): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $timeData = $timeService->getTimeForCoordinates(
            (float) $request->input('lat'),
            (float) $request->input('lng')
        );

        return response()->json([
            'success' => true,
            'data' => $timeData,
        ]);
    }

    /**
     * Get world clock times for key international cargo hubs.
     */
    public function getGlobalTimeHubs(TimeApiService $timeService): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $timeService->getGlobalHubsTime(),
        ]);
    }

    /**
     * Get global seaport list with UN/LOCODE.
     */
    public function getSeaports(Request $request, SeaportService $seaportService): JsonResponse
    {
        $q = $request->input('q', '');
        $ports = $seaportService->searchPorts($q);

        return response()->json([
            'success' => true,
            'count' => count($ports),
            'data' => $ports,
        ]);
    }

    /**
     * Get live air cargo flights and international airport hubs.
     */
    public function getAviation(Request $request, AviationService $aviationService): JsonResponse
    {
        $flights = $aviationService->getLiveCargoFlights();
        $airports = $aviationService->getMajorAirports();

        return response()->json([
            'success' => true,
            'flights_count' => count($flights),
            'airports_count' => count($airports),
            'flights' => $flights,
            'airports' => $airports,
        ]);
    }

    /**
     * Calculate multimodal route (land highway via OSRM, or great circle air arc).
     */
    public function getRoute(Request $request, RoutingService $routingService): JsonResponse
    {
        $request->validate([
            'lat1' => 'required|numeric|between:-90,90',
            'lng1' => 'required|numeric|between:-180,180',
            'lat2' => 'required|numeric|between:-90,90',
            'lng2' => 'required|numeric|between:-180,180',
            'mode' => 'nullable|string|in:land,air,sea',
        ]);

        $lat1 = (float) $request->input('lat1');
        $lng1 = (float) $request->input('lng1');
        $lat2 = (float) $request->input('lat2');
        $lng2 = (float) $request->input('lng2');
        $mode = strtolower($request->input('mode', 'land'));

        $route = match ($mode) {
            'air' => $routingService->getAirRoute($lat1, $lng1, $lat2, $lng2),
            'sea' => $routingService->getSeaRoute($lat1, $lng1, $lat2, $lng2),
            default => $routingService->getRoadRoute($lat1, $lng1, $lat2, $lng2),
        };

        return response()->json([
            'success' => true,
            'mode' => $mode,
            'route' => $route,
        ]);
    }
}
