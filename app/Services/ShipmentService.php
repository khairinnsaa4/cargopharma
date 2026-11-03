<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    protected EtaService $etaService;

    public function __construct(EtaService $etaService)
    {
        $this->etaService = $etaService;
    }

    /**
     * Permitted state transitions.
     */
    protected array $allowedTransitions = [
        'CREATED' => ['BOOKED', 'CANCELLED'],
        'BOOKED' => ['PICKED_UP', 'CANCELLED'],
        'PICKED_UP' => ['IN_TRANSIT'],
        'IN_TRANSIT' => ['DELIVERED'],
        'DELIVERED' => [], // Terminal state, locked
        'CANCELLED' => [], // Terminal state
    ];

    /**
     * Validate and advance shipment status.
     */
    public function updateStatus(Shipment $shipment, string $newStatus, ?User $actor = null, ?string $notes = null): Shipment
    {
        $currentStatus = $shipment->current_status;

        // Terminal state lock (Slide 6)
        if ($currentStatus === 'DELIVERED') {
            throw ValidationException::withMessages([
                'status' => ["Shipment {$shipment->shipment_number} has been DELIVERED. Terminal status cannot be altered without high-clearance override."],
            ]);
        }

        if ($currentStatus === 'CANCELLED') {
            throw ValidationException::withMessages([
                'status' => ['Cancelled shipments cannot be reactivated.'],
            ]);
        }

        // Validate valid forward transition
        $validTargets = $this->allowedTransitions[$currentStatus] ?? [];
        if (! in_array($newStatus, $validTargets, true)) {
            throw ValidationException::withMessages([
                'status' => ["Invalid status transition from '{$currentStatus}' to '{$newStatus}'. Allowed: ".implode(', ', $validTargets)],
            ]);
        }

        $oldValues = [
            'status' => $currentStatus,
            'actual_departure' => $shipment->actual_departure?->toIso8601String(),
            'actual_arrival' => $shipment->actual_arrival?->toIso8601String(),
        ];

        // Status-specific updates
        if (in_array($newStatus, ['PICKED_UP', 'IN_TRANSIT']) && empty($shipment->actual_departure)) {
            $shipment->actual_departure = Carbon::now();
        }

        if ($newStatus === 'DELIVERED') {
            $shipment->actual_arrival = Carbon::now();
        }

        $shipment->current_status = $newStatus;
        if ($notes) {
            $shipment->notes = $notes;
        }
        $shipment->save();

        // Evaluate ETA and delay status automatically
        $this->etaService->evaluateDelay($shipment);

        // Record Audit Log (Slide 10)
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

        // Send In-App notification to customer
        Notification::create([
            'user_id' => $shipment->customer_id,
            'shipment_id' => $shipment->id,
            'title' => "Shipment {$shipment->shipment_number}: {$newStatus}",
            'message' => "Your cold-chain shipment has transitioned to '{$newStatus}'. Check timeline for updated telemetry.",
            'type' => $newStatus === 'DELIVERED' ? 'info' : 'warning',
            'is_read' => false,
        ]);

        return $shipment->fresh();
    }
}
