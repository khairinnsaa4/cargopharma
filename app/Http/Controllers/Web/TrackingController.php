<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    protected TrackingService $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    public function store(Request $request, Shipment $shipment)
    {
        $user = Auth::user();
        if ($user->isCustomer()) {
            abort(403);
        }

        $validated = $request->validate([
            'event_type' => 'required|string|in:PICKED_UP,IN_TRANSIT,TRANSIT_HUB,CUSTOMS_INSPECTION,DELIVERED,EXCEPTION_OCCURRED',
            'location_id' => 'nullable|exists:locations,id',
            'event_time' => 'required|date',
            'container_temperature' => 'nullable|numeric',
            'ambient_temperature' => 'nullable|numeric',
            'recorded_lat' => 'nullable|numeric',
            'recorded_lng' => 'nullable|numeric',
            'status_snapshot' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $this->trackingService->recordEvent($shipment, $validated, $user);

        return back()->with('success', 'New cold chain tracking checkpoint logged.');
    }
}
