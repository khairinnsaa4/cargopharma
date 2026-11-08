<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingPageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Shipment::with([
            'carrier',
            'origin',
            'destination',
            'customer',
            'items',
            'latestTrackingEvent',
            'trackingEvents.location',
        ]);

        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('shipment_number', 'like', "%{$keyword}%")
                    ->orWhere('cargo_type', 'like', "%{$keyword}%")
                    ->orWhereHas('items', function ($itemQuery) use ($keyword) {
                        $itemQuery->where('product_name', 'like', "%{$keyword}%")
                            ->orWhere('batch_number', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('q')) {
            $activeShipments = (clone $query)->get();
        } else {
            $activeShipments = (clone $query)->whereIn('current_status', ['PICKED_UP', 'IN_TRANSIT'])->get();
            if ($activeShipments->isEmpty()) {
                $activeShipments = (clone $query)->latest()->take(3)->get();
            }
        }

        $allShipments = (clone $query)->latest()->paginate(10)->withQueryString();

        // Transform active shipments into structured JSON payload for Leaflet map
        $mapShipmentsData = $activeShipments->map(function ($s) {
            $firstItem = $s->items->first();
            $latestEvent = $s->latestTrackingEvent;

            $waypoints = [];
            if ($s->origin) {
                $waypoints[] = [
                    'type' => 'ORIGIN',
                    'name' => $s->origin->name,
                    'city' => $s->origin->city,
                    'country' => $s->origin->country,
                    'code' => $s->origin->code,
                    'lat' => (float) $s->origin->latitude,
                    'lng' => (float) $s->origin->longitude,
                    'time' => $s->actual_departure ? $s->actual_departure->format('d M Y H:i') : ($s->expected_departure ? $s->expected_departure->format('d M Y H:i') : null),
                ];
            }

            foreach ($s->trackingEvents as $ev) {
                if ($ev->recorded_lat !== null && $ev->recorded_lng !== null) {
                    $waypoints[] = [
                        'type' => 'CHECKPOINT',
                        'id' => $ev->id,
                        'event_type' => $ev->event_type,
                        'name' => $ev->location ? $ev->location->name : 'Transit Waypoint',
                        'city' => $ev->location ? $ev->location->city : '',
                        'country' => $ev->location ? $ev->location->country : '',
                        'code' => $ev->location ? $ev->location->code : 'CP',
                        'lat' => (float) $ev->recorded_lat,
                        'lng' => (float) $ev->recorded_lng,
                        'time' => $ev->event_time ? $ev->event_time->format('d M Y H:i') : null,
                        'status_snapshot' => $ev->status_snapshot,
                        'description' => $ev->description,
                        'container_temp' => $ev->container_temperature,
                        'ambient_temp' => $ev->ambient_temperature,
                    ];
                }
            }

            if ($s->destination) {
                $waypoints[] = [
                    'type' => 'DESTINATION',
                    'name' => $s->destination->name,
                    'city' => $s->destination->city,
                    'country' => $s->destination->country,
                    'code' => $s->destination->code,
                    'lat' => (float) $s->destination->latitude,
                    'lng' => (float) $s->destination->longitude,
                    'time' => $s->expected_arrival ? $s->expected_arrival->format('d M Y H:i') : null,
                ];
            }

            $currentLat = $latestEvent && $latestEvent->recorded_lat !== null ? (float) $latestEvent->recorded_lat : (float) $s->origin?->latitude;
            $currentLng = $latestEvent && $latestEvent->recorded_lng !== null ? (float) $latestEvent->recorded_lng : (float) $s->origin?->longitude;

            $targetLat = (float) $s->destination?->latitude;
            $targetLng = (float) $s->destination?->longitude;

            return [
                'id' => $s->id,
                'shipment_number' => $s->shipment_number,
                'cargo_type' => $s->cargo_type,
                'current_status' => $s->current_status,
                'is_delayed' => (bool) $s->is_delayed,
                'delay_duration_hours' => (int) $s->delay_duration_hours,
                'delay_severity' => $s->delay_severity,
                'target_temp_min' => (float) $s->target_temp_min,
                'target_temp_max' => (float) $s->target_temp_max,
                'total_weight_kg' => (float) $s->total_weight_kg,
                'total_volume_cbm' => (float) $s->total_volume_cbm,
                'expected_arrival' => $s->expected_arrival ? $s->expected_arrival->format('d M Y H:i') : 'TBD',
                'carrier' => [
                    'name' => $s->carrier?->name ?? 'Global Carrier',
                    'code' => $s->carrier?->code ?? 'CARRIER',
                    'transport_type' => strtolower($s->carrier?->transport_type ?? 'air'),
                ],
                'customer' => [
                    'name' => $s->customer?->name ?? 'Recipient',
                    'organization' => $s->customer?->organization ?? 'Global Health Organization',
                ],
                'origin' => [
                    'name' => $s->origin?->name,
                    'city' => $s->origin?->city,
                    'country' => $s->origin?->country,
                    'code' => $s->origin?->code,
                    'lat' => (float) $s->origin?->latitude,
                    'lng' => (float) $s->origin?->longitude,
                ],
                'destination' => [
                    'name' => $s->destination?->name,
                    'city' => $s->destination?->city,
                    'country' => $s->destination?->country,
                    'code' => $s->destination?->code,
                    'lat' => (float) $s->destination?->latitude,
                    'lng' => (float) $s->destination?->longitude,
                ],
                'primary_item' => $firstItem ? [
                    'product_name' => $firstItem->product_name,
                    'item_code' => $firstItem->item_code,
                    'batch_number' => $firstItem->batch_number,
                    'quantity_vials' => (int) $firstItem->quantity_vials,
                    'doses_per_vial' => (int) $firstItem->doses_per_vial,
                    'total_doses' => (int) ($firstItem->quantity_vials * $firstItem->doses_per_vial),
                    'container_type' => $firstItem->container_type,
                    'expiry_date' => $firstItem->expiry_date ? $firstItem->expiry_date->format('d M Y') : 'N/A',
                ] : null,
                'items_count' => $s->items->count(),
                'all_items' => $s->items->map(fn ($item) => [
                    'product_name' => $item->product_name,
                    'item_code' => $item->item_code,
                    'batch_number' => $item->batch_number,
                    'quantity_vials' => (int) $item->quantity_vials,
                    'doses_per_vial' => (int) $item->doses_per_vial,
                    'total_doses' => (int) ($item->quantity_vials * $item->doses_per_vial),
                    'container_type' => $item->container_type,
                    'expiry_date' => $item->expiry_date ? $item->expiry_date->format('d M Y') : 'N/A',
                ])->values()->all(),
                'latest_event' => $latestEvent ? [
                    'event_type' => $latestEvent->event_type,
                    'status_snapshot' => $latestEvent->status_snapshot,
                    'event_time' => $latestEvent->event_time ? $latestEvent->event_time->format('d M Y H:i') : null,
                    'description' => $latestEvent->description,
                    'container_temp' => $latestEvent->container_temperature,
                    'ambient_temp' => $latestEvent->ambient_temperature,
                ] : null,
                'current_vehicle_position' => [
                    'lat' => $currentLat,
                    'lng' => $currentLng,
                    'target_lat' => $targetLat,
                    'target_lng' => $targetLng,
                ],
                'waypoints' => $waypoints,
                'show_url' => route('shipments.show', $s->id),
            ];
        })->values();

        return view('tracking.index', compact('activeShipments', 'allShipments', 'mapShipmentsData'));
    }
}
