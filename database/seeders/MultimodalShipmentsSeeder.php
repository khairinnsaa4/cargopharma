<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MultimodalShipmentsSeeder extends Seeder
{
    public function run(): void
    {
        // 0. ENSURE CORE USERS EXIST
        $admin = User::firstOrCreate(
            ['email' => 'admin@cargopharma.com'],
            [
                'name' => 'Administrator Bio Farma',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin',
                'phone' => '+62 811-2233-4455',
                'organization' => 'PT Bio Farma (Persero) - Headquarters',
                'is_active' => true,
            ]
        );

        $operator = User::firstOrCreate(
            ['email' => 'operator@cargopharma.com'],
            [
                'name' => 'Rian Pratama',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'operator',
                'phone' => '+62 812-9988-7766',
                'organization' => 'PT Bio Farma - Global Export & Cold Chain Logistics',
                'is_active' => true,
            ]
        );

        $customerUnicef = User::firstOrCreate(
            ['email' => 'customer@unicef.org'],
            [
                'name' => 'Dr. Adebayo Johnson',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'customer',
                'phone' => '+45 35 27 35 27',
                'organization' => 'UNICEF Global Supply Division (Polio Outbreak Response)',
                'is_active' => true,
            ]
        );

        $customerKemenkes = User::firstOrCreate(
            ['email' => 'customer@fmoh.gov.ng'],
            [
                'name' => 'Fatima Al-Hassan',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'customer',
                'phone' => '+234 9 523 0524',
                'organization' => 'Federal Ministry of Health Nigeria (NPHCDA)',
                'is_active' => true,
            ]
        );

        // ---------------------------------------------------------------------
        // 1. CARRIERS (LENGKAP: UDARA, LAUT & DARAT)
        // ---------------------------------------------------------------------

        // Udara (Air)
        $garuda = Carrier::updateOrCreate(
            ['code' => 'GA-CARGO'],
            [
                'name' => 'Garuda Indonesia Cargo (Pharma Care)',
                'transport_type' => 'air',
                'contact_email' => 'pharmacare@garuda-indonesia.com',
                'contact_phone' => '+62 21 2351 9999',
                'is_active' => true,
            ]
        );

        $emirates = Carrier::updateOrCreate(
            ['code' => 'EK-SKYPHARMA'],
            [
                'name' => 'Emirates SkyPharma',
                'transport_type' => 'air',
                'contact_email' => 'skypharma@emirates.com',
                'contact_phone' => '+971 4 708 1111',
                'is_active' => true,
            ]
        );

        $qatar = Carrier::updateOrCreate(
            ['code' => 'QR-PHARMA'],
            [
                'name' => 'Qatar Airways Cargo Pharma',
                'transport_type' => 'air',
                'contact_email' => 'cargopharma@qatarairways.com.qa',
                'contact_phone' => '+974 4022 0000',
                'is_active' => true,
            ]
        );

        $singaporeAir = Carrier::updateOrCreate(
            ['code' => 'SQ-PHARMA'],
            [
                'name' => 'Singapore Airlines Cargo (THRUCOOL)',
                'transport_type' => 'air',
                'contact_email' => 'cargo_pharma@singaporeair.com.sg',
                'contact_phone' => '+65 6542 5588',
                'is_active' => true,
            ]
        );

        $turkishCargo = Carrier::updateOrCreate(
            ['code' => 'TK-PHARMA'],
            [
                'name' => 'Turkish Cargo TK Pharma',
                'transport_type' => 'air',
                'contact_email' => 'tkpharma@turkishcargo.com',
                'contact_phone' => '+90 212 463 6363',
                'is_active' => true,
            ]
        );

        // Laut (Sea)
        $maersk = Carrier::updateOrCreate(
            ['code' => 'MSK-PHARMA'],
            [
                'name' => 'Maersk Line Pharma Reefer Container',
                'transport_type' => 'sea',
                'contact_email' => 'pharma.reefer@maersk.com',
                'contact_phone' => '+45 33 63 33 63',
                'is_active' => true,
            ]
        );

        $oneSea = Carrier::updateOrCreate(
            ['code' => 'ONE-REEFER'],
            [
                'name' => 'ONE Ocean Network Express Cold-Chain',
                'transport_type' => 'sea',
                'contact_email' => 'pharma-care@one-line.com',
                'contact_phone' => '+65 6899 9000',
                'is_active' => true,
            ]
        );

        $cmaCgm = Carrier::updateOrCreate(
            ['code' => 'CMA-PHARMA'],
            [
                'name' => 'CMA CGM Reefer Pharma Care',
                'transport_type' => 'sea',
                'contact_email' => 'reefer.pharma@cma-cgm.com',
                'contact_phone' => '+33 4 88 91 90 00',
                'is_active' => true,
            ]
        );

        // Darat (Land)
        $landFleet = Carrier::updateOrCreate(
            ['code' => 'BF-COLDFLEET'],
            [
                'name' => 'Bio Farma Refrigerated Convoy Fleet',
                'transport_type' => 'land',
                'contact_email' => 'fleet@biofarma.co.id',
                'contact_phone' => '+62 22 203 3755',
                'is_active' => true,
            ]
        );

        $kalbeCold = Carrier::updateOrCreate(
            ['code' => 'KLB-COLD'],
            [
                'name' => 'Kalbe Cold-Chain Logistics',
                'transport_type' => 'land',
                'contact_email' => 'coldlogistics@kalbefarma.co.id',
                'contact_phone' => '+62 21 4287 3888',
                'is_active' => true,
            ]
        );

        $posPharma = Carrier::updateOrCreate(
            ['code' => 'POS-PHARMA'],
            [
                'name' => 'Pos Logistik Indonesia Pharma Express',
                'transport_type' => 'land',
                'contact_email' => 'pharma@poslogistik.co.id',
                'contact_phone' => '+62 21 385 4000',
                'is_active' => true,
            ]
        );

        // ---------------------------------------------------------------------
        // 2. LOCATIONS (LENGKAP: FASILITAS, BANDARA, PELABUHAN, GUDANG DINGIN)
        // ---------------------------------------------------------------------

        // Fasilitas / Pabrik Bio Farma
        $bdoPlant = Location::updateOrCreate(
            ['code' => 'BDO-PLANT'],
            [
                'name' => 'Bio Farma Pasteur Vaccine Production Plant & HQ',
                'city' => 'Bandung',
                'country' => 'Indonesia',
                'latitude' => -6.8995321,
                'longitude' => 107.5976542,
                'location_type' => 'facility',
                'has_cold_storage' => true,
            ]
        );

        $cikarangPlant = Location::updateOrCreate(
            ['code' => 'CKR-PLANT'],
            [
                'name' => 'Bio Farma Cikarang Vaccine Complex',
                'city' => 'Bekasi',
                'country' => 'Indonesia',
                'latitude' => -6.3262000,
                'longitude' => 107.1518000,
                'location_type' => 'facility',
                'has_cold_storage' => true,
            ]
        );

        // Bandara (Airports)
        $cgkAirport = Location::updateOrCreate(
            ['code' => 'CGK-CARGO'],
            [
                'name' => "Soekarno-Hatta Int'l Airport (JAS Pharma Terminal)",
                'city' => 'Tangerang',
                'country' => 'Indonesia',
                'latitude' => -6.1275000,
                'longitude' => 106.6537000,
                'location_type' => 'airport',
                'has_cold_storage' => true,
            ]
        );

        $dxbHub = Location::updateOrCreate(
            ['code' => 'DXB-HUB'],
            [
                'name' => 'Dubai International Airport (Emirates SkyPharma Mega Hub)',
                'city' => 'Dubai',
                'country' => 'United Arab Emirates',
                'latitude' => 25.2532000,
                'longitude' => 55.3657000,
                'location_type' => 'transit_hub',
                'has_cold_storage' => true,
            ]
        );

        $sinCoolport = Location::updateOrCreate(
            ['code' => 'SIN-COOL'],
            [
                'name' => 'Changi International Airport (Coolport Singapore)',
                'city' => 'Singapore',
                'country' => 'Singapore',
                'latitude' => 1.3644000,
                'longitude' => 103.9915000,
                'location_type' => 'airport',
                'has_cold_storage' => true,
            ]
        );

        $abvAirport = Location::updateOrCreate(
            ['code' => 'ABV-DEST'],
            [
                'name' => "Nnamdi Azikiwe Int'l Cargo Terminal",
                'city' => 'Abuja',
                'country' => 'Nigeria',
                'latitude' => 9.0065000,
                'longitude' => 7.2631000,
                'location_type' => 'airport',
                'has_cold_storage' => true,
            ]
        );

        $fraAirport = Location::updateOrCreate(
            ['code' => 'FRA-PHARMA'],
            [
                'name' => 'Frankfurt Airport CargoCity South Pharma Hub',
                'city' => 'Frankfurt',
                'country' => 'Germany',
                'latitude' => 50.0379000,
                'longitude' => 8.5622000,
                'location_type' => 'airport',
                'has_cold_storage' => true,
            ]
        );

        // Pelabuhan Laut (Seaports)
        $priokPort = Location::updateOrCreate(
            ['code' => 'JKT-PRIOK'],
            [
                'name' => 'Pelabuhan Tanjung Priok (JICT Reefer Container Terminal)',
                'city' => 'Jakarta Utara',
                'country' => 'Indonesia',
                'latitude' => -6.1022000,
                'longitude' => 106.8833000,
                'location_type' => 'transit_hub',
                'has_cold_storage' => true,
            ]
        );

        $psaSingapore = Location::updateOrCreate(
            ['code' => 'SGP-PSA'],
            [
                'name' => 'Port of Singapore (PSA Reefer Terminals Hub)',
                'city' => 'Singapore',
                'country' => 'Singapore',
                'latitude' => 1.2644000,
                'longitude' => 103.8222000,
                'location_type' => 'transit_hub',
                'has_cold_storage' => true,
            ]
        );

        $rotterdamPort = Location::updateOrCreate(
            ['code' => 'RTM-GATE'],
            [
                'name' => 'Port of Rotterdam (Pharma Cold-Chain Gateway Europe)',
                'city' => 'Rotterdam',
                'country' => 'Netherlands',
                'latitude' => 51.9540000,
                'longitude' => 4.1450000,
                'location_type' => 'transit_hub',
                'has_cold_storage' => true,
            ]
        );

        $lagosPort = Location::updateOrCreate(
            ['code' => 'LOS-APAPA'],
            [
                'name' => 'Port of Lagos (Apapa Cold-Chain Wharf)',
                'city' => 'Lagos',
                'country' => 'Nigeria',
                'latitude' => 6.4474000,
                'longitude' => 3.3644000,
                'location_type' => 'transit_hub',
                'has_cold_storage' => true,
            ]
        );

        // Gudang Dingin & Simpul Transit Darat (Cold Storage / Land Transit)
        $abvColdStore = Location::updateOrCreate(
            ['code' => 'ABV-COLD'],
            [
                'name' => 'Nigeria National Strategic Vaccine Cold Store (NPHCDA)',
                'city' => 'Abuja',
                'country' => 'Nigeria',
                'latitude' => 9.0765000,
                'longitude' => 7.3986000,
                'location_type' => 'cold_storage',
                'has_cold_storage' => true,
            ]
        );

        $surabayaDepot = Location::updateOrCreate(
            ['code' => 'SUB-DINKES'],
            [
                'name' => 'Gudang Farmasi Regional Dinkes Jawa Timur (Surabaya Cold Hub)',
                'city' => 'Surabaya',
                'country' => 'Indonesia',
                'latitude' => -7.2754000,
                'longitude' => 112.7578000,
                'location_type' => 'cold_storage',
                'has_cold_storage' => true,
            ]
        );

        $semarangTransit = Location::updateOrCreate(
            ['code' => 'SMG-TRANSIT'],
            [
                'name' => 'Hub Transit Dingin Tol Semarang KM 424',
                'city' => 'Semarang',
                'country' => 'Indonesia',
                'latitude' => -7.0350000,
                'longitude' => 110.4200000,
                'location_type' => 'transit_hub',
                'has_cold_storage' => true,
            ]
        );

        $yogyaHub = Location::updateOrCreate(
            ['code' => 'YOG-HUB'],
            [
                'name' => 'Bio Farma Regional Depot D.I. Yogyakarta',
                'city' => 'Sleman',
                'country' => 'Indonesia',
                'latitude' => -7.7692000,
                'longitude' => 110.3777000,
                'location_type' => 'cold_storage',
                'has_cold_storage' => true,
            ]
        );

        // ---------------------------------------------------------------------
        // 3. SHIPMENTS MULTIMODA (LAUT & DARAT TAMBAHAN)
        // ---------------------------------------------------------------------

        // SHIPMENT 5 (LAUT / SEA): Jakarta Tanjung Priok -> Rotterdam via Singapore
        $shipment5 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-BIO-2024-005'],
            [
                'customer_id' => $customerUnicef->id,
                'carrier_id' => $maersk->id,
                'origin_location_id' => $priokPort->id,
                'destination_location_id' => $rotterdamPort->id,
                'cargo_type' => 'Bulk Biological Vaccine - Maritime Reefer (bOPV Concentrate)',
                'target_temp_min' => -25.00,
                'target_temp_max' => -15.00,
                'total_weight_kg' => 14800.00,
                'total_volume_cbm' => 64.00,
                'expected_departure' => Carbon::now()->subDays(6),
                'expected_arrival' => Carbon::now()->addDays(12),
                'actual_departure' => Carbon::now()->subDays(6),
                'current_status' => 'IN_TRANSIT',
                'is_delayed' => false,
                'delay_duration_hours' => 0,
                'delay_severity' => 'NONE',
                'created_by' => $operator->id,
                'notes' => 'Ekspedisi kargo laut kontainer 40ft High-Cube Active Reefer (Star Cool Integrated Unit). Kapal: M/V Maersk Mc-Kinney Moller.',
            ]
        );

        ShipmentItem::updateOrCreate(
            ['shipment_id' => $shipment5->id, 'item_code' => 'BF-BOPV-MARITIME'],
            [
                'product_name' => 'bOPV Bulk Polio Vaccine Concentrate (Maritime Reefer)',
                'batch_number' => 'BF-2024-MAR-01',
                'expiry_date' => Carbon::now()->addYears(2),
                'quantity_vials' => 450000,
                'doses_per_vial' => 20,
                'container_type' => '40ft Daikin / Star Cool Active Maritime Ultra-Reefer #MSKU-902144',
            ]
        );

        TrackingEvent::firstOrCreate(
            ['shipment_id' => $shipment5->id, 'event_type' => 'PICKED_UP'],
            [
                'location_id' => $priokPort->id,
                'status_snapshot' => 'Kontainer Reefer 40ft dimuat di Dermaga JICT Pelabuhan Tanjung Priok.',
                'event_time' => Carbon::now()->subDays(6),
                'recorded_lat' => -6.1022000,
                'recorded_lng' => 106.8833000,
                'ambient_temperature' => 31.5,
                'container_temperature' => -22.3,
                'description' => 'Container plug-in verification completed at Jakarta International Container Terminal (JICT). Genset operational.',
                'created_by' => $operator->id,
            ]
        );

        TrackingEvent::firstOrCreate(
            ['shipment_id' => $shipment5->id, 'event_type' => 'TRANSIT_HUB'],
            [
                'location_id' => $psaSingapore->id,
                'status_snapshot' => 'Kapal bersandar & inspeksi plug-in listrik di PSA Singapore Reefer Terminal.',
                'event_time' => Carbon::now()->subDays(3),
                'recorded_lat' => 1.2644000,
                'recorded_lng' => 103.8222000,
                'ambient_temperature' => 29.8,
                'container_temperature' => -21.8,
                'description' => 'Transshipment crane transfer to European mainline vessel. Continuous telemetry link verified.',
                'created_by' => $operator->id,
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shipment5->id, 'event_type' => 'IN_TRANSIT'],
            [
                'location_id' => null,
                'status_snapshot' => 'Kapal melintas di Laut Arab mendekati Teluk Aden (Samudra Hindia).',
                'event_time' => Carbon::now()->subHours(8),
                'recorded_lat' => 11.4500000,
                'recorded_lng' => 54.3000000,
                'ambient_temperature' => 30.2,
                'container_temperature' => -21.4,
                'description' => 'Maritime satellite AIS telemetry: Speed 19.4 knots, course 285 deg. Compressor dual-redundancy operating normally.',
                'created_by' => $operator->id,
            ]
        );

        // SHIPMENT 6 (LAUT / SEA): Jakarta Tanjung Priok -> Lagos Apapa Wharf (Nigeria)
        $shipment6 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-BIO-2024-006'],
            [
                'customer_id' => $customerKemenkes->id,
                'carrier_id' => $oneSea->id,
                'origin_location_id' => $priokPort->id,
                'destination_location_id' => $lagosPort->id,
                'cargo_type' => 'Pentabio & Measles Diluents - Maritime Reefer Cargo',
                'target_temp_min' => 2.00,
                'target_temp_max' => 8.00,
                'total_weight_kg' => 9600.00,
                'total_volume_cbm' => 38.00,
                'expected_departure' => Carbon::now()->subDays(4),
                'expected_arrival' => Carbon::now()->addDays(16),
                'actual_departure' => Carbon::now()->subDays(4),
                'current_status' => 'IN_TRANSIT',
                'is_delayed' => false,
                'delay_duration_hours' => 0,
                'delay_severity' => 'NONE',
                'created_by' => $operator->id,
                'notes' => 'Direct West Africa reefer corridor. Kapal: ONE Apus Reefer. Chilled cold chain 2C-8C.',
            ]
        );

        ShipmentItem::updateOrCreate(
            ['shipment_id' => $shipment6->id, 'item_code' => 'BF-DILUENT-SEA'],
            [
                'product_name' => 'Vaccine Stabilizers & Syringes Cold Pack (Maritime)',
                'batch_number' => 'BF-2024-SEA09',
                'expiry_date' => Carbon::now()->addYears(3),
                'quantity_vials' => 320000,
                'doses_per_vial' => 5,
                'container_type' => '20ft ThermoKing Magnum Plus Maritime Container #ONEE-419022',
            ]
        );

        TrackingEvent::firstOrCreate(
            ['shipment_id' => $shipment6->id, 'event_type' => 'PICKED_UP'],
            [
                'location_id' => $priokPort->id,
                'status_snapshot' => 'Kontainer Reefer 20ft dimuat di Tanjung Priok.',
                'event_time' => Carbon::now()->subDays(4),
                'recorded_lat' => -6.1022000,
                'recorded_lng' => 106.8833000,
                'ambient_temperature' => 32.0,
                'container_temperature' => 4.5,
                'description' => 'Pre-cooling verified at +4.5C before departure.',
                'created_by' => $operator->id,
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shipment6->id, 'event_type' => 'IN_TRANSIT'],
            [
                'location_id' => null,
                'status_snapshot' => 'Kapal melintasi Selat Sunda menuju Samudra Hindia bagian barat.',
                'event_time' => Carbon::now()->subHours(12),
                'recorded_lat' => -6.0500000,
                'recorded_lng' => 104.9000000,
                'ambient_temperature' => 28.5,
                'container_temperature' => 4.3,
                'description' => 'Vessel ONE Apus en-route around Cape of Good Hope. Temperature securely maintained at 4.3°C.',
                'created_by' => $operator->id,
            ]
        );

        // SHIPMENT 7 (DARAT / LAND): Bandung Pasteur -> Surabaya Dinkes via Tol Trans-Jawa
        $shipment7 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-BIO-2024-007'],
            [
                'customer_id' => $customerUnicef->id,
                'carrier_id' => $landFleet->id,
                'origin_location_id' => $bdoPlant->id,
                'destination_location_id' => $surabayaDepot->id,
                'cargo_type' => 'Biological Vaccine - Cold Chain (MR Vaccine Trans-Java Express)',
                'target_temp_min' => 2.00,
                'target_temp_max' => 8.00,
                'total_weight_kg' => 1950.00,
                'total_volume_cbm' => 7.80,
                'expected_departure' => Carbon::now()->subHours(6),
                'expected_arrival' => Carbon::now()->addHours(4),
                'actual_departure' => Carbon::now()->subHours(6),
                'current_status' => 'IN_TRANSIT',
                'is_delayed' => false,
                'delay_duration_hours' => 0,
                'delay_severity' => 'NONE',
                'created_by' => $operator->id,
                'notes' => 'Distribusi rutin program imunisasi Jawa Timur via Tol Trans-Jawa. Truk Reefer ThermoKing Unit #FL-08.',
            ]
        );

        ShipmentItem::updateOrCreate(
            ['shipment_id' => $shipment7->id, 'item_code' => 'BF-MR-SURABAYA'],
            [
                'product_name' => 'Measles & Rubella Vaccine Lyophilized (10-Dose)',
                'batch_number' => 'BF-2024-MR-SUB',
                'expiry_date' => Carbon::now()->addYears(2),
                'quantity_vials' => 120000,
                'doses_per_vial' => 10,
                'container_type' => 'Isuzu Giga Reefer Truck ThermoKing T-1000R Active Unit #FL-08',
            ]
        );

        TrackingEvent::firstOrCreate(
            ['shipment_id' => $shipment7->id, 'event_type' => 'PICKED_UP'],
            [
                'location_id' => $bdoPlant->id,
                'status_snapshot' => 'Dimuat di Pabrik Bio Farma Pasteur Bandung.',
                'event_time' => Carbon::now()->subHours(6),
                'recorded_lat' => -6.8995321,
                'recorded_lng' => 107.5976542,
                'ambient_temperature' => 24.5,
                'container_temperature' => 4.1,
                'description' => 'Loading check passed at 4.1°C. Cold curtain deployed.',
                'created_by' => $operator->id,
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shipment7->id, 'event_type' => 'IN_TRANSIT'],
            [
                'location_id' => $semarangTransit->id,
                'status_snapshot' => 'Pemeriksaan suhu berkala di Rest Area Tol KM 424 Semarang.',
                'event_time' => Carbon::now()->subHours(2),
                'recorded_lat' => -7.0350000,
                'recorded_lng' => 110.4200000,
                'ambient_temperature' => 33.2,
                'container_temperature' => 4.4,
                'description' => 'Driver shift change and temperature logger verification. Thermoking compressor stable.',
                'created_by' => $operator->id,
            ]
        );

        // SHIPMENT 8 (UDARA / AIR): Bandung Pasteur -> Frankfurt Airport (European Distribution Hub)
        $shipment8 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-BIO-2024-008'],
            [
                'customer_id' => $customerUnicef->id,
                'carrier_id' => $turkishCargo->id,
                'origin_location_id' => $bdoPlant->id,
                'destination_location_id' => $fraAirport->id,
                'cargo_type' => 'Rotavirus Oral Vaccine & Polio Lyophilized Stock',
                'target_temp_min' => 2.00,
                'target_temp_max' => 8.00,
                'total_weight_kg' => 1850.00,
                'total_volume_cbm' => 6.20,
                'expected_departure' => Carbon::now()->subHours(10),
                'expected_arrival' => Carbon::now()->addHours(8),
                'actual_departure' => Carbon::now()->subHours(10),
                'current_status' => 'IN_TRANSIT',
                'is_delayed' => false,
                'delay_duration_hours' => 0,
                'delay_severity' => 'NONE',
                'created_by' => $operator->id,
                'notes' => 'European Global Vaccine Stockpile. Envirotainer e2 active climate unit.',
            ]
        );

        ShipmentItem::updateOrCreate(
            ['shipment_id' => $shipment8->id, 'item_code' => 'BF-ROTA-FRA'],
            [
                'product_name' => 'Rotavirus Oral Vaccine (Bio Farma)',
                'batch_number' => 'BF-ROTA-2024',
                'expiry_date' => Carbon::now()->addYears(2),
                'quantity_vials' => 80000,
                'doses_per_vial' => 5,
                'container_type' => 'Envirotainer RAP e2 Unit #ET-9901',
            ]
        );

        TrackingEvent::firstOrCreate(
            ['shipment_id' => $shipment8->id, 'event_type' => 'PICKED_UP'],
            [
                'location_id' => $bdoPlant->id,
                'status_snapshot' => 'Selesai dimuat di Envirotainer e2 Pabrik Bandung.',
                'event_time' => Carbon::now()->subHours(10),
                'recorded_lat' => -6.8995321,
                'recorded_lng' => 107.5976542,
                'ambient_temperature' => 25.0,
                'container_temperature' => 4.2,
                'description' => 'Dispatched via escort convoy to CGK Airport.',
                'created_by' => $operator->id,
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shipment8->id, 'event_type' => 'IN_TRANSIT'],
            [
                'location_id' => null,
                'status_snapshot' => 'Penerbangan kargo TK6590 melintas di atas Laut Hitam.',
                'event_time' => Carbon::now()->subHours(3),
                'recorded_lat' => 43.1500000,
                'recorded_lng' => 34.5000000,
                'ambient_temperature' => -45.0,
                'container_temperature' => 4.0,
                'description' => 'Cruising at FL360 altitude. Active compressor maintains strictly 4.0°C.',
                'created_by' => $operator->id,
            ]
        );

        // SHIPMENT 9 (DARAT / LAND): Cikarang Plant -> Yogyakarta Regional Hub (Pos Logistik)
        $shipment9 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-BIO-2024-009'],
            [
                'customer_id' => $customerUnicef->id,
                'carrier_id' => $posPharma->id,
                'origin_location_id' => $cikarangPlant->id,
                'destination_location_id' => $yogyaHub->id,
                'cargo_type' => 'Pentabio & DTP-HB-Hib Cold-Chain Regional Distribution',
                'target_temp_min' => 2.00,
                'target_temp_max' => 8.00,
                'total_weight_kg' => 1400.00,
                'total_volume_cbm' => 5.20,
                'expected_departure' => Carbon::now()->subHours(5),
                'expected_arrival' => Carbon::now()->addHours(3),
                'actual_departure' => Carbon::now()->subHours(5),
                'current_status' => 'IN_TRANSIT',
                'is_delayed' => false,
                'delay_duration_hours' => 0,
                'delay_severity' => 'NONE',
                'created_by' => $operator->id,
                'notes' => 'Pos Logistik Pharma Express Reefer Box. Tol Cipali - Trans Jawa.',
            ]
        );

        ShipmentItem::updateOrCreate(
            ['shipment_id' => $shipment9->id, 'item_code' => 'BF-PENTA-YOG'],
            [
                'product_name' => 'Pentabio DTP-HB-Hib Vaccine (Bio Farma)',
                'batch_number' => 'BF-PENT-YOG24',
                'expiry_date' => Carbon::now()->addYears(2),
                'quantity_vials' => 65000,
                'doses_per_vial' => 5,
                'container_type' => 'Mitsubishi Fuso Cold-Diesel Reefer Box #B-9144-PXZ',
            ]
        );

        TrackingEvent::firstOrCreate(
            ['shipment_id' => $shipment9->id, 'event_type' => 'PICKED_UP'],
            [
                'location_id' => $cikarangPlant->id,
                'status_snapshot' => 'Pemeriksaan suhu muat di Gudang Pusat Cikarang.',
                'event_time' => Carbon::now()->subHours(5),
                'recorded_lat' => -6.3262000,
                'recorded_lng' => 107.1518000,
                'ambient_temperature' => 29.5,
                'container_temperature' => 4.3,
                'description' => 'Loading audit completed.',
                'created_by' => $operator->id,
            ]
        );

        TrackingEvent::updateOrCreate(
            ['shipment_id' => $shipment9->id, 'event_type' => 'IN_TRANSIT'],
            [
                'location_id' => null,
                'status_snapshot' => 'Truk melintas di Tol Pejagan-Pemalang KM 294.',
                'event_time' => Carbon::now()->subHours(1),
                'recorded_lat' => -6.9100000,
                'recorded_lng' => 109.1200000,
                'ambient_temperature' => 32.1,
                'container_temperature' => 4.5,
                'description' => 'Real-time GPS tracker and thermal sensor transmitting normally.',
                'created_by' => $operator->id,
            ]
        );

        $firstShipmentId = Shipment::first()?->id ?? 1;

        // 9. SEED COLD-CHAIN REGULATORY AUDIT LOGS (WHO GDP & 21 CFR Part 11 COMPLIANCE)
        $auditEntries = [
            [
                'user_id' => $admin->id,
                'action' => 'USER_AUTHENTICATED_SSO',
                'entity_type' => 'User',
                'entity_id' => $admin->id,
                'old_values' => null,
                'new_values' => ['session' => 'SECURE_TLS_ECDHE', 'auth_level' => 'ADMIN_2FA', 'role' => 'admin'],
                'ip_address' => '10.20.1.45',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) BioFarma-Intranet/2026.1',
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'user_id' => $operator->id,
                'action' => 'BATCH_RELEASE_VERIFIED',
                'entity_type' => 'ShipmentItem',
                'entity_id' => 1,
                'old_values' => ['qa_status' => 'PENDING_RELEASE'],
                'new_values' => ['batch_number' => 'BF-nOPV2-24A1', 'product' => 'nOPV2 Vaccine', 'qa_approval' => 'WHO_PREQUALIFIED', 'vials' => 50000],
                'ip_address' => '10.20.2.112',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/131.0',
                'created_at' => Carbon::now()->subHours(8),
            ],
            [
                'user_id' => $operator->id,
                'action' => 'SENSOR_CALIBRATION_LOGGED',
                'entity_type' => 'TrackingEvent',
                'entity_id' => 1,
                'old_values' => ['calibrated' => false],
                'new_values' => ['sensor_id' => 'IOT-SENS-BDO-01', 'target_range' => '-20°C to -15°C', 'drift_tolerance' => '±0.2°C', 'cert' => 'NIST_TRACEABLE'],
                'ip_address' => '10.20.2.112',
                'user_agent' => 'BioFarma-Edge-Gateway/v2.4 (Debian)',
                'created_at' => Carbon::now()->subHours(12),
            ],
            [
                'user_id' => $operator->id,
                'action' => 'CARRIER_SLA_DISPATCH',
                'entity_type' => 'Shipment',
                'entity_id' => $firstShipmentId,
                'old_values' => ['carrier_id' => null, 'status' => 'MANIFEST_CREATED'],
                'new_values' => ['carrier_id' => $emirates->id, 'carrier_code' => 'EK-SKYPHARMA', 'flight_awb' => '176-92847162', 'status' => 'PICKED_UP'],
                'ip_address' => '10.20.2.112',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/131.0',
                'created_at' => Carbon::now()->subHours(18),
            ],
            [
                'user_id' => $operator->id,
                'action' => 'TEMPERATURE_ALERT_ACKNOWLEDGED',
                'entity_type' => 'ShipmentException',
                'entity_id' => 1,
                'old_values' => ['status' => 'OPEN', 'pcm_intervention' => false],
                'new_values' => ['status' => 'INVESTIGATING', 'action_taken' => 'Tarmac canopy deployment confirmed with Dnata Ground Ops at DXB', 'cool_pack_status' => 'ACTIVE_NOMINAL'],
                'ip_address' => '10.20.2.115',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/131.0',
                'created_at' => Carbon::now()->subHours(24),
            ],
            [
                'user_id' => $customerUnicef->id,
                'action' => 'CONSIGNEE_TELEMETRY_ACCESS',
                'entity_type' => 'Shipment',
                'entity_id' => $firstShipmentId,
                'old_values' => null,
                'new_values' => ['action' => 'REALTIME_TELEMETRY_EXPORT', 'consignee' => 'UNICEF Global Supply', 'destination' => 'Abuja NPHCDA Cold Store'],
                'ip_address' => '192.168.4.88',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'created_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($auditEntries as $entry) {
            \App\Models\AuditLog::firstOrCreate(
                ['action' => $entry['action'], 'entity_type' => $entry['entity_type'], 'entity_id' => $entry['entity_id']],
                $entry
            );
        }
    }
}
