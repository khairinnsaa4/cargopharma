<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Http\Request;

class ApiShipmentController extends Controller
{
    protected ShipmentService $shipmentService;

    protected TrackingService $trackingService;

    protected ExceptionService $exceptionService;

    public function __construct(
        ShipmentService $shipmentService,
        TrackingService $trackingService,
        ExceptionService $exceptionService
    ) {
        $this->shipmentService = $shipmentService;
        $this->trackingService = $trackingService;
        $this->exceptionService = $exceptionService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Shipment::with(['carrier', 'origin', 'destination', 'customer', 'items']);

        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        if ($request->boolean('delayed_only')) {
            $query->where('is_delayed', true);
        }

        $shipments = $query->latest()->paginate($request->input('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => $shipments,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->isCustomer()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden: Customers cannot create consignments.'], 403);
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
            'total_weight_kg' => 'required|numeric',
            'total_volume_cbm' => 'nullable|numeric',
            'expected_departure' => 'required|date',
            'expected_arrival' => 'required|date|after:expected_departure',
            'notes' => 'nullable|string',
        ]);

        $shipment = Shipment::create(array_merge($validated, [
            'current_status' => 'CREATED',
            'created_by' => $user->id,
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Shipment successfully created.',
            'data' => $shipment->load(['carrier', 'origin', 'destination']),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $shipment = Shipment::with(['carrier', 'origin', 'destination', 'customer', 'items', 'trackingEvents', 'exceptions'])
            ->find($id);

        if (! $shipment) {
            return response()->json(['status' => 'error', 'message' => 'Shipment not found.'], 404);
        }

        if ($user->isCustomer() && $shipment->customer_id !== $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized to view this shipment.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $shipment,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if ($user->isCustomer()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        $shipment = Shipment::find($id);
        if (! $shipment) {
            return response()->json(['status' => 'error', 'message' => 'Shipment not found.'], 404);
        }

        if ($request->filled('current_status')) {
            $shipment = $this->shipmentService->updateStatus(
                $shipment,
                $request->current_status,
                $user,
                $request->input('notes')
            );
        } else {
            $shipment->update($request->only(['notes', 'carrier_id']));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Shipment updated successfully.',
            'data' => $shipment->fresh(),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (! $user->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Only administrators can delete shipments.'], 403);
        }

        $shipment = Shipment::find($id);
        if (! $shipment) {
            return response()->json(['status' => 'error', 'message' => 'Shipment not found.'], 404);
        }

        $shipment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Shipment deleted successfully.',
        ]);
    }

    // Tracking endpoints
    public function getTracking(Request $request, $id)
    {
        $user = $request->user();
        $shipment = Shipment::find($id);
        if (! $shipment) {
            return response()->json(['status' => 'error', 'message' => 'Shipment not found.'], 404);
        }

        if ($user->isCustomer() && $shipment->customer_id !== $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $shipment->trackingEvents()->with('location')->get(),
        ]);
    }

    public function postTracking(Request $request, $id)
    {
        $user = $request->user();
        if ($user->isCustomer()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        $shipment = Shipment::find($id);
        if (! $shipment) {
            return response()->json(['status' => 'error', 'message' => 'Shipment not found.'], 404);
        }

        $validated = $request->validate([
            'event_type' => 'required|string',
            'location_id' => 'nullable|exists:locations,id',
            'event_time' => 'nullable|date',
            'container_temperature' => 'nullable|numeric',
            'ambient_temperature' => 'nullable|numeric',
            'status_snapshot' => 'required|string',
            'description' => 'required|string',
        ]);

        $event = $this->trackingService->recordEvent($shipment, $validated, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Tracking event logged.',
            'data' => $event,
        ], 201);
    }

    // Exceptions
    public function getExceptions(Request $request, $id)
    {
        $shipment = Shipment::find($id);
        if (! $shipment) {
            return response()->json(['status' => 'error', 'message' => 'Shipment not found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $shipment->exceptions()->with(['resolvedBy', 'trackingEvent'])->get(),
        ]);
    }

    public function postException(Request $request, $id)
    {
        $user = $request->user();
        if ($user->isCustomer()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        $shipment = Shipment::find($id);
        if (! $shipment) {
            return response()->json(['status' => 'error', 'message' => 'Shipment not found.'], 404);
        }

        $validated = $request->validate([
            'exception_type' => 'required|string',
            'severity' => 'required|string|in:LOW,MEDIUM,HIGH',
            'root_cause' => 'required|string',
        ]);

        $exception = $this->exceptionService->logException($shipment, $validated, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Exception logged.',
            'data' => $exception,
        ], 201);
    }

    public function patchException(Request $request, $id)
    {
        $user = $request->user();
        if ($user->isCustomer()) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
        }

        $exception = ShipmentException::find($id);
        if (! $exception) {
            return response()->json(['status' => 'error', 'message' => 'Exception not found.'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:INVESTIGATING,RESOLVED',
            'resolution_notes' => 'required|string',
        ]);

        $exception = $this->exceptionService->resolveException($exception, $validated['status'], $validated['resolution_notes'], $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Exception updated.',
            'data' => $exception,
        ]);
    }

    // Carriers & Locations
    public function getCarriers()
    {
        return response()->json(['status' => 'success', 'data' => Carrier::all()]);
    }

    public function postCarrier(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:carriers,code',
            'name' => 'required|string',
            'transport_type' => 'required|string|in:air,land,sea',
        ]);
        $carrier = Carrier::create($validated);

        return response()->json(['status' => 'success', 'data' => $carrier], 201);
    }

    public function getLocations()
    {
        return response()->json(['status' => 'success', 'data' => Location::all()]);
    }

    public function postLocation(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:locations,code',
            'name' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location_type' => 'required|string|in:facility,airport,transit_hub,cold_storage',
        ]);
        $location = Location::create($validated);

        return response()->json(['status' => 'success', 'data' => $location], 201);
    }
}
