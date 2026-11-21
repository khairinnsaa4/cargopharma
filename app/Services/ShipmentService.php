<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    protected EtaService $etaService;

    public function __construct(EtaService $etaService)
    {
        $this->etaService = $etaService;
    }

    /**
     * Permitted state transitions (Strict State Machine).
     * CREATED -> BOOKED -> PICKED_UP -> IN_TRANSIT -> DELIVERED.
     */
    protected array $allowedTransitions = [
        'CREATED' => ['BOOKED', 'CANCELLED'],
        'BOOKED' => ['PICKED_UP', 'CANCELLED'],
        'PICKED_UP' => ['IN_TRANSIT'],
        'IN_TRANSIT' => ['DELIVERED'],
        'DELIVERED' => [], // Terminal state, locked
        'CANCELLED' => [], // Terminal state, locked
    ];

    /**
     * Validate and advance shipment status with atomic tracking event creation.
     */
    public function updateStatus(Shipment $shipment, string $newStatus, ?User $actor = null, ?string $notes = null): Shipment
    {
        $currentStatus = $shipment->current_status;

        // Terminal state lock
        if ($currentStatus === 'DELIVERED') {
            throw ValidationException::withMessages([
                'status' => ["Shipment {$shipment->shipment_number} has already been DELIVERED. Terminal status cannot be altered without high-clearance override."],
            ]);
        }

        if ($currentStatus === 'CANCELLED') {
            throw ValidationException::withMessages([
                'status' => ['Cancelled shipments cannot be reactivated.'],
            ]);
        }

        // Validate state machine forward progression
        $validTargets = $this->allowedTransitions[$currentStatus] ?? [];
        if (! in_array($newStatus, $validTargets, true)) {
            throw ValidationException::withMessages([
                'status' => ["Invalid status transition from '{$currentStatus}' to '{$newStatus}'. Allowed next steps: ".implode(', ', $validTargets)],
            ]);
        }

        return DB::transaction(function () use ($shipment, $currentStatus, $newStatus, $actor, $notes) {
            $now = Carbon::now();

            $oldValues = [
                'status' => $currentStatus,
                'actual_departure' => $shipment->actual_departure?->toIso8601String(),
                'actual_arrival' => $shipment->actual_arrival?->toIso8601String(),
            ];

            // Status-specific timestamp updates
            if (in_array($newStatus, ['PICKED_UP', 'IN_TRANSIT']) && empty($shipment->actual_departure)) {
                $shipment->actual_departure = $now;
            }

            if ($newStatus === 'DELIVERED') {
                $shipment->actual_arrival = $now;
            }

            $shipment->current_status = $newStatus;
            if ($notes) {
                $shipment->notes = $notes;
            }
            $shipment->save();

            // Determine appropriate location context for automated tracking event
            $locationId = null;
            $lat = null;
            $lng = null;

            if (in_array($newStatus, ['CREATED', 'BOOKED', 'PICKED_UP'])) {
                $locationId = $shipment->origin_location_id;
                $lat = $shipment->origin?->latitude;
                $lng = $shipment->origin?->longitude;
            } elseif ($newStatus === 'DELIVERED') {
                $locationId = $shipment->destination_location_id;
                $lat = $shipment->destination?->latitude;
                $lng = $shipment->destination?->longitude;
            } else {
                // IN_TRANSIT: take latest known event coordinate or origin
                $latest = $shipment->latestTrackingEvent;
                $locationId = $latest?->location_id ?? $shipment->origin_location_id;
                $lat = $latest?->recorded_lat ?? $shipment->origin?->latitude;
                $lng = $latest?->recorded_lng ?? $shipment->origin?->longitude;
            }

            // Automatic Tracking Event generation (Phase 2 Requirement 2)
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'location_id' => $locationId,
                'event_type' => $newStatus,
                'status_snapshot' => "Peralihan konsinyasi ke {$newStatus}",
                'event_time' => $now,
                'recorded_lat' => $lat,
                'recorded_lng' => $lng,
                'ambient_temperature' => null,
                'container_temperature' => null,
                'description' => "Status otomatis dialihkan dari {$currentStatus} ke {$newStatus} oleh ".($actor?->name ?? 'Sistem').($notes ? ". Catatan: {$notes}" : ''),
                'created_by' => $actor?->id,
            ]);

            // Re-evaluate ETA and delay status
            $this->etaService->evaluateDelay($shipment);

            // Audit Log Trail
            AuditLog::create([
                'user_id' => $actor?->id,
                'action' => 'STATUS_TRANSITION',
                'entity_type' => 'Shipment',
                'entity_id' => $shipment->id,
                'old_values' => $oldValues,
                'new_values' => [
                    'status' => $newStatus,
                    'actual_departure' => $shipment->actual_departure?->toIso8601String(),
                    'actual_arrival' => $shipment->actual_arrival?->toIso8601String(),
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent(),
            ]);

            // Consignee In-App Notification
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title' => "Pengiriman {$shipment->shipment_number}: {$newStatus}",
                'message' => "Konsinyasi vaksin rantai dingin Anda telah beralih status ke '{$newStatus}'. Titik pemeriksaan telemetri otomatis dicatat.",
                'type' => $newStatus === 'DELIVERED' ? 'info' : 'warning',
                'is_read' => false,
            ]);

            return $shipment->fresh();
        });
    }
}
