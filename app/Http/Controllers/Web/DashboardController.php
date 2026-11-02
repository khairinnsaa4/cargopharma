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

        // Calculate Delay Rate (%)
        $delayRate = $totalShipments > 0 ? round(($delayedCount / $totalShipments) * 100, 1) : 0;

        // Status Distribution for Charts
        $statusDistribution = [
            'CREATED' => (clone $baseQuery)->where('current_status', 'CREATED')->count(),
            'BOOKED' => (clone $baseQuery)->where('current_status', 'BOOKED')->count(),
            'PICKED_UP' => (clone $baseQuery)->where('current_status', 'PICKED_UP')->count(),
            'IN_TRANSIT' => $inTransitCount,
            'DELIVERED' => $deliveredCount,
        ];

        // Active Shipments for the Live Monitor Table
        $recentShipments = (clone $baseQuery)
            ->with(['carrier', 'origin', 'destination', 'customer', 'latestTrackingEvent'])
            ->latest()
            ->take(6)
            ->get();

        // Active Critical Incidents
        $activeExceptions = $exceptionsQuery
            ->with(['shipment.origin', 'shipment.destination'])
            ->latest('detected_at')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalShipments',
            'inTransitCount',
            'deliveredCount',
            'delayedCount',
            'openExceptionsCount',
            'delayRate',
            'statusDistribution',
            'recentShipments',
            'activeExceptions',
            'user'
        ));
    }
}
