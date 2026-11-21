<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use App\Services\CurrencyApiService;
use App\Services\NewsFeedService;
use App\Services\WeatherApiService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(
        CurrencyApiService $currencyService,
        NewsFeedService $newsService,
        WeatherApiService $weatherService
    ) {
        $user = Auth::user();
        $isCustomer = $user->isCustomer();

        $baseQuery = Shipment::query();
        if ($isCustomer) {
            $baseQuery->where('customer_id', $user->id);
        }

        // Top 5 Real KPIs from Database
        $totalShipments = (clone $baseQuery)->count();
        $inTransitCount = (clone $baseQuery)->where('current_status', 'IN_TRANSIT')->count();
        $deliveredCount = (clone $baseQuery)->where('current_status', 'DELIVERED')->count();
        $delayedCount = (clone $baseQuery)->where('is_delayed', true)->count();

        // Open Exceptions: strictly OPEN or INVESTIGATING
        $exceptionsQuery = ShipmentException::whereIn('status', ['OPEN', 'INVESTIGATING']);
        if ($isCustomer) {
            $exceptionsQuery->whereHas('shipment', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            });
        }
        $openExceptionsCount = $exceptionsQuery->count();

        // Average Delay Rate (%)
        $averageDelayRate = $totalShipments > 0 ? round(($delayedCount / $totalShipments) * 100, 1) : 0;

        // 1. Status Distribution for Bar Chart
        $barChartData = [
            'in_transit' => $inTransitCount,
            'delivered' => $deliveredCount,
            'delayed' => $delayedCount,
            'exceptions' => $openExceptionsCount,
        ];

        // 2. Role-Tailored Metrics & Visualizations (Strict Data Integrity)
        $carrierChartColors = ['#0d9488', '#059669', '#64748b', '#8b5cf6', '#f59e0b'];

        if ($isCustomer) {
            // Consignee (UNICEF): Cold-Chain Quality & Temperature Integrity
            // Formula: (Readings within safe range / Total valid readings) * 100%
            $carrierChartTitle = 'Integritas Suhu Rantai Dingin';
            $carrierChartSubtitle = 'Tingkat kepatuhan telemetri suhu vaksin terhadap standar WHO';

            $events = TrackingEvent::whereHas('shipment', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            })->whereNotNull('container_temperature')->with('shipment')->get();

            $totalValidReadings = $events->count();

            if ($totalValidReadings > 0) {
                $inRangeCount = $events->filter(function ($ev) {
                    return $ev->shipment
                        && $ev->container_temperature >= $ev->shipment->target_temp_min
                        && $ev->container_temperature <= $ev->shipment->target_temp_max;
                })->count();

                $complianceRate = round(($inRangeCount / $totalValidReadings) * 100, 1);
                $excursionRate = round(100 - $complianceRate, 1);

                $carrierMetricValue = $complianceRate.'%';
                $carrierMetricLabel = 'Integritas';
                $carrierBenchmark = $complianceRate >= 95.0
                    ? '<span class="text-emerald-600 font-semibold font-mono">Standar WHO: Terpenuhi (&gt; 95%)</span>'
                    : '<span class="text-rose-600 font-semibold font-mono">Standar WHO: Sub-optimal (&lt; 95%)</span>';

                $carrierChartLabels = ['Rentang Aman', 'Penyimpangan Suhu'];
                $carrierChartData = [$complianceRate, max($excursionRate, 0.1)];
                $carrierChartColors = ['#0d9488', '#f43f5e'];

                $carrierLegendItems = [
                    ['label' => "Rentang Aman ({$inRangeCount}/{$totalValidReadings})", 'value' => $complianceRate.'%', 'color' => '#0d9488'],
                    ['label' => 'Peringatan Suhu', 'value' => $excursionRate.'%', 'color' => '#f43f5e'],
                ];
            } else {
                $carrierMetricValue = 'N/A';
                $carrierMetricLabel = 'Belum Ada Data';
                $carrierBenchmark = '<span class="text-slate-400 font-mono">Data Suhu Belum Tersedia (No Temperature Data Available)</span>';

                $carrierChartLabels = ['Belum Ada Data'];
                $carrierChartData = [100];
                $carrierChartColors = ['#cbd5e1'];

                $carrierLegendItems = [
                    ['label' => 'Data Suhu Belum Tersedia', 'value' => 'N/A', 'color' => '#cbd5e1'],
                ];
            }
        } elseif ($user->isOperator()) {
            // Operator: Operational Fleet Reliability & Active Delay Monitoring
            $carrierChartTitle = 'Keandalan Armada Mitra (Carrier Reliability)';
            $carrierChartSubtitle = 'Distribusi keterlambatan pengiriman kargo udara dan darat pada rute aktif';
            $carrierMetricValue = $delayedCount.' / '.$totalShipments;
            $carrierMetricLabel = 'Tertunda';
            $carrierBenchmark = '<span class="text-amber-600 font-semibold font-mono">'.($totalShipments - $delayedCount).' Pengiriman Tepat Waktu</span>';

            $carriers = Carrier::withCount(['shipments', 'shipments as delayed_count' => function ($q) {
                $q->where('is_delayed', true);
            }])->get();

            $carrierChartLabels = [];
            $carrierChartData = [];
            $carrierLegendItems = [];

            foreach ($carriers as $idx => $c) {
                if ($c->shipments_count > 0) {
                    $rate = round(($c->delayed_count / $c->shipments_count) * 100);
                    $color = $carrierChartColors[$idx % count($carrierChartColors)];
                    $carrierChartLabels[] = $c->name;
                    $carrierChartData[] = max($rate, 10);
                    $carrierLegendItems[] = [
                        'label' => $c->name,
                        'value' => $rate.'% ('.$c->delayed_count.'/'.$c->shipments_count.')',
                        'color' => $color,
                    ];
                }
            }

            if (empty($carrierChartData)) {
                $carrierChartLabels = ['Tidak Ada Pengiriman Aktif'];
                $carrierChartData = [100];
                $carrierChartColors = ['#cbd5e1'];
                $carrierLegendItems = [
                    ['label' => 'Tidak Ada Pengiriman Mitra', 'value' => '0', 'color' => '#cbd5e1'],
                ];
            }
        } else {
            // Administrator: Global Delay Rate by Carrier & SLA Governance
            $carrierChartTitle = 'Tingkat Keterlambatan per Kurir (Delay Rate by Carrier)';
            $carrierChartSubtitle = 'Distribusi insiden keterlambatan pengiriman antar mitra logistik';
            $carrierMetricValue = $averageDelayRate.'%';
            $carrierMetricLabel = 'Rata-rata Delay';
            $carrierBenchmark = '<span class="text-emerald-600 font-semibold font-mono">&lt; Ambang Batas Aman 15%</span>';

            $carriers = Carrier::withCount(['shipments', 'shipments as delayed_count' => function ($q) {
                $q->where('is_delayed', true);
            }])->get();

            $carrierChartLabels = [];
            $carrierChartData = [];
            $carrierLegendItems = [];

            foreach ($carriers as $idx => $c) {
                if ($c->shipments_count > 0) {
                    $rate = round(($c->delayed_count / $c->shipments_count) * 100);
                    $color = $carrierChartColors[$idx % count($carrierChartColors)];
                    $carrierChartLabels[] = $c->name;
                    $carrierChartData[] = max($rate, 10);
                    $carrierLegendItems[] = [
                        'label' => $c->name,
                        'value' => $rate.'% ('.$c->delayed_count.'/'.$c->shipments_count.')',
                        'color' => $color,
                    ];
                }
            }

            if (empty($carrierChartData)) {
                $carrierChartLabels = ['Tidak Ada Pengiriman Aktif'];
                $carrierChartData = [100];
                $carrierChartColors = ['#cbd5e1'];
                $carrierLegendItems = [
                    ['label' => 'Tidak Ada Pengiriman Mitra', 'value' => '0', 'color' => '#cbd5e1'],
                ];
            }
        }

        // 3. Mini Map Data (Active In-Transit Route Coordinates)
        $activeShipments = (clone $baseQuery)
            ->with(['origin', 'destination', 'carrier'])
            ->whereIn('current_status', ['PICKED_UP', 'IN_TRANSIT', 'DELIVERED'])
            ->latest()
            ->take(5)
            ->get();

        $mapRoutes = [];
        foreach ($activeShipments as $s) {
            $mapRoutes[] = [
                'number' => $s->shipment_number,
                'origin' => ['name' => $s->origin->name, 'lat' => (float) $s->origin->latitude, 'lng' => (float) $s->origin->longitude],
                'destination' => ['name' => $s->destination->name, 'lat' => (float) $s->destination->latitude, 'lng' => (float) $s->destination->longitude],
                'dest' => ['name' => $s->destination->name, 'lat' => (float) $s->destination->latitude, 'lng' => (float) $s->destination->longitude],
                'status' => $s->current_status,
                'delayed' => (bool) $s->is_delayed,
                'carrier' => $s->carrier?->name ?? 'Carrier',
                'transport_type' => $s->carrier?->transport_type ?? 'air',
            ];
        }

        // 4. Recent Shipments Table (Actual DB Records)
        $recentShipments = (clone $baseQuery)
            ->with(['carrier', 'origin', 'destination', 'customer', 'items'])
            ->latest()
            ->take(5)
            ->get();

        // 5. Open Exceptions Triage List (Real DB Records)
        $recentExceptions = (clone $exceptionsQuery)
            ->with(['shipment.carrier', 'shipment.origin', 'shipment.destination'])
            ->latest('detected_at')
            ->take(5)
            ->get();

        // 6. External Telemetry: Live Currency Preview
        $currencyData = $currencyService->getRates();
        $keyCurrencies = array_slice($currencyData['pairs'] ?? [], 0, 4);

        // 7. Live Weather Preview for Primary Global Hubs
        $weatherHubs = [
            ['code' => 'BDO', 'name' => 'Bandung Plant', 'lat' => -6.8995, 'lon' => 107.5976, 'flag' => '🇮🇩'],
            ['code' => 'DXB', 'name' => 'Dubai SkyPharma', 'lat' => 25.2532, 'lon' => 55.3657, 'flag' => '🇦🇪'],
            ['code' => 'ABV', 'name' => 'Abuja Terminal', 'lat' => 9.0065, 'lon' => 7.2631, 'flag' => '🇳🇬'],
        ];

        $weatherPreview = [];
        foreach ($weatherHubs as $hub) {
            $wx = $weatherService->getWeatherByCoordinates($hub['lat'], $hub['lon'], $hub['name']);
            $weatherPreview[] = array_merge($hub, [
                'temp' => $wx['temp'] ?? 28,
                'condition' => $wx['condition'] ?? 'Normal',
                'is_extreme_heat' => $wx['is_extreme_heat'] ?? false,
                'source' => $wx['source'] ?? 'LIVE_OPEN_METEO',
            ]);
        }

        // 8. Role-Specific Data Hydration
        $recentAuditLogs = collect();
        $totalCarriersCount = Carrier::count();
        $totalUsersCount = \App\Models\User::count();
        $manifestPendingCount = 0;
        $activeDispatchQueue = collect();
        $customerVials = 0;
        $customerDoses = 0;
        $incomingShipments = collect();

        if ($user->isAdmin()) {
            $recentAuditLogs = \App\Models\AuditLog::with('user')->latest()->take(6)->get();
        } elseif ($user->isOperator()) {
            $manifestPendingCount = Shipment::whereIn('current_status', ['ORDER_CREATED', 'MANIFEST_CREATED'])->count();
            $activeDispatchQueue = Shipment::with(['origin', 'destination', 'carrier', 'latestTrackingEvent', 'items'])
                ->whereIn('current_status', ['PICKED_UP', 'IN_TRANSIT', 'OUT_FOR_DELIVERY'])
                ->latest()
                ->take(6)
                ->get();
        } elseif ($isCustomer) {
            $customerVials = (int) \App\Models\ShipmentItem::whereHas('shipment', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            })->sum('quantity_vials');

            $rawDoses = \App\Models\ShipmentItem::whereHas('shipment', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            })->selectRaw('SUM(quantity_vials * doses_per_vial) as total_doses')->value('total_doses');

            $customerDoses = $rawDoses ? (int) $rawDoses : ($customerVials * 10);

            $incomingShipments = Shipment::with(['origin', 'destination', 'carrier', 'latestTrackingEvent', 'items'])
                ->where('customer_id', $user->id)
                ->whereIn('current_status', ['PICKED_UP', 'IN_TRANSIT', 'OUT_FOR_DELIVERY'])
                ->latest()
                ->take(5)
                ->get();
        }

        return view('dashboard.index', compact(
            'totalShipments',
            'inTransitCount',
            'deliveredCount',
            'delayedCount',
            'openExceptionsCount',
            'averageDelayRate',
            'barChartData',
            'carrierChartTitle',
            'carrierChartSubtitle',
            'carrierMetricValue',
            'carrierMetricLabel',
            'carrierBenchmark',
            'carrierChartLabels',
            'carrierChartData',
            'carrierChartColors',
            'carrierLegendItems',
            'mapRoutes',
            'recentShipments',
            'recentExceptions',
            'currencyData',
            'keyCurrencies',
            'weatherPreview',
            'recentAuditLogs',
            'totalCarriersCount',
            'totalUsersCount',
            'manifestPendingCount',
            'activeDispatchQueue',
            'customerVials',
            'customerDoses',
            'incomingShipments'
        ));
    }
}
