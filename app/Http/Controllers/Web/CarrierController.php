<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarrierController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $carriers = Carrier::withCount(['shipments', 'shipments as active_shipments_count' => function ($q) {
            $q->where('current_status', 'IN_TRANSIT');
        }, 'shipments as delayed_shipments_count' => function ($q) {
            $q->where('is_delayed', true);
        }])->get();

        return view('carriers.index', compact('carriers'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => 'required|string|unique:carriers,code',
            'name' => 'required|string',
            'transport_type' => 'required|string|in:air,land,sea',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
        ]);

        Carrier::create($validated);

        return back()->with('success', 'Carrier partner registered successfully.');
    }
}
