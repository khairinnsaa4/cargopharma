<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class TrackingService
{
    protected WeatherApiService $weatherService;
    protected EtaService $etaService;

    public function __construct(WeatherApiService $weatherService, EtaService $etaService)
    {
        $this->weatherService = $weatherService;
        $this->etaService = $etaService;
    }

    /**
     * Record a new append-only tracking event with live environmental telemetry.
     */
    public function recordEvent(Shipment $shipment, array $data, ?User $actor = null): TrackingEvent
    {
        // Enforce terminal lock: cannot append events after DELIVERED
        if ($shipment->current_status === 'DELIVERED') {
            throw ValidationException::withMessages([
                'tracking' => ['Cannot attach new tracking events to an already DELIVERED shipment.'],
            ]);
        }

        $eventTime = isset($data['event_time']) ? Carbon::parse($data['event_time']) : Carbon::now();

        // Coordinates resolution: explicit input or location lookup
        $lat = $data['recorded_lat'] ?? null;
        $lng = $data['recorded_lng'] ?? null;
        $location = null;

        if (!empty($data['location_id'])) {
            $location = Location::find($data['location_id']);
            if ($location) {
                $lat = $lat ?? $location->latitude;
                $lng = $lng ?? $location->longitude;
            }
        }

        // Live Weather API query if coordinates are available
        $ambientTemp = $data['ambient_temperature'] ?? null;
        if ($ambientTemp === null && $lat !== null && $lng !== null) {
            $weather = $this->weatherService->getWeatherByCoordinates((float)$lat, (float)$lng);
            $ambientTemp = $weather['temp'] ?? null;
        }

        $containerTemp = isset($data['container_temperature']) ? (float)$data['container_temperature'] : null;

        // Create append-only event
        $event = TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'location_id' => $data['location_id'] ?? null,
            'event_type' => $data['event_type'] ?? 'IN_TRANSIT',
            'status_snapshot' => $data['status_snapshot'] ?? "Checkpoint logged at " . ($location?->name ?? 'transit path'),
            'event_time' => $eventTime,
            'recorded_lat' => $lat,
            'recorded_lng' => $lng,
            'ambient_temperature' => $ambientTemp,
            'container_temperature' => $containerTemp,
            'description' => $data['description'] ?? 'Routine cold-chain verification logged by telemetry station.',
            'created_by' => $actor?->id,
        ]);

        // Evaluate ETA against current event time
        $this->etaService->evaluateDelay($shipment, $eventTime);

        // Check for Cold Chain Temperature Excursion
        if ($containerTemp !== null && ($containerTemp > $shipment->target_temp_max || $containerTemp < $shipment->target_temp_min)) {
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title' => 'COLD CHAIN ALERT: Temperature Excursion',
                'message' => "Sensor recorded {$containerTemp}°C (Safe target: {$shipment->target_temp_min}°C to {$shipment->target_temp_max}°C) for {$shipment->shipment_number}.",
                'type' => 'danger',
                'is_read' => false,
            ]);
        }

        // Audit Trail
        AuditLog::create([
            'user_id' => $actor?->id,
            'action' => 'ADD_TRACKING_EVENT',
            'entity_type' => 'TrackingEvent',
            'entity_id' => $event->id,
            'new_values' => [
                'shipment_number' => $shipment->shipment_number,
                'event_type' => $event->event_type,
                'container_temp' => $containerTemp,
                'ambient_temp' => $ambientTemp,
            ],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent(),
        ]);

        return $event;
    }
}
