<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarrierController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isCustomer()) {
            abort(403, 'Akses tidak diizinkan: Manajemen armada mitra kargo dibatasi khusus untuk personel logistik Bio Farma.');
        }

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
        if (! $user->isAdmin()) {
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

        return back()->with('success', 'Mitra armada berhasil didaftarkan.');
    }

    public function update(Request $request, Carrier $carrier)
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            abort(403, 'Hanya administrator yang dapat memperbarui data mitra armada.');
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'transport_type' => 'required|string|in:air,land,sea',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $carrier->update($validated);

        return back()->with('success', "Mitra armada {$carrier->name} berhasil diperbarui.");
    }
}
