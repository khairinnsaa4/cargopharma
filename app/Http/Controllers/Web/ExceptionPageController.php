<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShipmentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExceptionPageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ShipmentException::with(['shipment.origin', 'shipment.destination', 'shipment.carrier', 'resolvedBy']);

        if ($user->isCustomer()) {
            $query->whereHas('shipment', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $exceptions = $query->latest('detected_at')->paginate(10)->withQueryString();

        $openCount = (clone $query)->where('status', 'OPEN')->count();
        $investigatingCount = (clone $query)->where('status', 'INVESTIGATING')->count();
        $resolvedCount = (clone $query)->where('status', 'RESOLVED')->count();

        return view('exceptions.index', compact('exceptions', 'openCount', 'investigatingCount', 'resolvedCount'));
    }
}
