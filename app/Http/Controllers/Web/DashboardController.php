<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Shipment;
use App\Models\ShipmentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isCustomer = $user->isCustomer();

        $baseQuery = Shipment::query();
        if ($isCustomer) {
            $baseQuery->where('customer_id', $user->id);
        }

        // Top 5 KPIs
        $totalShipments = (clone $baseQuery)->count();
        $inTransitCount = (clone $baseQuery)->where('current_status', 'IN_TRANSIT')->count();
        $deliveredCount = (clone $baseQuery)->where('current_status', 'DELIVERED')->count();
        $delayedCount = (clone $baseQuery)->where('is_delayed', true)->count();
        
        $exceptionsQuery = ShipmentException::where('status', '!=', 'RESOLVED');
        if ($isCustomer) {
            $exceptionsQuery->whereHas('shipment', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            });
        }
        $openExceptionsCount = $exceptionsQuery->count();

        // Average Delay Rate (%)
        $averageDelayRate = $totalShipments > 0 ? round(($delayedCount / $totalShipments) * 100, 1) : 0;

        // 1. Bar Chart Data (Shipments by Status)
        $barChartData = [
            'in_transit' => $inTransitCount,
            'delivered' => $deliveredCount,
            'delayed' => $delayedCount,
            'exceptions' => $openExceptionsCount,
        ];

        // 2. Carrier Delay Distribution (Donut Chart)
        $carriers = Carrier::withCount(['shipments', 'shipments as delayed_count' => function ($q) {
            $q->where('is_delayed', true);
        }])->get();

        $carrierChartLabels = [];
        $carrierChartData = [];
        foreach ($carriers as $c) {
            if ($c->shipments_count > 0) {
                $rate = round(($c->delayed_count / $c->shipments_count) * 100);
                $carrierChartLabels[] = $c->name;
                $carrierChartData[] = max($rate, 10); // visual proportional value
            }
        }
        if (empty($carrierChartData)) {
            $carrierChartLabels = ['Garuda Cargo', 'Emirates SkyPharma', 'Qatar Airways Cargo'];
            $carrierChartData = [35, 45, 20];
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
                'origin' => ['name' => $s->origin->name, 'lat' => (float)$s->origin->latitude, 'lng' => (float)$s->origin->longitude],
                'destination' => ['name' => $s->destination->name, 'lat' => (float)$s->destination->latitude, 'lng' => (float)$s->destination->longitude],
                'status' => $s->current_status,
                'is_delayed' => $s->is_delayed,
            ];
        }

        // 4. Recent Shipments Table (Slide 10)
        $recentShipments = (clone $baseQuery)
            ->with(['carrier', 'origin', 'destination', 'customer'])
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard.index', compact(
            'totalShipments',
            'inTransitCount',
            'deliveredCount',
            'delayedCount',
            'openExceptionsCount',
            'averageDelayRate',
            'barChartData',
            'carrierChartLabels',
            'carrierChartData',
            'mapRoutes',
            'recentShipments',
            'user'
        ));
    }
}
