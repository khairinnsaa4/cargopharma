<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFourFeatureTest extends TestCase
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
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->admin = User::factory()->create([
            'email' => 'admin_test@biopharma.co.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->operator = User::factory()->create([
            'email' => 'operator_test@biopharma.co.id',
            'role' => 'operator',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'email' => 'customer_test@unicef.org',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->carrier = Carrier::create([
            'code' => 'GA-CARGO-TEST',
            'name' => 'Garuda Cargo Test',
            'transport_type' => 'air',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'BDO-PLANT-TEST',
            'name' => 'Bio Farma Production Bandung',
            'city' => 'Bandung',
            'country' => 'Indonesia',
            'latitude' => -6.8995,
            'longitude' => 107.6001,
            'location_type' => 'facility',
            'has_cold_storage' => true,
        ]);

        $this->destination = Location::create([
            'code' => 'ABV-DEPOT-TEST',
            'name' => 'Abuja Central Vaccine Hub',
            'city' => 'Abuja',
            'country' => 'Nigeria',
            'latitude' => 9.0765,
            'longitude' => 7.3986,
            'location_type' => 'cold_storage',
            'has_cold_storage' => true,
        ]);
    }

    public function test_admin_can_manage_users_while_operator_and_customer_are_forbidden(): void
    {
        // 1. Customer forbidden
        $response = $this->actingAs($this->customer)->get(route('users.index'));
        $response->assertStatus(403);

        // 2. Operator forbidden
        $response = $this->actingAs($this->operator)->get(route('users.index'));
        $response->assertStatus(403);

        // 3. Admin can list users
        $response = $this->actingAs($this->admin)->get(route('users.index'));
        $response->assertStatus(200);

        // 4. Admin can create new user
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'New Logistics Specialist',
            'email' => 'logistics.new@biopharma.co.id',
            'password' => 'SecurePass123!',
            'role' => 'operator',
            'organization' => 'Bio Farma Distribution',
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'logistics.new@biopharma.co.id', 'role' => 'operator']);

        // 5. Admin cannot delete self
        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $this->admin->id));
        $response->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_cannot_deactivate_or_delete_sole_admin(): void
    {
        // Ensure there is only 1 admin
        $this->assertEquals(1, User::where('role', 'admin')->count());

        // Attempt to demote/deactivate
        $response = $this->actingAs($this->admin)->put(route('users.update', $this->admin->id), [
            'name' => 'Admin Test Renamed',
            'email' => $this->admin->email,
            'role' => 'operator', // Attempt to demote
            'is_active' => false,
        ]);
        $response->assertSessionHasErrors('role');
        $this->assertEquals('admin', $this->admin->fresh()->role);
    }

    public function test_location_management_access_control(): void
    {
        // Customer forbidden
        $response = $this->actingAs($this->customer)->get(route('locations.index'));
        $response->assertStatus(403);

        // Operator can view
        $response = $this->actingAs($this->operator)->get(route('locations.index'));
        $response->assertStatus(200);

        // Operator cannot create
        $response = $this->actingAs($this->operator)->post(route('locations.store'), [
            'code' => 'KUL-AIRPORT',
            'name' => 'Kuala Lumpur Cargo Terminal',
            'city' => 'Sepang',
            'country' => 'Malaysia',
            'latitude' => 2.7456,
            'longitude' => 101.7072,
            'location_type' => 'airport',
            'has_cold_storage' => true,
        ]);
        $response->assertStatus(403);

        // Admin can create location
        $response = $this->actingAs($this->admin)->post(route('locations.store'), [
            'code' => 'KUL-AIRPORT',
            'name' => 'Kuala Lumpur Cargo Terminal',
            'city' => 'Sepang',
            'country' => 'Malaysia',
            'latitude' => 2.7456,
            'longitude' => 101.7072,
            'location_type' => 'airport',
            'has_cold_storage' => true,
        ]);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('locations', ['code' => 'KUL-AIRPORT']);
    }

    public function test_notifications_lifecycle(): void
    {
        $notification = Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Cold-Chain Alert',
            'message' => 'Your shipment has been dispatched.',
            'type' => 'info',
            'is_read' => false,
        ]);

        // Customer sees unread notification
        $response = $this->actingAs($this->customer)->get(route('notifications.index', ['filter' => 'unread']));
        $response->assertStatus(200);
        $response->assertSee('Cold-Chain Alert');

        // Customer marks as read
        $response = $this->actingAs($this->customer)->patch(route('notifications.read', $notification->id));
        $response->assertSessionHas('success');
        $this->assertTrue($notification->fresh()->is_read);

        // Another user cannot mark this notification
        $response = $this->actingAs($this->operator)->patch(route('notifications.read', $notification->id));
        $response->assertStatus(403);
    }

    public function test_reports_access_and_csv_export(): void
    {
        // Customer forbidden
        $response = $this->actingAs($this->customer)->get(route('reports.index'));
        $response->assertStatus(403);

        // Operator can view reports
        $response = $this->actingAs($this->operator)->get(route('reports.index'));
        $response->assertStatus(200);

        // Operator can export CSV
        $response = $this->actingAs($this->operator)->get(route('reports.exportCsv'));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_consignment_creation_with_multiple_pharmaceutical_items(): void
    {
        $response = $this->actingAs($this->operator)->post(route('shipments.store'), [
            'shipment_number' => 'SHP-MULTI-2024-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Multi-Vaccine Cold Chain Consignment',
            'target_temp_min' => -25.0,
            'target_temp_max' => -15.0,
            'total_weight_kg' => 1200.0,
            'total_volume_cbm' => 5.5,
            'expected_departure' => now()->format('Y-m-d H:i:s'),
            'expected_arrival' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'notes' => 'Priority WHO Emergency Response',

            'items' => [
                [
                    'product_name' => 'nOPV2 Polio Vaccine',
                    'batch_number' => 'BF-NOPV-LOT01',
                    'item_code' => 'BF-NOPV2-10D',
                    'quantity_vials' => 30000,
                    'doses_per_vial' => 10,
                    'expiry_date' => now()->addYears(2)->format('Y-m-d'),
                    'container_type' => 'Envirotainer RKN e1',
                ],
                [
                    'product_name' => 'bOPV Polio Vaccine',
                    'batch_number' => 'BF-BOPV-LOT02',
                    'item_code' => 'BF-BOPV-10D',
                    'quantity_vials' => 20000,
                    'doses_per_vial' => 10,
                    'expiry_date' => now()->addYears(2)->format('Y-m-d'),
                    'container_type' => 'Envirotainer RKN e1',
                ],
            ],
        ]);

        $this->assertDatabaseHas('shipments', ['shipment_number' => 'SHP-MULTI-2024-001']);
        $shipment = Shipment::where('shipment_number', 'SHP-MULTI-2024-001')->first();
        $this->assertNotNull($shipment);

        $this->assertEquals(2, $shipment->items()->count());
        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipment->id,
            'item_code' => 'BF-NOPV2-10D',
            'quantity_vials' => 30000,
        ]);
        $this->assertDatabaseHas('shipment_items', [
            'shipment_id' => $shipment->id,
            'item_code' => 'BF-BOPV-10D',
            'quantity_vials' => 20000,
        ]);
    }

    public function test_shipment_show_page_renders_successfully_with_map_data(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'SHP-SHOW-TEST-001',
            'customer_id' => $this->customer->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Polio Vaccine Cold Chain',
            'target_temp_min' => -25.0,
            'target_temp_max' => -15.0,
            'total_weight_kg' => 500.0,
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(2),
            'current_status' => 'IN_TRANSIT',
            'created_by' => $this->admin->id,
        ]);

        ShipmentItem::create([
            'shipment_id' => $shipment->id,
            'item_code' => 'BF-ITEM-01',
            'product_name' => 'nOPV2 Vaccine',
            'batch_number' => 'BF-BATCH-001',
            'expiry_date' => now()->addYear(),
            'quantity_vials' => 10000,
            'doses_per_vial' => 10,
        ]);

        $response = $this->actingAs($this->admin)->get(route('shipments.show', $shipment->id));
        $response->assertStatus(200);
        $response->assertSee('Peta Rute Koridor');
        $response->assertSee('const shipmentData =', false);
    }
}
