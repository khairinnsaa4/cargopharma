<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;

class EtaService
{
    /**
     * Threshold rules for Cold-Chain Vaccine Logistics:
     * - <= 0 hours: On Time (NONE)
     * - 1 to 6 hours: LOW (Dry-ice cooling safely intact)
     * - >6 to 18 hours: MEDIUM (Re-icing inspection required at transit hub)
     * - >18 hours: HIGH / CRITICAL (Urgent risk of vaccine denaturation)
     */
    public const THRESHOLD_LOW_MAX = 6;
    public const THRESHOLD_MEDIUM_MAX = 18;

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
        $reason = 'Shipment is tracking on schedule within acceptable cold-chain ETA.';

        if ($comparisonTime->greaterThan($expectedArrival)) {
            $isDelayed = true;
            $delayHours = (int) ceil($expectedArrival->diffInHours($comparisonTime, false));

            if ($delayHours <= self::THRESHOLD_LOW_MAX) {
                $severity = 'LOW';
                $reason = "Minor transit variance ({$delayHours}h). Active cooling remains well within nominal power endurance.";
            } elseif ($delayHours <= self::THRESHOLD_MEDIUM_MAX) {
                $severity = 'MEDIUM';
                $reason = "Moderate delay ({$delayHours}h). Secondary dry-ice recharge inspection required at handling airport.";
            } else {
                $severity = 'HIGH';
                $reason = "Critical delay ({$delayHours}h). Cold-chain integrity warning: exceeds battery endurance threshold.";
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
            'expected_arrival' => $expectedArrival->toIso8601String(),
            'current_checkpoint_time' => $comparisonTime->toIso8601String(),
            'reason' => $reason,
        ];
    }
}
