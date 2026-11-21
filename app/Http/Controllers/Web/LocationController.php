<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isCustomer()) {
            abort(403, 'Akses tidak diizinkan ke data master simpul lokasi.');
        }

        $locations = Location::withCount(['originShipments', 'destinationShipments'])->latest()->paginate(15);

        return view('locations.index', compact('locations'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            abort(403, 'Hanya administrator yang dapat menambahkan lokasi transit baru.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:locations,code',
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'location_type' => 'required|string|in:facility,airport,transit_hub,cold_storage',
            'has_cold_storage' => 'nullable|boolean',
        ]);

        $validated['has_cold_storage'] = $request->boolean('has_cold_storage');

        $location = Location::create($validated);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'CREATE_LOCATION',
            'entity_type' => 'Location',
            'entity_id' => $location->id,
            'new_values' => $validated,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Lokasi {$location->name} ({$location->code}) berhasil didaftarkan.");
    }

    public function update(Request $request, Location $location)
    {
        $user = Auth::user();
        if (! $user->isAdmin()) {
            abort(403, 'Hanya administrator yang dapat mengubah data lokasi transit.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'location_type' => 'required|string|in:facility,airport,transit_hub,cold_storage',
            'has_cold_storage' => 'nullable|boolean',
        ]);

        $validated['has_cold_storage'] = $request->boolean('has_cold_storage');

        $oldValues = $location->only(['name', 'city', 'country', 'latitude', 'longitude', 'location_type', 'has_cold_storage']);
        $location->update($validated);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'UPDATE_LOCATION',
            'entity_type' => 'Location',
            'entity_id' => $location->id,
            'old_values' => $oldValues,
            'new_values' => $validated,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Lokasi {$location->code} berhasil diperbarui.");
    }
}
