@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER & SEARCH -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pelacakan</h1>

        <form action="{{ route('tracking.index') }}" method="GET" class="flex items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari resi / AWB..." 
                    class="pl-9 pr-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-600 font-mono w-48 sm:w-56 shadow-xs">
            </div>
            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-xs transition-colors">
                Lacak
            </button>
            @if(request('q'))
                <a href="{{ route('tracking.index') }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- INTERACTIVE TRACKING MAP (MULTIMODAL: UDARA, DARAT & LAUT) -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <!-- MAP TOOLBAR & MODA SELECTOR -->
        <div class="p-3.5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 bg-slate-50/50">
            <!-- Left: Title, Live Indicator & Moda Tabs -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1.5 mr-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Radar Multimoda</h3>
                </div>

                @php
                    $airCount = collect($mapShipmentsData)->where('carrier.transport_type', 'air')->count();
                    $landCount = collect($mapShipmentsData)->where('carrier.transport_type', 'land')->count();
                    $seaCount = collect($mapShipmentsData)->where('carrier.transport_type', 'sea')->count();
                @endphp

                <!-- Transport Mode Buttons: ALL, AIR, LAND, SEA -->
                <div class="inline-flex items-center rounded-lg bg-slate-200/70 p-0.5 text-xs font-semibold">
                    <button type="button" onclick="filterByTransportMode('ALL')" id="tab-mode-ALL"
                        class="mode-tab-btn px-2.5 py-1 rounded-md bg-teal-600 text-white shadow-xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>🌐 Semua ({{ count($mapShipmentsData) }})</span>
                    </button>
                    <button type="button" onclick="filterByTransportMode('air')" id="tab-mode-air"
                        class="mode-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all flex items-center gap-1 cursor-pointer">
                        <span>✈️ Udara ({{ $airCount }})</span>
                    </button>
                    <button type="button" onclick="filterByTransportMode('land')" id="tab-mode-land"
                        class="mode-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all flex items-center gap-1 cursor-pointer">
                        <span>🚚 Darat ({{ $landCount }})</span>
                    </button>
                    <button type="button" onclick="filterByTransportMode('sea')" id="tab-mode-sea"
                        class="mode-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all flex items-center gap-1 cursor-pointer">
                        <span>🚢 Laut ({{ $seaCount }})</span>
                    </button>
                </div>
            </div>

            <!-- Right Controls: Status filter, Focus dropdown & Reset Map -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Status Filter Pills -->
                <div class="flex items-center bg-slate-100 p-0.5 rounded-lg text-xs font-medium text-slate-600">
                    <a href="{{ route('tracking.index') }}" class="px-2 py-1 rounded-md {{ !request('status') || request('status') === 'ALL' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'hover:text-slate-900' }}">Semua Status</a>
                    <a href="{{ route('tracking.index', ['status' => 'ACTIVE']) }}" class="px-2 py-1 rounded-md {{ request('status') === 'ACTIVE' ? 'bg-white text-teal-800 font-bold shadow-2xs' : 'hover:text-slate-900' }}">Aktif</a>
                    <a href="{{ route('tracking.index', ['status' => 'DELIVERED']) }}" class="px-2 py-1 rounded-md {{ request('status') === 'DELIVERED' ? 'bg-white text-emerald-800 font-bold shadow-2xs' : 'hover:text-slate-900' }}">Terkirim</a>
                </div>

                <!-- Quick Focus Dropdown -->
                <select id="shipment-focus-selector" onchange="focusRoute(this.value)" class="pl-2.5 pr-7 py-1 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:border-teal-600 cursor-pointer shadow-2xs">
                    <option value="ALL">🔍 Pilih Resi...</option>
                    @foreach($mapShipmentsData as $s)
                        <option value="{{ $s['id'] }}">
                            {{ $s['carrier']['transport_type'] === 'air' ? '✈️' : ($s['carrier']['transport_type'] === 'land' ? '🚚' : '🚢') }}
                            {{ $s['shipment_number'] }} • {{ $s['destination']['city'] ?? '' }}
                        </option>
                    @endforeach
                </select>

                <!-- Reset Map View Button -->
                <button type="button" onclick="resetMapView()" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors flex items-center gap-1 shadow-2xs" title="Pusatkan Peta dan Tampilkan Semua Rute">
                    <i data-lucide="maximize" class="w-3.5 h-3.5 text-teal-600"></i>
                    <span>Pusatkan</span>
                </button>
            </div>
        </div>

        <!-- QUICK CHIPS BAR FOR EACH ROUTE (WITH MODA ICONS) -->
        <div class="px-3 py-2 bg-slate-50 border-b border-slate-100 flex items-center gap-2 overflow-x-auto text-xs">
            <button type="button" onclick="focusRoute('ALL')" id="chip-all" 
                class="px-2.5 py-1 rounded-lg font-bold text-xs bg-teal-600 text-white shadow-2xs border border-teal-700 flex items-center gap-1 shrink-0 transition-all cursor-pointer">
                <span>🌐 Semua</span>
            </button>
            @foreach($mapShipmentsData as $idx => $s)
                @php
                    $sMode = $s['carrier']['transport_type'] ?? 'air';
                    $modeColor = $sMode === 'air' ? '#0284c7' : ($sMode === 'sea' ? '#0d9488' : '#d97706');
                    $modeEmoji = $sMode === 'air' ? '✈️' : ($sMode === 'sea' ? '🚢' : '🚚');
                @endphp
                <button type="button" onclick="focusRoute({{ $s['id'] }})" id="chip-{{ $s['id'] }}" data-mode="{{ $sMode }}"
                    class="route-chip px-2.5 py-1 rounded-lg font-medium text-xs bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs flex items-center gap-1.5 shrink-0 transition-all cursor-pointer">
                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $modeColor }}"></span>
                    <span>{{ $modeEmoji }} {{ $s['shipment_number'] }} <span class="text-slate-400 font-normal">({{ $s['destination']['city'] ?? '' }})</span></span>
                </button>
            @endforeach
        </div>

        <!-- MAP CANVAS -->
        <div class="relative w-full h-[520px] overflow-hidden">
            <div id="full-tracking-map" class="w-full h-full z-0"></div>

            <!-- Floating Pan & Navigation D-Pad Controls -->
            <div class="absolute top-3 left-14 z-[400] flex items-center gap-1 bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-lg p-1 shadow-md">
                <span class="text-[10px] font-bold text-slate-500 uppercase px-1.5 hidden sm:inline">Geser:</span>
                <button type="button" onclick="panTrackingMap(0, -180)" title="Geser ke Atas"
                    class="w-7 h-7 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                    <i data-lucide="arrow-up" class="w-3.5 h-3.5"></i>
                </button>
                <button type="button" onclick="panTrackingMap(0, 180)" title="Geser ke Bawah"
                    class="w-7 h-7 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                    <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
                </button>
                <button type="button" onclick="panTrackingMap(-180, 0)" title="Geser ke Kiri"
                    class="w-7 h-7 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                </button>
                <button type="button" onclick="panTrackingMap(180, 0)" title="Geser ke Kanan"
                    class="w-7 h-7 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </button>
                <div class="h-4 w-px bg-slate-200 mx-0.5"></div>
                <button type="button" onclick="resetTrackingMapCenter()" title="Pusatkan Peta (Reset)"
                    class="px-2 h-7 rounded bg-teal-50 hover:bg-teal-600 hover:text-white text-teal-800 flex items-center gap-1 transition-colors cursor-pointer text-xs font-semibold">
                    <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                    <span class="text-[11px] hidden sm:inline">Pusat</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MONITORED SHIPMENTS LIST -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Daftar Pengiriman</h3>
            <span class="text-xs text-slate-400 font-mono">{{ $allShipments->total() }} Total</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-medium">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Resi</th>
                        <th class="py-3 px-4 font-semibold">Kurir</th>
                        <th class="py-3 px-4 font-semibold">Komoditas</th>
                        <th class="py-3 px-4 font-semibold">Rute</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($allShipments as $shipment)
                    @php
                        $tType = $shipment->carrier?->transport_type ?? 'air';
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors cursor-pointer group" onclick="focusShipmentOnMap({{ $shipment->id }})" id="shipment-row-{{ $shipment->id }}" data-mode="{{ $tType }}">
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">
                            <div class="flex items-center gap-1.5">
                                @if($tType === 'land')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200" title="Kargo Darat">🚚 Darat</span>
                                @elseif($tType === 'sea')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-200" title="Kargo Laut">🚢 Laut</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-800 border border-sky-200" title="Kargo Udara">✈️ Udara</span>
                                @endif
                                <span class="group-hover:text-teal-700 transition-colors">{{ $shipment->shipment_number }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-800 font-medium">
                            {{ $shipment->carrier?->name }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-medium text-slate-900 block">{{ $shipment->items->first()?->product_name ?? $shipment->cargo_type }}</span>
                            @if($shipment->items->first())
                                <span class="text-[10px] text-teal-700 font-mono">
                                    {{ number_format($shipment->items->first()->quantity_vials) }} vial
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 font-medium">
                            <span class="flex items-center gap-1">
                                <span>{{ $shipment->origin->city }}</span>
                                <span class="text-teal-600">&rarr;</span>
                                <span>{{ $shipment->destination->city }}</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($shipment->is_delayed)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-600 text-white shadow-2xs">TERTUNDA</span>
                            @elseif($shipment->current_status === 'DELIVERED')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white shadow-2xs">TERKIRIM</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-600 text-white shadow-2xs">DALAM PERJALANAN</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" onclick="event.stopPropagation(); focusShipmentOnMap({{ $shipment->id }})" title="Lihat di Peta" class="p-1.5 rounded-lg text-slate-400 hover:text-teal-700 hover:bg-slate-100 transition-colors">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                </button>
                                <a href="{{ route('shipments.show', $shipment->id) }}" title="Detail" class="p-1.5 rounded-lg text-slate-400 hover:text-teal-700 hover:bg-slate-100 transition-colors">
                                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400 font-mono">
                            Tidak ada data pengiriman.
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

<!-- CLEAN CUSTOM POPUP STYLING -->
<style>
    .cargopharma-popup .leaflet-popup-content-wrapper {
        padding: 0;
        overflow: hidden;
        border-radius: 10px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }
    .cargopharma-popup .leaflet-popup-content {
        margin: 0;
        line-height: 1.4;
        width: 275px !important;
    }
    .cargopharma-popup .leaflet-popup-tip {
        background: #ffffff;
    }
</style>

@push('scripts')
<script>
    const mapShipmentsData = @json($mapShipmentsData);
    let map;
    const shipmentMarkers = {};
    const shipmentPolylines = {};
    const shipmentBounds = {};

    // Helper: Calculate initial bearing (0-360 deg) for vehicle orientation
    function getBearing(lat1, lon1, lat2, lon2) {
        const toRad = deg => deg * Math.PI / 180;
        const toDeg = rad => rad * 180 / Math.PI;
        const dLon = toRad(lon2 - lon1);
        const y = Math.sin(dLon) * Math.cos(toRad(lat2));
        const x = Math.cos(toRad(lat1)) * Math.sin(toRad(lat2)) -
                  Math.sin(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.cos(dLon);
        const brng = toDeg(Math.atan2(y, x));
        return (brng + 360) % 360;
    }

    // Helper: Build compact, essential-only popup HTML
    function buildCargoPopupHtml(shipment, nodeType, nodeData) {
        const item = shipment.primary_item || {
            product_name: shipment.cargo_type,
            batch_number: 'N/A',
            quantity_vials: 0
        };

        const mode = shipment.carrier.transport_type;
        const modeIcon = mode === 'air' ? '✈️' : (mode === 'land' ? '🚚' : '🚢');

        const statusBadge = shipment.is_delayed ? 
            `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-600 text-white">TERTUNDA</span>` : 
            `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-600 text-white">${shipment.current_status}</span>`;

        let locName = nodeType === 'ORIGIN' ? `${nodeData.city || nodeData.name}` :
                      nodeType === 'DESTINATION' ? `${nodeData.city || nodeData.name}` :
                      nodeType === 'CHECKPOINT' ? `${nodeData.city || nodeData.name}` :
                      `${shipment.origin.city} &rarr; ${shipment.destination.city}`;

        const containerTemp = (nodeData && nodeData.container_temp !== undefined && nodeData.container_temp !== null) ? 
            nodeData.container_temp : (shipment.latest_event ? shipment.latest_event.container_temp : null);

        return `
            <div class="font-sans text-slate-800 text-xs p-2.5 space-y-2 min-w-[210px]">
                <!-- Header: Icon, Resi, Status -->
                <div class="flex items-center justify-between gap-2 pb-1.5 border-b border-slate-100">
                    <span class="font-mono font-bold text-slate-900 text-xs flex items-center gap-1.5">
                        <span>${modeIcon}</span>
                        <span>${shipment.shipment_number}</span>
                    </span>
                    ${statusBadge}
                </div>

                <!-- Location / Route & Carrier -->
                <div class="flex items-center justify-between text-[11px] text-slate-600">
                    <span class="font-semibold text-slate-800">${locName}</span>
                    <span class="text-slate-400 font-mono text-[10px]">${shipment.carrier.name}</span>
                </div>

                <!-- Item & Temp Tile -->
                <div class="p-2 rounded-lg bg-teal-50/80 border border-teal-200/80 flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <div class="font-bold text-slate-900 text-[11px] truncate">${item.product_name}</div>
                        <div class="text-[10px] text-teal-800 font-mono">${Number(item.quantity_vials).toLocaleString()} vials</div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="font-mono font-bold text-xs text-teal-950">
                            ${containerTemp !== null ? containerTemp + '&deg;C' : 'N/A'}
                        </div>
                        <div class="text-[9px] text-slate-500 font-mono">${shipment.target_temp_min}&deg;~${shipment.target_temp_max}&deg;C</div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="pt-0.5 text-right">
                    <a href="${shipment.show_url}" class="px-2.5 py-1 rounded bg-teal-600 hover:bg-teal-700 text-white font-semibold text-[10px] inline-flex items-center gap-1 shadow-2xs">
                        <span>Detail</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        `;
    }

    const routeColors = ['#0d9488', '#2563eb', '#7c3aed', '#d97706', '#e11d48', '#0891b2', '#4f46e5', '#ca8a04'];
    const allMapBounds = [];

    // Filter by Transport Mode (ALL, air, land, sea)
    window.currentTransportMode = 'ALL';
    window.filterByTransportMode = function(mode) {
        window.currentTransportMode = mode;

        // Update mode tab buttons UI
        document.querySelectorAll('.mode-tab-btn').forEach(btn => {
            btn.classList.remove('bg-teal-600', 'text-white', 'shadow-xs');
            btn.classList.add('text-slate-700');
        });
        const activeBtn = document.getElementById('tab-mode-' + mode);
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-700');
            activeBtn.classList.add('bg-teal-600', 'text-white', 'shadow-xs');
        }

        const filteredBounds = [];

        mapShipmentsData.forEach(s => {
            const sMode = s.carrier.transport_type; // 'air', 'land', 'sea'
            const matches = (mode === 'ALL' || sMode === mode);

            // Filter quick chips
            const chip = document.getElementById('chip-' + s.id);
            if (chip) {
                chip.style.display = matches ? 'inline-flex' : 'none';
            }

            // Filter table rows
            const row = document.getElementById('shipment-row-' + s.id);
            if (row) {
                row.style.display = matches ? '' : 'none';
            }

            // Map polyline visibility & style
            if (shipmentPolylines[s.id]) {
                shipmentPolylines[s.id].setStyle({
                    opacity: matches ? 0.85 : 0.05,
                    weight: matches ? (sMode === 'air' ? 3.5 : 4.5) : 1
                });
                if (matches) shipmentPolylines[s.id].bringToFront();
            }

            // Map markers opacity
            const m = shipmentMarkers[s.id];
            if (m) {
                const op = matches ? 1.0 : 0.08;
                if (m.origin) m.origin.setOpacity(op);
                if (m.destination) m.destination.setOpacity(op);
                if (m.vehicle) m.vehicle.setOpacity(op);
                if (m.waypoints) m.waypoints.forEach(w => w.setOpacity(op));
            }

            if (matches && shipmentBounds[s.id]) {
                shipmentBounds[s.id].forEach(pt => filteredBounds.push(pt));
            }
        });

        if (filteredBounds.length > 0 && map) {
            map.fitBounds(L.latLngBounds(filteredBounds), {
                padding: [50, 50],
                maxZoom: (mode === 'land' ? 9 : (mode === 'sea' ? 6 : 5))
            });
        }
    };

    // Reset View
    window.resetMapView = function() {
        focusRoute('ALL');
        filterByTransportMode('ALL');
    };

    // Function to isolate, highlight, or reset routes on the map
    window.focusRoute = function(shipmentId) {
        const selector = document.getElementById('shipment-focus-selector');
        
        // Reset all chips styling
        document.querySelectorAll('.route-chip').forEach(el => {
            el.classList.remove('bg-teal-600', 'text-white', 'font-bold', 'border-teal-700');
            el.classList.add('bg-white', 'text-slate-700', 'font-medium', 'border-slate-200');
        });
        const chipAll = document.getElementById('chip-all');
        if (chipAll) {
            chipAll.classList.remove('bg-teal-600', 'text-white', 'font-bold', 'border-teal-700');
            chipAll.classList.add('bg-white', 'text-slate-700', 'font-medium', 'border-slate-200');
        }

        if (shipmentId === 'ALL') {
            if (selector) selector.value = 'ALL';
            if (chipAll) {
                chipAll.classList.remove('bg-white', 'text-slate-700', 'font-medium', 'border-slate-200');
                chipAll.classList.add('bg-teal-600', 'text-white', 'font-bold', 'border-teal-700');
            }

            // Restore ALL polylines and markers to full visibility
            mapShipmentsData.forEach(s => {
                const sMode = s.carrier.transport_type;
                if (shipmentPolylines[s.id]) {
                    shipmentPolylines[s.id].setStyle({ opacity: 0.85, weight: (sMode === 'air' ? 3.5 : 4.5) });
                }
                const m = shipmentMarkers[s.id];
                if (m) {
                    if (m.origin) m.origin.setOpacity(1.0);
                    if (m.destination) m.destination.setOpacity(1.0);
                    if (m.vehicle) m.vehicle.setOpacity(1.0);
                    if (m.waypoints) m.waypoints.forEach(w => w.setOpacity(1.0));
                }
            });

            // Fit bounds to show ALL routes
            if (allMapBounds.length > 0) {
                map.fitBounds(L.latLngBounds(allMapBounds), { padding: [50, 50], maxZoom: 6 });
            }
            return;
        }

        // Specific shipment selected
        const sId = parseInt(shipmentId, 10);
        if (selector) selector.value = sId;

        const activeChip = document.getElementById('chip-' + sId);
        if (activeChip) {
            activeChip.classList.remove('bg-white', 'text-slate-700', 'font-medium', 'border-slate-200');
            activeChip.classList.add('bg-teal-600', 'text-white', 'font-bold', 'border-teal-700');
        }

        // HIGHLIGHT selected route & DIM others
        mapShipmentsData.forEach(s => {
            const isTarget = (s.id === sId);
            if (shipmentPolylines[s.id]) {
                if (isTarget) {
                    shipmentPolylines[s.id].setStyle({ opacity: 1.0, weight: 6 });
                    shipmentPolylines[s.id].bringToFront();
                } else {
                    shipmentPolylines[s.id].setStyle({ opacity: 0.12, weight: 1.5 });
                }
            }
            const m = shipmentMarkers[s.id];
            if (m) {
                const targetOpacity = isTarget ? 1.0 : 0.15;
                if (m.origin) m.origin.setOpacity(targetOpacity);
                if (m.destination) m.destination.setOpacity(targetOpacity);
                if (m.vehicle) m.vehicle.setOpacity(targetOpacity);
                if (m.waypoints) m.waypoints.forEach(w => w.setOpacity(targetOpacity));
            }
        });

        // Zoom directly to target route bounds smoothly
        if (shipmentBounds[sId] && shipmentBounds[sId].length > 0) {
            map.fitBounds(L.latLngBounds(shipmentBounds[sId]), { padding: [70, 70], maxZoom: 8, animate: true, duration: 1.0 });
        }

        // Open vehicle popup or destination popup
        setTimeout(() => {
            const m = shipmentMarkers[sId];
            if (m && m.vehicle) {
                m.vehicle.openPopup();
            } else if (m && m.destination) {
                m.destination.openPopup();
            }
        }, 400);
    };

    window.focusShipmentOnMap = function(shipmentId) {
        focusRoute(shipmentId);
        const mapEl = document.getElementById('full-tracking-map');
        if (mapEl) {
            mapEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    };

    document.addEventListener("DOMContentLoaded", function () {
        // High-Quality Tile Basemaps
        const osmStreet = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        });

        const esriStreet = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: 'Tiles &copy; Esri'
        });

        const esriSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: 'Tiles &copy; Esri &mdash; Satellite'
        });

        map = L.map('full-tracking-map', {
            center: [15.0, 55.0],
            zoom: 3,
            layers: [osmStreet],
            dragging: true,
            touchZoom: true,
            scrollWheelZoom: true,
            doubleClickZoom: true,
            boxZoom: true,
            tap: false,
            keyboard: true,
            inertia: true,
            inertiaDeceleration: 3000
        });

        map.dragging.enable();
        if (map.keyboard) map.keyboard.enable();

        window.panTrackingMap = function(dx, dy) {
            if (map) {
                map.panBy([dx, dy], { animate: true, duration: 0.35 });
            }
        };

        window.resetTrackingMapCenter = function() {
            if (map) {
                map.setView([15.0, 55.0], 3, { animate: true });
            }
        };

        L.control.layers({
            "Peta Jalan (OSM)": osmStreet,
            "Esri Street Map": esriStreet,
            "Satelit (Esri)": esriSatellite
        }, null, { position: 'topright' }).addTo(map);

        setTimeout(() => { map.invalidateSize(); }, 250);
        window.addEventListener('resize', () => { map.invalidateSize(); });

        // RENDER EACH SHIPMENT ON THE MAP
        mapShipmentsData.forEach((shipment, sIdx) => {
            const mode = shipment.carrier.transport_type; // 'air', 'land', 'sea'
            
            // Mode-specific route colors: Air=Sky Blue, Sea=Marine Teal, Land=Warm Amber
            let routeColor = mode === 'air' ? '#0284c7' : (mode === 'sea' ? '#0d9488' : '#d97706');
            if (shipment.is_delayed) {
                routeColor = '#e11d48'; // Red for delay
            }

            shipmentMarkers[shipment.id] = { waypoints: [], color: routeColor };
            const thisShipmentBounds = [];
            const routeCoords = [];

            // 1. ORIGIN MARKER
            if (shipment.origin && shipment.origin.lat && shipment.origin.lng) {
                const originPt = [shipment.origin.lat, shipment.origin.lng];
                routeCoords.push(originPt);
                thisShipmentBounds.push(originPt);
                allMapBounds.push(originPt);

                const originEmoji = mode === 'air' ? '🛫' : (mode === 'sea' ? '⚓' : '🏭');

                const originIconHtml = `
                    <div class="relative group cursor-pointer flex items-center justify-center w-7 h-7 rounded-full bg-slate-900 border-2 shadow-md text-white font-bold text-xs hover:scale-115 transition-transform" style="border-color: ${routeColor};">
                        <span>${originEmoji}</span>
                    </div>
                `;

                const originMarker = L.marker(originPt, {
                    icon: L.divIcon({
                        className: 'cargopharma-origin-marker',
                        html: originIconHtml,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14]
                    })
                }).addTo(map);

                const originPopup = buildCargoPopupHtml(shipment, 'ORIGIN', shipment.origin);
                originMarker.bindPopup(originPopup, { className: 'cargopharma-popup' });
                shipmentMarkers[shipment.id].origin = originMarker;
            }

            // 2. INTERMEDIATE CHECKPOINTS
            const checkpointList = shipment.waypoints.filter(w => w.type === 'CHECKPOINT');
            checkpointList.forEach(cp => {
                const cpPt = [cp.lat, cp.lng];
                routeCoords.push(cpPt);
                thisShipmentBounds.push(cpPt);
                allMapBounds.push(cpPt);

                const cpEmoji = mode === 'sea' ? '⚓' : '🔄';

                const cpIconHtml = `
                    <div class="relative group cursor-pointer flex items-center justify-center w-6 h-6 rounded-full bg-slate-900 border-2 shadow-sm text-white text-[9px] hover:scale-115 transition-transform" style="border-color: ${routeColor};">
                        <span>${cpEmoji}</span>
                    </div>
                `;

                const cpMarker = L.marker(cpPt, {
                    icon: L.divIcon({
                        className: 'cargopharma-cp-marker',
                        html: cpIconHtml,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    })
                }).addTo(map);

                const cpPopup = buildCargoPopupHtml(shipment, 'CHECKPOINT', cp);
                cpMarker.bindPopup(cpPopup, { className: 'cargopharma-popup' });
                shipmentMarkers[shipment.id].waypoints.push(cpMarker);
            });

            // 3. DESTINATION MARKER
            if (shipment.destination && shipment.destination.lat && shipment.destination.lng) {
                const destPt = [shipment.destination.lat, shipment.destination.lng];
                routeCoords.push(destPt);
                thisShipmentBounds.push(destPt);
                allMapBounds.push(destPt);

                const destEmoji = mode === 'air' ? '🛬' : (mode === 'sea' ? '⚓' : '🏥');

                const destIconHtml = `
                    <div class="relative group cursor-pointer flex items-center justify-center w-7 h-7 rounded-full bg-slate-900 border-2 shadow-md text-white font-bold text-xs hover:scale-115 transition-transform" style="border-color: ${routeColor};">
                        <span>${destEmoji}</span>
                    </div>
                `;

                const destMarker = L.marker(destPt, {
                    icon: L.divIcon({
                        className: 'cargopharma-dest-marker',
                        html: destIconHtml,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14]
                    })
                }).addTo(map);

                const destPopup = buildCargoPopupHtml(shipment, 'DESTINATION', shipment.destination);
                destMarker.bindPopup(destPopup, { className: 'cargopharma-popup' });
                shipmentMarkers[shipment.id].destination = destMarker;
            }

            // 4. ROUTE POLYLINE (Realistic Highway OSRM, Great-Circle Aviation Arc, or Maritime Sea-Lane)
            const polylinePoints = (shipment.route_geometry && shipment.route_geometry.length >= 2) 
                ? shipment.route_geometry 
                : routeCoords;

            if (polylinePoints.length >= 2) {
                const polyline = L.polyline(polylinePoints, {
                    color: routeColor,
                    weight: mode === 'air' ? 3.5 : 4.5,
                    opacity: 0.85,
                    dashArray: mode === 'air' ? '8, 8' : 'none'
                }).addTo(map);

                polyline.on('click', () => {
                    focusRoute(shipment.id);
                });

                const modeLabel = mode === 'air' ? '✈️ Kargo Udara (Flight Path)' : (mode === 'sea' ? '🚢 Kargo Laut (Maritime Lane)' : '🚚 Kargo Darat (Highway Tol)');

                polyline.bindTooltip(`
                    <div class="text-xs font-semibold p-1">
                        <span class="font-mono text-teal-300 font-bold">${shipment.shipment_number}</span><br>
                        <span class="text-[10px] text-slate-200">${modeLabel}</span><br>
                        ${shipment.origin.city} &rarr; ${shipment.destination.city}<br>
                        ${shipment.distance_km ? '<span class="text-[10px] text-teal-200 font-mono">📏 ' + Number(shipment.distance_km).toLocaleString('id-ID') + ' km</span><br>' : ''}
                        <span class="text-[10px] text-slate-300">Klik untuk fokus rute ini</span>
                    </div>
                `, { sticky: true, className: 'cargopharma-tooltip' });

                shipmentPolylines[shipment.id] = polyline;
            }

            // 5. ACTIVE VEHICLE MARKER ON THE ROUTE (Airplane ✈️ / Truck 🚚 / Ship 🚢)
            const vPos = shipment.current_vehicle_position;
            if (vPos && vPos.lat && vPos.lng) {
                const vehiclePt = [vPos.lat, vPos.lng];
                thisShipmentBounds.push(vehiclePt);

                const bearing = getBearing(vPos.lat, vPos.lng, vPos.target_lat, vPos.target_lng);

                let vehicleHtml = '';
                if (mode === 'air') {
                    vehicleHtml = `
                        <div class="relative group cursor-pointer">
                            <div class="absolute -inset-2 rounded-full opacity-40 animate-ping bg-sky-400"></div>
                            <div class="relative flex items-center justify-center w-9 h-9 rounded-full bg-slate-900 border-2 shadow-xl text-white transform hover:scale-120 transition-transform" style="border-color: #38bdf8;">
                                <svg style="transform: rotate(${bearing}deg); transform-origin: center; color: #38bdf8;" class="w-5 h-5 drop-shadow" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                                </svg>
                            </div>
                            <div class="absolute -bottom-4.5 left-1/2 -translate-x-1/2 whitespace-nowrap px-1.5 py-0.2 rounded-full bg-slate-900/90 text-[9px] font-mono font-bold text-sky-300 border border-sky-400/50 shadow">
                                ✈️ ${shipment.carrier.name}
                            </div>
                        </div>
                    `;
                } else if (mode === 'land') {
                    vehicleHtml = `
                        <div class="relative group cursor-pointer">
                            <div class="absolute -inset-2 rounded-full opacity-40 animate-ping bg-amber-400"></div>
                            <div class="relative flex items-center justify-center w-9 h-9 rounded-full bg-slate-900 border-2 shadow-xl text-white transform hover:scale-120 transition-transform" style="border-color: #f59e0b;">
                                <svg class="w-5 h-5 drop-shadow text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18 18.5a1.5 1.5 0 0 1-1.5-1.5 1.5 1.5 0 0 1 1.5-1.5 1.5 1.5 0 0 1 1.5 1.5 1.5 1.5 0 0 1-1.5 1.5m1.5-9H17V12h4.5M6 18.5A1.5 1.5 0 0 1 4.5 17 1.5 1.5 0 0 1 6 15.5 1.5 1.5 0 0 1 7.5 17 1.5 1.5 0 0 1 6 18.5M20 8h-3V4H1v13h2a3 3 0 0 0 6 0h6a3 3 0 0 0 6 0h2v-6l-3-3z"/>
                                </svg>
                            </div>
                            <div class="absolute -bottom-4.5 left-1/2 -translate-x-1/2 whitespace-nowrap px-1.5 py-0.2 rounded-full bg-slate-900/90 text-[9px] font-mono font-bold text-amber-300 border border-amber-400/50 shadow">
                                🚚 ${shipment.carrier.name}
                            </div>
                        </div>
                    `;
                } else {
                    vehicleHtml = `
                        <div class="relative group cursor-pointer">
                            <div class="absolute -inset-2 rounded-full opacity-40 animate-ping bg-teal-400"></div>
                            <div class="relative flex items-center justify-center w-9 h-9 rounded-full bg-slate-900 border-2 shadow-xl text-white transform hover:scale-120 transition-transform" style="border-color: #2dd4bf;">
                                <svg class="w-5 h-5 drop-shadow text-teal-300" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 21c-1.39 0-2.78-.47-4-1.32-2.44 1.71-5.56 1.71-8 0C6.78 20.53 5.39 21 4 21H2v2h2c1.38 0 2.74-.35 4-.99 2.52 1.29 5.48 1.29 8 0 1.26.64 2.62.99 4 .99h2v-2h-2M3.95 19H4c1.6 0 3.02-.88 4-2 .98 1.12 2.4 2 4 2s3.02-.88 4-2c.98 1.12 2.4 2 4 2h.05l1.89-6.68c.08-.26.06-.54-.06-.78s-.32-.42-.58-.49L20 10.7V6c0-1.11-.89-2-2-2h-3V1h-2v3h-2V1H9v3H6c-1.11 0-2 .89-2 2v4.7l-1.3.35c-.26.07-.46.25-.58.49s-.14.52-.06.78L3.95 19z"/>
                                </svg>
                            </div>
                            <div class="absolute -bottom-4.5 left-1/2 -translate-x-1/2 whitespace-nowrap px-1.5 py-0.2 rounded-full bg-slate-900/90 text-[9px] font-mono font-bold text-teal-300 border border-teal-400/50 shadow">
                                🚢 ${shipment.carrier.name}
                            </div>
                        </div>
                    `;
                }

                const vehicleMarker = L.marker(vehiclePt, {
                    icon: L.divIcon({
                        className: 'cargopharma-vehicle-marker',
                        html: vehicleHtml,
                        iconSize: [36, 36],
                        iconAnchor: [18, 18]
                    }),
                    zIndexOffset: 1000
                }).addTo(map);

                const vehiclePopup = buildCargoPopupHtml(shipment, 'VEHICLE', {
                    container_temp: shipment.latest_event ? shipment.latest_event.container_temp : null
                });
                vehicleMarker.bindPopup(vehiclePopup, { className: 'cargopharma-popup' });
                shipmentMarkers[shipment.id].vehicle = vehicleMarker;
            }

            shipmentBounds[shipment.id] = thisShipmentBounds;
        });

        // Fit map bounds
        if (allMapBounds.length > 0) {
            map.fitBounds(L.latLngBounds(allMapBounds), { padding: [50, 50], maxZoom: 6 });
        }

        // Handle dropdown filter
        const focusSelector = document.getElementById('shipment-focus-selector');
        if (focusSelector) {
            focusSelector.addEventListener('change', function () {
                focusRoute(this.value);
            });
        }
    });
</script>
@endpush
@endsection
