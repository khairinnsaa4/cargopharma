<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use App\Services\TrackingService;
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
            ['email' => 'admin_test@cargopharma.com'],
            ['name' => 'Admin Tester', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $this->operator = User::firstOrCreate(
            ['email' => 'operator_test@cargopharma.com'],
            ['name' => 'Operator Tester', 'password' => bcrypt('password'), 'role' => 'operator']
        );

        $this->customer = User::firstOrCreate(
            ['email' => 'customer_test@cargopharma.com'],
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

        // Verify automated tracking events were generated (Phase 2 Requirement 2)
        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipment->id,
            'event_type' => 'DELIVERED',
        ]);
        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipment->id,
            'event_type' => 'BOOKED',
        ]);
    }

    public function test_temperature_excursion_automatically_creates_exception_and_notifications(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'TEST-TEMP-BREACH-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Novel Polio Vaccine',
            'target_temp_min' => -25.0,
            'target_temp_max' => -15.0,
            'total_weight_kg' => 500,
            'expected_departure' => Carbon::now(),
            'expected_arrival' => Carbon::now()->addDays(2),
            'current_status' => 'IN_TRANSIT',
            'created_by' => $this->operator->id,
        ]);

        $trackingService = app(TrackingService::class);

        // Record excursion temperature of -5.0°C (10°C above maximum of -15.0°C -> HIGH severity)
        $event = $trackingService->recordEvent($shipment, [
            'event_type' => 'TRANSIT_HUB',
            'status_snapshot' => 'Severe heat exposure at tarmac',
            'container_temperature' => -5.0,
            'ambient_temperature' => 44.0,
            'description' => 'Tarmac heatwave inspection logged.',
        ], $this->operator);

        $this->assertDatabaseHas('shipment_exceptions', [
            'shipment_id' => $shipment->id,
            'exception_type' => 'TEMPERATURE_BREACH',
            'severity' => 'HIGH',
            'status' => 'OPEN',
        ]);

        $this->assertDatabaseHas('notifications', [
            'shipment_id' => $shipment->id,
            'type' => 'danger',
        ]);
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

    public function test_customer_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->customer)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Jadwal Kedatangan Konsinyasi Vaksin Saya');
    }
}
