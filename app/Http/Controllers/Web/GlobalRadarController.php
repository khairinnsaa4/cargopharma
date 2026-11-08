<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyApiService;
use App\Services\NewsFeedService;
use App\Services\WeatherApiService;
use Illuminate\Http\Request;

class GlobalRadarController extends Controller
{
    public function index(
        Request $request,
        CurrencyApiService $currencyService,
        NewsFeedService $newsService,
        WeatherApiService $weatherService
    ) {
        $currencyData = $currencyService->getRates('IDR');
        $continentalNews = $newsService->getAdvisoriesByContinent();
        $globalWeatherHubs = $weatherService->getGlobalHubsWeather();

        return view('global-radar.index', compact(
            'currencyData',
            'continentalNews',
            'globalWeatherHubs'
        ));
    }
}
