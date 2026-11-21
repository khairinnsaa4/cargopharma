<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;

class EtaService
{
    /**
     * Threshold rules for Cold-Chain Vaccine Logistics:
     * - <= 0 hours: ON_TIME (DB: NONE)
     * - >0 to 6 hours: LOW
     * - >6 to 24 hours: MEDIUM
     * - >24 hours: HIGH (Critical threshold)
     */
    public const THRESHOLD_LOW_MAX = 6;

    public const THRESHOLD_MEDIUM_MAX = 24;

    /**
     * Calculate delay and update shipment state deterministically.
     */
    public function evaluateDelay(Shipment $shipment, ?Carbon $comparisonTime = null): array
    {
        $comparisonTime = $comparisonTime ?? Carbon::now();
        $expectedArrival = Carbon::parse($shipment->expected_arrival);

        // If already delivered, compare actual_arrival against expected_arrival
        if ($shipment->current_status === 'DELIVERED' && $shipment->actual_arrival) {
            $comparisonTime = Carbon::parse($shipment->actual_arrival);
        }

        $delayHours = 0;
        $isDelayed = false;
        $severity = 'NONE';
        $statusLabel = 'ON_TIME';
        $reason = 'Shipment is tracking on schedule within acceptable cold-chain ETA.';

        if ($comparisonTime->greaterThan($expectedArrival)) {
            $isDelayed = true;
            $delayHours = (int) ceil($expectedArrival->diffInHours($comparisonTime, false));

            if ($delayHours <= self::THRESHOLD_LOW_MAX) {
                $severity = 'LOW';
                $statusLabel = 'LOW';
                $reason = "Minor transit variance ({$delayHours}h). Active cooling remains within nominal endurance.";
            } elseif ($delayHours <= self::THRESHOLD_MEDIUM_MAX) {
                $severity = 'MEDIUM';
                $statusLabel = 'MEDIUM';
                $reason = "Moderate delay ({$delayHours}h). Secondary dry-ice recharge inspection required at handling airport.";
            } else {
                $severity = 'HIGH';
                $statusLabel = 'HIGH';
                $reason = "Critical delay ({$delayHours}h). Exceeds 24-hour safety threshold. Risk of cold-chain failure.";
            }
        }

        // Apply changes to database
        $shipment->update([
            'is_delayed' => $isDelayed,
            'delay_duration_hours' => $delayHours,
            'delay_severity' => $severity,
        ]);

        return [
            'is_delayed' => $isDelayed,
            'delay_duration_hours' => $delayHours,
            'delay_severity' => $severity,
            'status_label' => $statusLabel,
            'expected_arrival' => $expectedArrival->toIso8601String(),
            'current_checkpoint_time' => $comparisonTime->toIso8601String(),
            'reason' => $reason,
        ];
    }
}
