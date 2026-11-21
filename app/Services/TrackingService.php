<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
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
     * Record a new append-only tracking event with environmental telemetry and automatic breach handling.
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

        if (! empty($data['location_id'])) {
            $location = Location::find($data['location_id']);
            if ($location) {
                $lat = $lat ?? $location->latitude;
                $lng = $lng ?? $location->longitude;
            }
        }

        // Live Weather API query if coordinates are available
        $ambientTemp = $data['ambient_temperature'] ?? null;
        if ($ambientTemp === null && $lat !== null && $lng !== null) {
            $weather = $this->weatherService->getWeatherByCoordinates((float) $lat, (float) $lng);
            $ambientTemp = $weather['temp'] ?? null;
        }

        $containerTemp = isset($data['container_temperature']) ? (float) $data['container_temperature'] : null;

        // Create append-only event
        $event = TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'location_id' => $data['location_id'] ?? null,
            'event_type' => $data['event_type'] ?? 'IN_TRANSIT',
            'status_snapshot' => $data['status_snapshot'] ?? 'Checkpoint logged at '.($location?->name ?? 'transit path'),
            'event_time' => $eventTime,
            'recorded_lat' => $lat,
            'recorded_lng' => $lng,
            'ambient_temperature' => $ambientTemp,
            'container_temperature' => $containerTemp,
            'description' => $data['description'] ?? 'Cold-chain verification logged by telemetry station.',
            'created_by' => $actor?->id,
        ]);

        // Evaluate ETA against current event time
        $this->etaService->evaluateDelay($shipment, $eventTime);

        // Cold Chain Temperature Monitoring & Breach Automation (Phase 2 Requirement 3)
        if ($containerTemp !== null) {
            $isOutOfRange = ($containerTemp > $shipment->target_temp_max || $containerTemp < $shipment->target_temp_min);

            if ($isOutOfRange) {
                // Calculate excursion delta
                $delta = round(max($containerTemp - $shipment->target_temp_max, $shipment->target_temp_min - $containerTemp), 2);

                // Determine severity: <= 2.0C -> LOW, 2.0C - 5.0C -> MEDIUM, > 5.0C -> HIGH
                $severity = 'LOW';
                if ($delta > 5.0) {
                    $severity = 'HIGH';
                } elseif ($delta > 2.0) {
                    $severity = 'MEDIUM';
                }

                // Check for existing unresolved breach exception to avoid duplicates
                $activeBreach = ShipmentException::where('shipment_id', $shipment->id)
                    ->where('exception_type', 'TEMPERATURE_BREACH')
                    ->whereIn('status', ['OPEN', 'INVESTIGATING'])
                    ->first();

                if (! $activeBreach) {
                    ShipmentException::create([
                        'shipment_id' => $shipment->id,
                        'tracking_event_id' => $event->id,
                        'exception_type' => 'TEMPERATURE_BREACH',
                        'severity' => $severity,
                        'status' => 'OPEN',
                        'detected_at' => $eventTime,
                        'root_cause' => "Container temperature ({$containerTemp}°C) breached safe range ({$shipment->target_temp_min}°C to {$shipment->target_temp_max}°C) by {$delta}°C.",
                        'resolution_notes' => null,
                        'resolved_by' => null,
                    ]);
                }

                // Notify Consignee
                Notification::create([
                    'user_id' => $shipment->customer_id,
                    'shipment_id' => $shipment->id,
                    'title' => "PERINGATAN RANTAI DINGIN: Penyimpangan Suhu ({$containerTemp}°C)",
                    'message' => "Sensor mencatat suhu {$containerTemp}°C (Batas aman: {$shipment->target_temp_min}°C s/d {$shipment->target_temp_max}°C) untuk {$shipment->shipment_number}. Deviasi: {$delta}°C [Tingkat: {$severity}].",
                    'type' => 'danger',
                    'is_read' => false,
                ]);

                // Notify Logistics Operator team
                $operators = User::whereIn('role', ['operator', 'admin'])->get();
                foreach ($operators as $op) {
                    Notification::create([
                        'user_id' => $op->id,
                        'shipment_id' => $shipment->id,
                        'title' => "TINDAKAN DIBUTUHKAN: Kebocoran Suhu pada {$shipment->shipment_number}",
                        'message' => "Penyimpangan suhu sebesar {$containerTemp}°C terdeteksi. Deviasi: {$delta}°C [{$severity}]. Mohon segera periksa status kompresor aktif dan dry-ice.",
                        'type' => 'danger',
                        'is_read' => false,
                    ]);
                }
            }
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
