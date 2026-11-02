@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Active Shipment Tracking</h1>
            <p class="text-xs text-slate-500">Live geospatial tracking and checkpoint verification across global transit hubs.</p>
        </div>

        <form action="{{ route('tracking.index') }}" method="GET" class="flex items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search waybill or batch..." 
                    class="pl-9 pr-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 font-mono">
            </div>
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs">
                Track
            </button>
        </form>
    </div>

    <!-- FULL INTERACTIVE TRACKING MAP -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="map" class="w-4 h-4 text-blue-600"></i>
                <h3 class="text-sm font-bold text-slate-900">Global Corridor Map</h3>
            </div>
            <div class="text-xs text-slate-500 flex items-center gap-3">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> In Transit</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Destination</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Delayed Checkpoint</span>
            </div>
        </div>

        <div id="full-tracking-map" class="w-full h-[420px] z-0"></div>
    </div>

    <!-- ACTIVE CORRIDORS LIST -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Monitored Consignments</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-medium">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Shipment No.</th>
                        <th class="py-3 px-4 font-semibold">Commodity</th>
                        <th class="py-3 px-4 font-semibold">Origin</th>
                        <th class="py-3 px-4 font-semibold">Destination</th>
                        <th class="py-3 px-4 font-semibold">Last Known Checkpoint</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 text-right font-semibold">Inspect</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($allShipments as $shipment)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">
                            {{ $shipment->shipment_number }}
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-800">
                            {{ $shipment->cargo_type }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $shipment->origin->city }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $shipment->destination->city }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $shipment->latestTrackingEvent->status_snapshot ?? 'En-route' }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($shipment->is_delayed)
                                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-rose-600 text-white">DELAYED</span>
                            @elseif($shipment->current_status === 'DELIVERED')
                                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-600 text-white">DELIVERED</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-blue-600 text-white">IN TRANSIT</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('shipments.show', $shipment->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">
                                Details & Timeline →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 font-mono">
                            No shipments found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $allShipments->links() }}
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const map = L.map('full-tracking-map').setView([-6.89, 107.59], 3);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);

        const bounds = [];

        @foreach($activeShipments as $s)
            const originCoord = [{{ $s->origin->latitude }}, {{ $s->origin->longitude }}];
            const destCoord = [{{ $s->destination->latitude }}, {{ $s->destination->longitude }}];
            bounds.push(originCoord, destCoord);

            L.circleMarker(originCoord, { radius: 6, color: '#0284c7', fillColor: '#0284c7', fillOpacity: 1 })
                .addTo(map)
                .bindPopup(`<b>{{ $s->shipment_number }} Origin</b><br>{{ $s->origin->name }}`);

            L.circleMarker(destCoord, { radius: 6, color: '#10b981', fillColor: '#10b981', fillOpacity: 1 })
                .addTo(map)
                .bindPopup(`<b>{{ $s->shipment_number }} Destination</b><br>{{ $s->destination->name }}`);

            L.polyline([originCoord, destCoord], {
                color: '{{ $s->is_delayed ? "#f59e0b" : "#0284c7" }}',
                weight: 3,
                opacity: 0.8,
                dashArray: '6, 6'
            }).addTo(map);
        @endforeach

        if (bounds.length > 0) {
            map.fitBounds(L.latLngBounds(bounds), { padding: [50, 50] });
        }
    });
</script>
@endpush
@endsection
