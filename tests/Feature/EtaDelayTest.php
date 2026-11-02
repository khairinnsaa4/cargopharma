<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\EtaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EtaDelayTest extends TestCase
{
    use RefreshDatabase;
    public function test_eta_delay_calculation_and_severity_thresholds(): void
    {
        $customer = User::create([
            'name' => 'Customer Tester',
            'email' => 'customer_test2@cargotrace.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $carrier = Carrier::create([
            'code' => 'TEST-AIR',
            'name' => 'Emirates Test Cargo',
            'transport_type' => 'air',
        ]);

        $origin = Location::create([
            'code' => 'TEST-BDO-2',
            'name' => 'Bandung Plant',
            'city' => 'Bandung',
            'country' => 'Indonesia',
            'latitude' => -6.89,
            'longitude' => 107.59,
        ]);

        $destination = Location::create([
            'code' => 'TEST-ABV-2',
            'name' => 'Abuja Central',
            'city' => 'Abuja',
            'country' => 'Nigeria',
            'latitude' => 9.05,
            'longitude' => 7.49,
        ]);

        $expectedArrival = Carbon::parse('2026-11-02 12:00:00');

        $shipment = Shipment::create([
            'shipment_number' => 'TEST-DELAY-001',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_location_id' => $origin->id,
            'destination_location_id' => $destination->id,
            'cargo_type' => 'Polio Vaccine',
            'target_temp_min' => -25.0,
            'target_temp_max' => -15.0,
            'total_weight_kg' => 800,
            'expected_departure' => $expectedArrival->copy()->subDays(2),
            'expected_arrival' => $expectedArrival,
            'current_status' => 'IN_TRANSIT',
        ]);

        $etaService = app(EtaService::class);

        // 1. Test On-Time (Before expected arrival)
        $resultOnTime = $etaService->evaluateDelay($shipment, $expectedArrival->copy()->subHour());
        $this->assertFalse($resultOnTime['is_delayed']);
        $this->assertEquals('NONE', $resultOnTime['delay_severity']);

        // 2. Test LOW severity delay (e.g. 4 hours delay)
        $resultLow = $etaService->evaluateDelay($shipment, $expectedArrival->copy()->addHours(4));
        $this->assertTrue($resultLow['is_delayed']);
        $this->assertEquals('LOW', $resultLow['delay_severity']);
        $this->assertEquals(4, $resultLow['delay_duration_hours']);

        // 3. Test MEDIUM severity delay (e.g. 12 hours delay)
        $resultMed = $etaService->evaluateDelay($shipment, $expectedArrival->copy()->addHours(12));
        $this->assertTrue($resultMed['is_delayed']);
        $this->assertEquals('MEDIUM', $resultMed['delay_severity']);
        $this->assertEquals(12, $resultMed['delay_duration_hours']);

        // 4. Test HIGH / CRITICAL severity delay (e.g. 24 hours delay)
        $resultHigh = $etaService->evaluateDelay($shipment, $expectedArrival->copy()->addHours(24));
        $this->assertTrue($resultHigh['is_delayed']);
        $this->assertEquals('HIGH', $resultHigh['delay_severity']);
        $this->assertEquals(24, $resultHigh['delay_duration_hours']);
    }
}
