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
            abort(403, 'Unauthorized access: You are only permitted to inspect shipments assigned to your organization.');
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

        // Real-Time Currency Conversion for Customs & Cargo Insurance Valuation
        $currencyRates = $this->currencyService->getRates();
        $totalVials = $shipment->items->sum('quantity_vials') ?: 50000;
        $unitPriceUsd = 8.50; // International benchmark per nOPV2 10-dose vial
        $declaredUsd = $totalVials * $unitPriceUsd;
        $declaredIdr = $this->currencyService->convert($declaredUsd, 'USD', 'IDR');
        $declaredEur = $this->currencyService->convert($declaredUsd, 'USD', 'EUR');

        return view('shipments.show', compact(
            'shipment',
            'locations',
            'carriers',
            'weatherData',
            'latestEvent',
            'currencyRates',
            'declaredUsd',
            'declaredIdr',
            'declaredEur'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->isCustomer()) {
            abort(403, 'Customers are not permitted to register new consignments.');
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

            // Items validation
            'item_code' => 'required|string',
            'product_name' => 'required|string',
            'batch_number' => 'required|string',
            'expiry_date' => 'required|date',
            'quantity_vials' => 'required|integer|min:1',
            'container_type' => 'required|string',
        ]);

        DB::transaction(function () use ($validated, $user, &$shipment) {
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

            ShipmentItem::create([
                'shipment_id' => $shipment->id,
                'item_code' => $validated['item_code'],
                'product_name' => $validated['product_name'],
                'batch_number' => $validated['batch_number'],
                'expiry_date' => $validated['expiry_date'],
                'quantity_vials' => $validated['quantity_vials'],
                'container_type' => $validated['container_type'],
            ]);
        });

        return redirect()->route('shipments.show', $shipment->id)
            ->with('success', "Consignment {$shipment->shipment_number} successfully registered into Bio Farma Cold Chain Registry.");
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

        return back()->with('success', "Shipment status updated to {$validated['new_status']}.");
    }
}
