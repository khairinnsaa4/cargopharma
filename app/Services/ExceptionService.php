<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ExceptionService
{
    /**
     * Raise a new shipment exception/incident.
     */
    public function logException(Shipment $shipment, array $data, ?User $actor = null): ShipmentException
    {
        $exception = ShipmentException::create([
            'shipment_id' => $shipment->id,
            'tracking_event_id' => $data['tracking_event_id'] ?? null,
            'exception_type' => $data['exception_type'] ?? 'WEATHER_DELAY',
            'severity' => $data['severity'] ?? 'MEDIUM',
            'status' => 'OPEN',
            'detected_at' => isset($data['detected_at']) ? Carbon::parse($data['detected_at']) : Carbon::now(),
            'root_cause' => $data['root_cause'] ?? 'Unscheduled variance during active transit.',
            'resolution_notes' => null,
            'resolved_by' => null,
        ]);

        // Audit Trail
        AuditLog::create([
            'user_id' => $actor?->id,
            'action' => 'EXCEPTION_RAISED',
            'entity_type' => 'ShipmentException',
            'entity_id' => $exception->id,
            'new_values' => [
                'shipment_number' => $shipment->shipment_number,
                'type' => $exception->exception_type,
                'severity' => $exception->severity,
            ],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent(),
        ]);

        // Broadcast notifications
        Notification::create([
            'user_id' => $shipment->customer_id,
            'shipment_id' => $shipment->id,
            'title' => "Incident Logged: {$exception->exception_type}",
            'message' => "Shipment {$shipment->shipment_number} flagged for {$exception->exception_type} (Severity: {$exception->severity}). Cold-chain team is investigating.",
            'type' => $exception->severity === 'HIGH' ? 'danger' : 'warning',
            'is_read' => false,
        ]);

        return $exception;
    }

    /**
     * Update exception status (INVESTIGATING or RESOLVED).
     */
    public function resolveException(ShipmentException $exception, string $newStatus, string $resolutionNotes, ?User $actor = null): ShipmentException
    {
        if ($exception->status === 'RESOLVED') {
            throw ValidationException::withMessages([
                'exception' => ['This incident is already RESOLVED.'],
            ]);
        }

        $isClosing = ($newStatus === 'RESOLVED');

        $exception->update([
            'status' => $newStatus,
            'resolution_notes' => $resolutionNotes,
            'resolved_at' => $isClosing ? Carbon::now() : null,
            'resolved_by' => $isClosing ? $actor?->id : $exception->resolved_by,
        ]);

        AuditLog::create([
            'user_id' => $actor?->id,
            'action' => 'EXCEPTION_' . $newStatus,
            'entity_type' => 'ShipmentException',
            'entity_id' => $exception->id,
            'new_values' => [
                'status' => $newStatus,
                'resolution_notes' => $resolutionNotes,
                'resolved_by' => $actor?->name ?? 'System',
            ],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent(),
        ]);

        return $exception;
    }
}
