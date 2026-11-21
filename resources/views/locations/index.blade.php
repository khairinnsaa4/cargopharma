@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER & FILTERS -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Simpul & Lokasi Jaringan</h1>
            <p class="text-xs text-slate-500">Pabrik Bio Farma, Bandara Udara, Pelabuhan Kontainer Reefer, dan Gudang Dingin</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Filter Type Buttons -->
            <div class="inline-flex items-center rounded-lg bg-slate-200/70 p-0.5 text-xs font-semibold">
                <button type="button" onclick="filterLocations('ALL')" id="loc-tab-ALL" class="loc-tab-btn px-2.5 py-1 rounded-md bg-teal-600 text-white shadow-xs transition-all cursor-pointer">
                    Semua ({{ count($locations) }})
                </button>
                <button type="button" onclick="filterLocations('facility')" id="loc-tab-facility" class="loc-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all cursor-pointer">
                    🏭 Pabrik ({{ $locations->where('location_type', 'facility')->count() }})
                </button>
                <button type="button" onclick="filterLocations('airport')" id="loc-tab-airport" class="loc-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all cursor-pointer">
                    ✈️ Bandara ({{ $locations->where('location_type', 'airport')->count() }})
                </button>
                <button type="button" onclick="filterLocations('transit_hub')" id="loc-tab-transit_hub" class="loc-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all cursor-pointer">
                    🚢 Pelabuhan/Transit ({{ $locations->where('location_type', 'transit_hub')->count() }})
                </button>
                <button type="button" onclick="filterLocations('cold_storage')" id="loc-tab-cold_storage" class="loc-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all cursor-pointer">
                    🧊 Gudang Dingin ({{ $locations->where('location_type', 'cold_storage')->count() }})
                </button>
            </div>

            @if(Auth::user()->isAdmin())
            <button onclick="document.getElementById('modal-add-location').classList.remove('hidden')" class="px-3.5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-xs">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah
            </button>
            @endif
        </div>
    </div>

    <!-- LOCATIONS TABLE -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Nama</th>
                        <th class="py-3 px-4">Kota / Negara</th>
                        <th class="py-3 px-4">Tipe</th>
                        <th class="py-3 px-4">Pendingin</th>
                        <th class="py-3 px-4">Koordinat</th>
                        <th class="py-3 px-4 text-center">Total</th>
                        @if(Auth::user()->isAdmin())
                        <th class="py-3 px-4 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($locations as $location)
                    <tr class="location-row hover:bg-slate-50/60 transition-colors" data-type="{{ $location->location_type }}">
                        <td class="py-3.5 px-4 font-mono font-bold text-teal-800">
                            {{ $location->code }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900">
                            {{ $location->name }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="text-slate-800">{{ $location->city }}</span>,
                            <span class="text-slate-500">{{ $location->country }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $typeLabels = [
                                    'cold_storage' => 'Gudang Dingin',
                                    'facility' => 'Pabrik / Lab',
                                    'airport' => 'Bandara Hub',
                                    'transit_hub' => 'Simpul Transit'
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium uppercase font-mono
                                @if($location->location_type === 'cold_storage') bg-cyan-50 text-cyan-700 border border-cyan-200
                                @elseif($location->location_type === 'facility') bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($location->location_type === 'airport') bg-blue-50 text-blue-700 border border-blue-200
                                @else bg-slate-50 text-slate-700 border border-slate-200 @endif">
                                {{ $typeLabels[$location->location_type] ?? str_replace('_', ' ', $location->location_type) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($location->has_cold_storage)
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                <i data-lucide="snowflake" class="w-3.5 h-3.5 text-emerald-600"></i> Dingin
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                                Ambient
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500">
                            {{ number_format($location->latitude, 4) }}, {{ number_format($location->longitude, 4) }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold text-[11px]">
                                {{ ($location->origin_shipments_count ?? 0) + ($location->destination_shipments_count ?? 0) }}
                            </span>
                        </td>
                        @if(Auth::user()->isAdmin())
                        <td class="py-3.5 px-4 text-right">
                            <button onclick="openEditLocationModal({{ json_encode($location) }})" title="Edit" class="p-1.5 rounded-lg text-slate-400 hover:text-teal-700 hover:bg-slate-100 transition-colors inline-flex items-center">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 font-mono">
                            Tidak ada data lokasi transit.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($locations->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $locations->links() }}
        </div>
        @endif
    </div>

</div>

<!-- MODAL ADD LOCATION -->
@if(Auth::user()->isAdmin())
<div id="modal-add-location" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg bg-white rounded-xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Tambah Simpul Transit Baru</h3>
            <button onclick="document.getElementById('modal-add-location').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('locations.store') }}" class="space-y-3 text-xs">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Kode Simpul *</label>
                    <input type="text" name="code" required placeholder="Contoh: SIN-AP" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Nama Fasilitas *</label>
                    <input type="text" name="name" required placeholder="Contoh: Changi Cargo Complex" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Kota *</label>
                    <input type="text" name="city" required placeholder="Contoh: Singapore" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Negara *</label>
                    <input type="text" name="country" required placeholder="Contoh: Singapore" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Garis Lintang (Latitude) *</label>
                    <input type="number" step="0.0000001" name="latitude" required placeholder="1.3644" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Garis Bujur (Longitude) *</label>
                    <input type="number" step="0.0000001" name="longitude" required placeholder="103.9915" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Tipe Fasilitas *</label>
                    <select name="location_type" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                        <option value="airport">Hub Bandara (Airport Hub)</option>
                        <option value="facility">Pabrik / Manufaktur Farmasi (Plant)</option>
                        <option value="cold_storage">Depo Gudang Dingin (Cold Storage)</option>
                        <option value="transit_hub">Simpul Transit Intermoda (Transit Hub)</option>
                    </select>
                </div>
                <div class="flex items-center pt-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="has_cold_storage" value="1" checked class="rounded border-slate-300 text-teal-600">
                        <span class="font-medium text-slate-700">Dilengkapi Fasilitas Pendingin Khusus</span>
                    </label>
                </div>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-add-location').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold shadow-xs">Simpan Simpul Transit</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT LOCATION -->
<div id="modal-edit-location" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg bg-white rounded-xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Edit Simpul Transit</h3>
            <button onclick="document.getElementById('modal-edit-location').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="edit-location-form" method="POST" action="" class="space-y-3 text-xs">
            @csrf
            @method('PATCH')
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Kode Simpul</label>
                    <input type="text" id="edit-location-code" disabled class="w-full p-2 rounded-lg border border-slate-200 bg-slate-100 text-slate-500 font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Nama Fasilitas *</label>
                    <input type="text" name="name" id="edit-location-name" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Kota *</label>
                    <input type="text" name="city" id="edit-location-city" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Negara *</label>
                    <input type="text" name="country" id="edit-location-country" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Garis Lintang (Latitude) *</label>
                    <input type="number" step="0.0000001" name="latitude" id="edit-location-lat" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Garis Bujur (Longitude) *</label>
                    <input type="number" step="0.0000001" name="longitude" id="edit-location-lng" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Tipe Fasilitas *</label>
                    <select name="location_type" id="edit-location-type" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                        <option value="airport">Hub Bandara (Airport Hub)</option>
                        <option value="facility">Pabrik / Manufaktur Farmasi (Plant)</option>
                        <option value="cold_storage">Depo Gudang Dingin (Cold Storage)</option>
                        <option value="transit_hub">Simpul Transit Intermoda (Transit Hub)</option>
                    </select>
                </div>
                <div class="flex items-center pt-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="has_cold_storage" id="edit-location-cold" value="1" class="rounded border-slate-300 text-teal-600">
                        <span class="font-medium text-slate-700">Dilengkapi Fasilitas Pendingin Khusus</span>
                    </label>
                </div>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-edit-location').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold shadow-xs">Perbarui Simpul</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterLocations(type) {
    document.querySelectorAll('.loc-tab-btn').forEach(btn => {
        btn.classList.remove('bg-teal-600', 'text-white', 'shadow-xs');
        btn.classList.add('text-slate-700');
    });
    const active = document.getElementById('loc-tab-' + type);
    if (active) {
        active.classList.remove('text-slate-700');
        active.classList.add('bg-teal-600', 'text-white', 'shadow-xs');
    }

    document.querySelectorAll('.location-row').forEach(row => {
        const rowType = row.getAttribute('data-type');
        if (type === 'ALL' || rowType === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openEditLocationModal(location) {
    const form = document.getElementById('edit-location-form');
    form.action = `/locations/${location.id}`;
    document.getElementById('edit-location-code').value = location.code;
    document.getElementById('edit-location-name').value = location.name;
    document.getElementById('edit-location-city').value = location.city;
    document.getElementById('edit-location-country').value = location.country;
    document.getElementById('edit-location-lat').value = location.latitude;
    document.getElementById('edit-location-lng').value = location.longitude;
    document.getElementById('edit-location-type').value = location.location_type;
    document.getElementById('edit-location-cold').checked = Boolean(location.has_cold_storage);
    document.getElementById('modal-edit-location').classList.remove('hidden');
}
</script>
@endif
@endsection
