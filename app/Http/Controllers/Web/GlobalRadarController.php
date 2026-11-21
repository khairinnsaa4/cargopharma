<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AviationService;
use App\Services\CurrencyApiService;
use App\Services\NewsFeedService;
use App\Services\SeaportService;
use App\Services\TimeApiService;
use App\Services\WeatherApiService;
use Illuminate\Http\Request;

class GlobalRadarController extends Controller
{
    public function index(
        Request $request,
        CurrencyApiService $currencyService,
        NewsFeedService $newsService,
        WeatherApiService $weatherService,
        TimeApiService $timeService,
        SeaportService $seaportService,
        AviationService $aviationService
    ) {
        $currencyData = $currencyService->getRates('IDR');
        $continentalNews = $newsService->getAdvisoriesByContinent();
        $globalWeatherHubs = $weatherService->getGlobalHubsWeather();
        $globalTimeHubs = $timeService->getGlobalHubsTime();
        $majorSeaports = $seaportService->getMajorPorts();
        $liveFlights = $aviationService->getLiveCargoFlights();
        $majorAirports = $aviationService->getMajorAirports();

        return view('global-radar.index', compact(
            'currencyData',
            'continentalNews',
            'globalWeatherHubs',
            'globalTimeHubs',
            'majorSeaports',
            'liveFlights',
            'majorAirports'
        ));
    }
}
