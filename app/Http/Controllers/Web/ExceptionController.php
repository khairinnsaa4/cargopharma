<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExceptionController extends Controller
{
    protected ExceptionService $exceptionService;

    public function __construct(ExceptionService $exceptionService)
    {
        $this->exceptionService = $exceptionService;
    }

    public function store(Request $request, Shipment $shipment)
    {
        $user = Auth::user();
        if ($user->isCustomer()) {
            abort(403);
        }

        $validated = $request->validate([
            'exception_type' => 'required|string|in:TEMPERATURE_BREACH,CUSTOMS_HOLD,WEATHER_DELAY,PORT_CONGESTION,DAMAGED,DOCUMENTATION_ISSUE,MISROUTED',
            'severity' => 'required|string|in:LOW,MEDIUM,HIGH',
            'root_cause' => 'required|string',
            'detected_at' => 'required|date',
        ]);

        $this->exceptionService->logException($shipment, $validated, $user);

        return back()->with('warning', 'Shipment incident/exception has been logged.');
    }

    public function resolve(Request $request, ShipmentException $exception)
    {
        $user = Auth::user();
        if ($user->isCustomer()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:INVESTIGATING,RESOLVED',
            'resolution_notes' => 'required|string|min:5',
        ]);

        $this->exceptionService->resolveException($exception, $validated['status'], $validated['resolution_notes'], $user);

        return back()->with('success', "Incident #{$exception->id} status updated to {$validated['status']}.");
    }
}
