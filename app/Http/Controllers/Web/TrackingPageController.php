<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingPageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Shipment::with(['carrier', 'origin', 'destination', 'customer', 'latestTrackingEvent']);

        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('shipment_number', 'like', "%{$keyword}%")
                  ->orWhere('cargo_type', 'like', "%{$keyword}%");
            });
        }

        $activeShipments = (clone $query)->whereIn('current_status', ['PICKED_UP', 'IN_TRANSIT'])->get();
        $allShipments = $query->latest()->paginate(10)->withQueryString();

        return view('tracking.index', compact('activeShipments', 'allShipments'));
    }
}
