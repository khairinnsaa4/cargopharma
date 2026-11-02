<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ShipmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@cargotrace.com'],
            ['name' => 'Admin Tester', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $this->operator = User::firstOrCreate(
            ['email' => 'operator_test@cargotrace.com'],
            ['name' => 'Operator Tester', 'password' => bcrypt('password'), 'role' => 'operator']
        );

        $this->customer = User::firstOrCreate(
            ['email' => 'customer_test@cargotrace.com'],
            ['name' => 'Customer Tester', 'password' => bcrypt('password'), 'role' => 'customer']
        );

        $this->carrier = Carrier::firstOrCreate(
            ['code' => 'TEST-CARRIER'],
            ['name' => 'Test Air Cargo', 'transport_type' => 'air']
        );

        $this->origin = Location::firstOrCreate(
            ['code' => 'TEST-BDO'],
            ['name' => 'Bandung Plant', 'city' => 'Bandung', 'country' => 'Indonesia', 'latitude' => -6.89, 'longitude' => 107.59]
        );

        $this->destination = Location::firstOrCreate(
            ['code' => 'TEST-ABV'],
            ['name' => 'Abuja Depot', 'city' => 'Abuja', 'country' => 'Nigeria', 'latitude' => 9.05, 'longitude' => 7.49]
        );
    }

    public function test_can_advance_status_through_valid_lifecycle(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'TEST-LIFECYCLE-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Polio Vaccine',
            'target_temp_min' => -25.0,
            'target_temp_max' => -15.0,
            'total_weight_kg' => 500,
            'expected_departure' => Carbon::now(),
            'expected_arrival' => Carbon::now()->addDays(2),
            'current_status' => 'CREATED',
            'created_by' => $this->operator->id,
        ]);

        $service = app(ShipmentService::class);

        // Advance to BOOKED
        $shipment = $service->updateStatus($shipment, 'BOOKED', $this->operator);
        $this->assertEquals('BOOKED', $shipment->current_status);

        // Advance to PICKED_UP
        $shipment = $service->updateStatus($shipment, 'PICKED_UP', $this->operator);
        $this->assertEquals('PICKED_UP', $shipment->current_status);
        $this->assertNotNull($shipment->actual_departure);

        // Advance to IN_TRANSIT
        $shipment = $service->updateStatus($shipment, 'IN_TRANSIT', $this->operator);
        $this->assertEquals('IN_TRANSIT', $shipment->current_status);

        // Advance to DELIVERED
        $shipment = $service->updateStatus($shipment, 'DELIVERED', $this->operator);
        $this->assertEquals('DELIVERED', $shipment->current_status);
        $this->assertNotNull($shipment->actual_arrival);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $shipment = Shipment::create([
            'shipment_number' => 'TEST-INVALID-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Polio Vaccine',
            'target_temp_min' => -25.0,
            'target_temp_max' => -15.0,
            'total_weight_kg' => 500,
            'expected_departure' => Carbon::now(),
            'expected_arrival' => Carbon::now()->addDays(2),
            'current_status' => 'CREATED',
            'created_by' => $this->operator->id,
        ]);

        $service = app(ShipmentService::class);
        // Illegal jump from CREATED directly to DELIVERED
        $service->updateStatus($shipment, 'DELIVERED', $this->operator);
    }

    public function test_delivered_terminal_status_cannot_be_reverted(): void
    {
        $this->expectException(ValidationException::class);

        $shipment = Shipment::create([
            'shipment_number' => 'TEST-TERMINAL-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Polio Vaccine',
            'target_temp_min' => -25.0,
            'target_temp_max' => -15.0,
            'total_weight_kg' => 500,
            'expected_departure' => Carbon::now()->subDays(3),
            'expected_arrival' => Carbon::now()->subDay(),
            'actual_arrival' => Carbon::now(),
            'current_status' => 'DELIVERED',
            'created_by' => $this->operator->id,
        ]);

        $service = app(ShipmentService::class);
        // Attempt to revert DELIVERED to IN_TRANSIT
        $service->updateStatus($shipment, 'IN_TRANSIT', $this->operator);
    }
}
