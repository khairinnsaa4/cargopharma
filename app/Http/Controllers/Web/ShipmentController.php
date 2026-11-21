<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\User;
use App\Services\CurrencyApiService;
use App\Services\ShipmentService;
use App\Services\WeatherApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    protected ShipmentService $shipmentService;

    protected WeatherApiService $weatherService;

    protected CurrencyApiService $currencyService;

    public function __construct(
        ShipmentService $shipmentService,
        WeatherApiService $weatherService,
        CurrencyApiService $currencyService
    ) {
        $this->shipmentService = $shipmentService;
        $this->weatherService = $weatherService;
        $this->currencyService = $currencyService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Shipment::with(['carrier', 'origin', 'destination', 'customer', 'exceptions']);

        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        // Search & Filters (Slide 10)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                    ->orWhere('cargo_type', 'like', "%{$search}%")
                    ->orWhereHas('items', function ($itemQ) use ($search) {
                        $itemQ->where('batch_number', 'like', "%{$search}%")
                            ->orWhere('product_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->input('status'));
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        }

        if ($request->filled('transport_type')) {
            $transportType = $request->input('transport_type');
            $query->whereHas('carrier', function ($q) use ($transportType) {
                $q->where('transport_type', $transportType);
            });
        }

        if ($request->filled('delay_only') && $request->boolean('delay_only')) {
            $query->where('is_delayed', true);
        }

        $shipments = $query->latest()->paginate(10)->withQueryString();
        $carriers = Carrier::where('is_active', true)->get();

        return view('shipments.index', compact('shipments', 'carriers'));
    }

    public function show(Shipment $shipment)
    {
        $user = Auth::user();

        // Customer authorization: can only view own shipment (Slide 5 & 13)
        if ($user->isCustomer() && $shipment->customer_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan: Anda hanya berhak memeriksa pengiriman yang ditugaskan ke organisasi Anda.');
        }

        $shipment->load([
            'customer',
            'carrier',
            'origin',
            'destination',
            'items',
            'trackingEvents.location',
            'trackingEvents.createdBy',
            'exceptions.resolvedBy',
            'exceptions.trackingEvent',
        ]);

        $locations = Location::orderBy('name')->get();
        $carriers = Carrier::where('is_active', true)->get();

        // Get live/latest weather for current coordinate
        $latestEvent = $shipment->trackingEvents->last();
        $currentLat = $latestEvent?->recorded_lat ?? $shipment->origin->latitude;
        $currentLng = $latestEvent?->recorded_lng ?? $shipment->origin->longitude;
        $weatherData = $this->weatherService->getWeatherByCoordinates((float) $currentLat, (float) $currentLng);

        // Currency Conversion for Customs & Cargo Insurance Valuation based strictly on actual items
        $currencyRates = $this->currencyService->getRates();
        $totalVials = (int) $shipment->items->sum('quantity_vials');
        $unitPriceUsd = 8.50; // International benchmark per nOPV2 10-dose vial
        $declaredUsd = $totalVials > 0 ? ($totalVials * $unitPriceUsd) : 0.0;
        $declaredIdr = $declaredUsd > 0 ? $this->currencyService->convert($declaredUsd, 'USD', 'IDR') : 0.0;
        $declaredEur = $declaredUsd > 0 ? $this->currencyService->convert($declaredUsd, 'USD', 'EUR') : 0.0;

        $firstItem = $shipment->items->first();
        $mapData = [
            'id' => $shipment->id,
            'shipment_number' => $shipment->shipment_number,
            'current_status' => $shipment->current_status,
            'is_delayed' => (bool) $shipment->is_delayed,
            'target_temp_min' => (float) $shipment->target_temp_min,
            'target_temp_max' => (float) $shipment->target_temp_max,
            'product_name' => $firstItem?->product_name ?? $shipment->cargo_type,
            'batch_number' => $firstItem?->batch_number ?? 'N/A',
            'quantity_vials' => $totalVials,
            'carrier_name' => $shipment->carrier?->name ?? 'Carrier Partner',
            'transport_type' => strtolower($shipment->carrier?->transport_type ?? 'air'),
            'origin' => [
                'name' => $shipment->origin?->name ?? 'Origin Hub',
                'city' => $shipment->origin?->city ?? 'Origin',
                'country' => $shipment->origin?->country ?? '',
                'lat' => (float) ($shipment->origin?->latitude ?? 0),
                'lng' => (float) ($shipment->origin?->longitude ?? 0),
            ],
            'destination' => [
                'name' => $shipment->destination?->name ?? 'Destination Hub',
                'city' => $shipment->destination?->city ?? 'Destination',
                'country' => $shipment->destination?->country ?? '',
                'lat' => (float) ($shipment->destination?->latitude ?? 0),
                'lng' => (float) ($shipment->destination?->longitude ?? 0),
            ],
            'tracking_events' => $shipment->trackingEvents->map(function ($ev) {
                return [
                    'id' => $ev->id,
                    'event_type' => $ev->event_type,
                    'status_snapshot' => $ev->status_snapshot,
                    'notes' => $ev->notes,
                    'container_temperature' => $ev->container_temperature !== null ? (float) $ev->container_temperature : null,
                    'lat' => $ev->recorded_lat !== null ? (float) $ev->recorded_lat : null,
                    'lng' => $ev->recorded_lng !== null ? (float) $ev->recorded_lng : null,
                    'location_name' => $ev->location?->name ?? 'Transit Checkpoint',
                    'city' => $ev->location?->city ?? '',
                    'formatted_time' => $ev->event_time ? $ev->event_time->format('d M Y, H:i') : '',
                ];
            })->values()->all(),
        ];

        return view('shipments.show', compact(
            'shipment',
            'locations',
            'carriers',
            'weatherData',
            'latestEvent',
            'currencyRates',
            'declaredUsd',
            'declaredIdr',
            'declaredEur',
            'mapData'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->isCustomer()) {
            abort(403, 'Pelanggan tidak diizinkan untuk mendaftarkan konsinyasi baru.');
        }

        $customers = User::where('role', 'customer')->get();
        $carriers = Carrier::where('is_active', true)->get();
        $locations = Location::all();

        return view('shipments.create', compact('customers', 'carriers', 'locations'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->isCustomer()) {
            abort(403);
        }

        $validated = $request->validate([
            'shipment_number' => 'required|string|unique:shipments,shipment_number',
            'customer_id' => 'required|exists:users,id',
            'carrier_id' => 'required|exists:carriers,id',
            'origin_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|exists:locations,id|different:origin_location_id',
            'cargo_type' => 'required|string',
            'target_temp_min' => 'required|numeric',
            'target_temp_max' => 'required|numeric|gt:target_temp_min',
            'total_weight_kg' => 'required|numeric|min:1',
            'total_volume_cbm' => 'nullable|numeric',
            'expected_departure' => 'required|date',
            'expected_arrival' => 'required|date|after:expected_departure',
            'notes' => 'nullable|string',

            // Single item fallback
            'item_code' => 'required_without:items|nullable|string',
            'product_name' => 'required_without:items|nullable|string',
            'batch_number' => 'required_without:items|nullable|string',
            'expiry_date' => 'required_without:items|nullable|date',
            'quantity_vials' => 'required_without:items|nullable|integer|min:1',
            'container_type' => 'nullable|string',

            // Multiple items support
            'items' => 'nullable|array|min:1',
            'items.*.item_code' => 'required_with:items|string',
            'items.*.product_name' => 'required_with:items|string',
            'items.*.batch_number' => 'required_with:items|string',
            'items.*.expiry_date' => 'required_with:items|date',
            'items.*.quantity_vials' => 'required_with:items|integer|min:1',
            'items.*.doses_per_vial' => 'nullable|integer',
            'items.*.container_type' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $user, $request, &$shipment) {
            $shipment = Shipment::create([
                'shipment_number' => strtoupper($validated['shipment_number']),
                'customer_id' => $validated['customer_id'],
                'carrier_id' => $validated['carrier_id'],
                'origin_location_id' => $validated['origin_location_id'],
                'destination_location_id' => $validated['destination_location_id'],
                'cargo_type' => $validated['cargo_type'],
                'target_temp_min' => $validated['target_temp_min'],
                'target_temp_max' => $validated['target_temp_max'],
                'total_weight_kg' => $validated['total_weight_kg'],
                'total_volume_cbm' => $validated['total_volume_cbm'] ?? null,
                'expected_departure' => $validated['expected_departure'],
                'expected_arrival' => $validated['expected_arrival'],
                'current_status' => 'CREATED',
                'created_by' => $user->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            if (! empty($validated['items']) && is_array($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    ShipmentItem::create([
                        'shipment_id' => $shipment->id,
                        'item_code' => $item['item_code'],
                        'product_name' => $item['product_name'],
                        'batch_number' => $item['batch_number'],
                        'expiry_date' => $item['expiry_date'],
                        'quantity_vials' => $item['quantity_vials'],
                        'doses_per_vial' => $item['doses_per_vial'] ?? 10,
                        'container_type' => $item['container_type'] ?? 'Envirotainer RKN e1 Active Cold Unit',
                    ]);
                }
            } else {
                ShipmentItem::create([
                    'shipment_id' => $shipment->id,
                    'item_code' => $validated['item_code'],
                    'product_name' => $validated['product_name'],
                    'batch_number' => $validated['batch_number'],
                    'expiry_date' => $validated['expiry_date'],
                    'quantity_vials' => $validated['quantity_vials'],
                    'doses_per_vial' => (int) $request->input('doses_per_vial', 10),
                    'container_type' => $validated['container_type'] ?? 'Envirotainer RKN e1 Active Cold Unit',
                ]);
            }
        });

        return redirect()->route('shipments.show', $shipment->id)
            ->with('success', "Konsinyasi {$shipment->shipment_number} berhasil didaftarkan ke dalam Registri Rantai Dingin Bio Farma.");
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $user = Auth::user();
        if ($user->isCustomer()) {
            abort(403);
        }

        $validated = $request->validate([
            'new_status' => 'required|string|in:BOOKED,PICKED_UP,IN_TRANSIT,DELIVERED,CANCELLED',
            'notes' => 'nullable|string',
        ]);

        $this->shipmentService->updateStatus($shipment, $validated['new_status'], $user, $validated['notes'] ?? null);

        return back()->with('success', "Status pengiriman berhasil diperbarui menjadi {$validated['new_status']}.");
    }
}
