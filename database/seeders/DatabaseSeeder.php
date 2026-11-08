<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SEED USERS (Admin, Operator, Customers)
        $admin = User::create([
            'name' => 'Administrator Bio Farma',
            'email' => 'admin@cargotrace.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+62 811-2233-4455',
            'organization' => 'PT Bio Farma (Persero) - Headquarters',
        ]);

        $operator = User::create([
            'name' => 'Rian Pratama',
            'email' => 'operator@cargotrace.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'phone' => '+62 812-9988-7766',
            'organization' => 'PT Bio Farma - Global Export & Cold Chain Logistics',
        ]);

        $customerUnicef = User::create([
            'name' => 'Dr. Adebayo Johnson',
            'email' => 'customer@unicef.org',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+45 35 27 35 27',
            'organization' => 'UNICEF Global Supply Division (Polio Outbreak Response)',
        ]);

        $customerKemenkes = User::create([
            'name' => 'Fatima Al-Hassan',
            'email' => 'customer@fmoh.gov.ng',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+234 9 523 0524',
            'organization' => 'Federal Ministry of Health Nigeria (NPHCDA)',
        ]);

        // 2. SEED CARRIERS
        $garuda = Carrier::create([
            'code' => 'GA-CARGO',
            'name' => 'Garuda Indonesia Cargo (Pharma Care)',
            'transport_type' => 'air',
            'contact_email' => 'pharmacare@garuda-indonesia.com',
            'contact_phone' => '+62 21 2351 9999',
            'is_active' => true,
        ]);

        $emirates = Carrier::create([
            'code' => 'EK-SKYPHARMA',
            'name' => 'Emirates SkyPharma',
            'transport_type' => 'air',
            'contact_email' => 'skypharma@emirates.com',
            'contact_phone' => '+971 4 708 1111',
            'is_active' => true,
        ]);

        $qatar = Carrier::create([
            'code' => 'QR-PHARMA',
            'name' => 'Qatar Airways Cargo Pharma',
            'transport_type' => 'air',
            'contact_email' => 'cargopharma@qatarairways.com.qa',
            'contact_phone' => '+974 4022 0000',
            'is_active' => true,
        ]);

        $landFleet = Carrier::create([
            'code' => 'BF-COLDFLEET',
            'name' => 'Bio Farma Refrigerated Convoy Fleet',
            'transport_type' => 'land',
            'contact_email' => 'fleet@biofarma.co.id',
            'contact_phone' => '+62 22 203 3755',
            'is_active' => true,
        ]);

        // 3. SEED LOCATIONS (Real Coordinates)
        $bdoPlant = Location::create([
            'code' => 'BDO-PLANT',
            'name' => 'Bio Farma Pasteur Vaccine Plant',
            'city' => 'Bandung',
            'country' => 'Indonesia',
            'latitude' => -6.8995321,
            'longitude' => 107.5976542,
            'location_type' => 'facility',
            'has_cold_storage' => true,
        ]);

        $cgkAirport = Location::create([
            'code' => 'CGK-CARGO',
            'name' => "Soekarno-Hatta Int'l Airport (JAS Pharma Terminal)",
            'city' => 'Tangerang',
            'country' => 'Indonesia',
            'latitude' => -6.1275000,
            'longitude' => 106.6537000,
            'location_type' => 'airport',
            'has_cold_storage' => true,
        ]);

        $dxbHub = Location::create([
            'code' => 'DXB-HUB',
            'name' => 'Dubai International Airport (Emirates SkyPharma Mega Hub)',
            'city' => 'Dubai',
            'country' => 'United Arab Emirates',
            'latitude' => 25.2532000,
            'longitude' => 55.3657000,
            'location_type' => 'transit_hub',
            'has_cold_storage' => true,
        ]);

        $abvAirport = Location::create([
            'code' => 'ABV-DEST',
            'name' => "Nnamdi Azikiwe Int'l Cargo Terminal",
            'city' => 'Abuja',
            'country' => 'Nigeria',
            'latitude' => 9.0065000,
            'longitude' => 7.2631000,
            'location_type' => 'airport',
            'has_cold_storage' => true,
        ]);

        $abvColdStore = Location::create([
            'code' => 'ABV-COLD',
            'name' => 'Nigeria National Strategic Vaccine Cold Store (NPHCDA)',
            'city' => 'Abuja',
            'country' => 'Nigeria',
            'latitude' => 9.0579000,
            'longitude' => 7.4951000,
            'location_type' => 'cold_storage',
            'has_cold_storage' => true,
        ]);

        // 4. SEED SHIPMENT 1: IN_TRANSIT (Primary Demo Case: Active Delay & Exception in Dubai)
        $shipment1 = Shipment::create([
            'shipment_number' => 'CT-BIO-2024-001',
            'customer_id' => $customerUnicef->id,
            'carrier_id' => $emirates->id,
            'origin_location_id' => $bdoPlant->id,
            'destination_location_id' => $abvColdStore->id,
            'cargo_type' => 'Biological Vaccine - Cold Chain (nOPV2)',
            'target_temp_min' => -25.00,
            'target_temp_max' => -15.00,
            'total_weight_kg' => 1850.00,
            'total_volume_cbm' => 8.50,
            'expected_departure' => Carbon::now()->subDays(2),
            'expected_arrival' => Carbon::now()->addHours(10),
            'actual_departure' => Carbon::now()->subDays(2)->addHours(1),
            'current_status' => 'IN_TRANSIT',
            'is_delayed' => true,
            'delay_duration_hours' => 14,
            'delay_severity' => 'MEDIUM',
            'created_by' => $operator->id,
            'notes' => 'UNICEF Global Polio Eradication Initiative priority shipment. Keep under -20C.',
        ]);

        // Item 1
        ShipmentItem::create([
            'shipment_id' => $shipment1->id,
            'item_code' => 'BF-NOPV2-10D',
            'product_name' => 'nOPV2 Novel Oral Polio Vaccine (10-Dose Vial)',
            'batch_number' => 'BF-2024-LOT089',
            'expiry_date' => Carbon::now()->addYears(2),
            'quantity_vials' => 150000,
            'doses_per_vial' => 10,
            'container_type' => 'Envirotainer RKN e1 (Active Electric + Dry Ice Backup)',
        ]);

        // Tracking Events for Shipment 1
        $event1 = TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'location_id' => $bdoPlant->id,
            'event_type' => 'PICKED_UP',
            'status_snapshot' => 'Vaksin dimuat ke kontainer aktif di Fasilitas Bio Farma Pasteur.',
            'event_time' => Carbon::now()->subDays(2)->addHours(1),
            'recorded_lat' => -6.8995321,
            'recorded_lng' => 107.5976542,
            'ambient_temperature' => 24.2,
            'container_temperature' => -21.8,
            'description' => 'Cold chain packaging verified. Batch BF-2024-LOT089 loaded into Envirotainer RKN e1 with dry ice ballast.',
            'created_by' => $operator->id,
        ]);

        $event2 = TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'location_id' => $cgkAirport->id,
            'event_type' => 'IN_TRANSIT',
            'status_snapshot' => 'Tiba di Terminal Kargo Bandara Soekarno-Hatta (CGK) & lolos verifikasi BPOM.',
            'event_time' => Carbon::now()->subDays(1)->subHours(18),
            'recorded_lat' => -6.1275000,
            'recorded_lng' => 106.6537000,
            'ambient_temperature' => 29.5,
            'container_temperature' => -20.5,
            'description' => 'BPOM Lot Release Certificate #BPOM-EX-8839 verified. Container handed over to JAS Airport Services cold-storage holding area.',
            'created_by' => $operator->id,
        ]);

        $event3 = TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'location_id' => $dxbHub->id,
            'event_type' => 'TRANSIT_HUB',
            'status_snapshot' => 'Pesawat mendarat di Hub Transit Emirates SkyPharma Dubai (DXB).',
            'event_time' => Carbon::now()->subHours(6),
            'recorded_lat' => 25.2532000,
            'recorded_lng' => 55.3657000,
            'ambient_temperature' => 42.8, // Extreme Middle East Tarmac Heat
            'container_temperature' => -18.4,
            'description' => 'Flight EK359 touched down at DXB. Ambient tarmac heat recorded 42.8°C. Active compressor running at maximum capacity.',
            'created_by' => $operator->id,
        ]);

        $event4 = TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'location_id' => $dxbHub->id,
            'event_type' => 'EXCEPTION_OCCURRED',
            'status_snapshot' => 'PERINGATAN SUHU EKSTREM & PENUNDAAN PENERBANGAN FEEDER KE ABUJA.',
            'event_time' => Carbon::now()->subHours(4),
            'recorded_lat' => 25.2532000,
            'recorded_lng' => 55.3657000,
            'ambient_temperature' => 43.1,
            'container_temperature' => -17.5,
            'description' => 'Feeder cargo flight to Abuja delayed by 14 hours due to air traffic congestion. Envirotainer moved to internal Pharma Cool Cell (5C ambient) to prevent compressor fatigue.',
            'created_by' => $operator->id,
        ]);

        // Exception for Shipment 1
        ShipmentException::create([
            'shipment_id' => $shipment1->id,
            'tracking_event_id' => $event4->id,
            'exception_type' => 'WEATHER_DELAY',
            'severity' => 'MEDIUM',
            'status' => 'INVESTIGATING',
            'detected_at' => Carbon::now()->subHours(4),
            'root_cause' => 'Extreme tarmac heatwave (43.1°C) at DXB transit hub combined with 14-hour connecting feeder flight reschedule.',
            'resolution_notes' => 'Container placed inside Emirates SkyPharma indoor Cool Cell. Supplementary dry ice replenishment scheduled before next feeder departure.',
            'resolved_by' => null,
        ]);

        // 5. SEED SHIPMENT 2: DELIVERED (Past Successful Benchmark Control)
        $shipment2 = Shipment::create([
            'shipment_number' => 'CT-BIO-2024-002',
            'customer_id' => $customerKemenkes->id,
            'carrier_id' => $garuda->id,
            'origin_location_id' => $bdoPlant->id,
            'destination_location_id' => $abvColdStore->id,
            'cargo_type' => 'Biological Vaccine - Cold Chain (bOPV)',
            'target_temp_min' => -25.00,
            'target_temp_max' => -15.00,
            'total_weight_kg' => 2400.00,
            'total_volume_cbm' => 11.20,
            'expected_departure' => Carbon::now()->subDays(10),
            'expected_arrival' => Carbon::now()->subDays(7),
            'actual_departure' => Carbon::now()->subDays(10),
            'actual_arrival' => Carbon::now()->subDays(7)->subHours(2),
            'current_status' => 'DELIVERED',
            'is_delayed' => false,
            'delay_duration_hours' => 0,
            'delay_severity' => 'NONE',
            'created_by' => $operator->id,
            'notes' => 'Batch delivered intact without any temperature excursions.',
        ]);

        ShipmentItem::create([
            'shipment_id' => $shipment2->id,
            'item_code' => 'BF-BOPV-20D',
            'product_name' => 'bOPV Bivalent Oral Polio Vaccine (20-Dose Vial)',
            'batch_number' => 'BF-2024-LOT042',
            'expiry_date' => Carbon::now()->addMonths(18),
            'quantity_vials' => 200000,
            'doses_per_vial' => 20,
            'container_type' => 'Envirotainer RAP e2 High-Capacity Active Unit',
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment2->id,
            'location_id' => $abvColdStore->id,
            'event_type' => 'DELIVERED',
            'status_snapshot' => 'Vaksin diterima lengkap dan lolos inspeksi di National Strategic Cold Store Abuja.',
            'event_time' => Carbon::now()->subDays(7)->subHours(2),
            'recorded_lat' => 9.0579000,
            'recorded_lng' => 7.4951000,
            'ambient_temperature' => 28.0,
            'container_temperature' => -21.4,
            'description' => 'Delivery signed and accepted by Dr. Fatima Al-Hassan (FMOH Nigeria). Data loggers confirm zero temperature deviation during 72-hour transit.',
            'created_by' => $operator->id,
        ]);

        // 6. SEED SHIPMENT 3: BOOKED (Future / Upcoming Shipment)
        $shipment3 = Shipment::create([
            'shipment_number' => 'CT-BIO-2024-003',
            'customer_id' => $customerUnicef->id,
            'carrier_id' => $qatar->id,
            'origin_location_id' => $bdoPlant->id,
            'destination_location_id' => $abvAirport->id,
            'cargo_type' => 'Biological Vaccine - Cold Chain (MR Vaccine)',
            'target_temp_min' => 2.00,
            'target_temp_max' => 8.00,
            'total_weight_kg' => 950.00,
            'total_volume_cbm' => 4.20,
            'expected_departure' => Carbon::now()->addDays(3),
            'expected_arrival' => Carbon::now()->addDays(5),
            'current_status' => 'BOOKED',
            'is_delayed' => false,
            'delay_duration_hours' => 0,
            'delay_severity' => 'NONE',
            'created_by' => $operator->id,
            'notes' => 'Measles Rubella vaccine schedule for West Africa routine immunization.',
        ]);

        ShipmentItem::create([
            'shipment_id' => $shipment3->id,
            'item_code' => 'BF-MR-10D',
            'product_name' => 'Measles & Rubella Vaccine Lyophilized (10-Dose)',
            'batch_number' => 'BF-2024-MR011',
            'expiry_date' => Carbon::now()->addYears(3),
            'quantity_vials' => 50000,
            'doses_per_vial' => 10,
            'container_type' => 'Credo Cube Passive Chilled Shipper (+2C to +8C)',
        ]);

        // 7. SEED SHIPMENT 4: IN_TRANSIT LAND CONVOY (Refrigerated Truck Convoy)
        $shipment4 = Shipment::create([
            'shipment_number' => 'CT-BIO-2024-004',
            'customer_id' => $customerKemenkes->id,
            'carrier_id' => $landFleet->id,
            'origin_location_id' => $bdoPlant->id,
            'destination_location_id' => $cgkAirport->id,
            'cargo_type' => 'Biological Vaccine - Cold Chain (Pentabio DTP-HB-Hib)',
            'target_temp_min' => 2.00,
            'target_temp_max' => 8.00,
            'total_weight_kg' => 1250.00,
            'total_volume_cbm' => 5.20,
            'expected_departure' => Carbon::now()->subHours(4),
            'expected_arrival' => Carbon::now()->addHours(2),
            'actual_departure' => Carbon::now()->subHours(4),
            'current_status' => 'IN_TRANSIT',
            'is_delayed' => false,
            'delay_duration_hours' => 0,
            'delay_severity' => 'NONE',
            'created_by' => $operator->id,
            'notes' => 'Bandung to CGK cold truck convoy. Active ThermoKing refrigeration unit.',
        ]);

        ShipmentItem::create([
            'shipment_id' => $shipment4->id,
            'item_code' => 'BF-PNT-05D',
            'product_name' => 'Pentabio DTP-HB-Hib Combined Vaccine (5-Dose)',
            'batch_number' => 'BF-2024-PNT44',
            'expiry_date' => Carbon::now()->addYears(2),
            'quantity_vials' => 85000,
            'doses_per_vial' => 5,
            'container_type' => 'ThermoKing V-500 Cold-Reefer Truck #FL-04',
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment4->id,
            'location_id' => $bdoPlant->id,
            'event_type' => 'PICKED_UP',
            'status_snapshot' => 'Dimuat ke armada truk berpendingin di Fasilitas Bio Farma Pasteur.',
            'event_time' => Carbon::now()->subHours(4),
            'recorded_lat' => -6.8995321,
            'recorded_lng' => 107.5976542,
            'ambient_temperature' => 26.5,
            'container_temperature' => 4.2,
            'description' => 'Cold chain loading verified at +4.2°C. Pre-trip inspection passed.',
            'created_by' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment4->id,
            'location_id' => null,
            'event_type' => 'IN_TRANSIT',
            'status_snapshot' => 'Konvoi melintas di Tol Cipularang KM 72 menuju Terminal Kargo CGK.',
            'event_time' => Carbon::now()->subHours(1),
            'recorded_lat' => -6.5050,
            'recorded_lng' => 107.4520,
            'ambient_temperature' => 31.0,
            'container_temperature' => 4.6,
            'description' => 'Overland highway transit en-route to Jakarta. Temperature stably maintained at 4.6°C.',
            'created_by' => $operator->id,
        ]);

        // 8. SEED NOTIFICATIONS
        Notification::create([
            'user_id' => $customerUnicef->id,
            'shipment_id' => $shipment1->id,
            'title' => 'Transit Heat Alert: CT-BIO-2024-001',
            'message' => 'Extreme ambient heat (42.8°C) detected at Dubai transit hub. Connecting feeder flight delayed by 14 hours. Active cooling maintained.',
            'type' => 'warning',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $operator->id,
            'shipment_id' => $shipment1->id,
            'title' => 'Cold Chain Exception Investigation',
            'message' => 'New WEATHER_DELAY logged for CT-BIO-2024-001 at DXB. Action required: confirm secondary dry ice top-up.',
            'type' => 'danger',
            'is_read' => false,
        ]);

        // 8. SEED AUDIT LOGS
        AuditLog::create([
            'user_id' => $operator->id,
            'action' => 'CREATE_SHIPMENT',
            'entity_type' => 'Shipment',
            'entity_id' => $shipment1->id,
            'new_values' => ['shipment_number' => 'CT-BIO-2024-001', 'status' => 'CREATED'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        AuditLog::create([
            'user_id' => $operator->id,
            'action' => 'EXCEPTION_LOGGED',
            'entity_type' => 'ShipmentException',
            'entity_id' => 1,
            'new_values' => ['type' => 'WEATHER_DELAY', 'severity' => 'MEDIUM', 'status' => 'INVESTIGATING'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);
    }
}
