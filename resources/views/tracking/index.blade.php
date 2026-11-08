@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER & SEARCH -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Active Shipment Tracking</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pelacakan posisi armada real-time dan pemantauan telemetri rantai dingin vaksin.</p>
        </div>

        <form action="{{ route('tracking.index') }}" method="GET" class="flex items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari AWB, vaksin, batch..." 
                    class="pl-9 pr-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-600 font-mono w-52 sm:w-60 shadow-xs">
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

    <!-- INTERACTIVE TRACKING MAP -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <!-- MAP TOOLBAR -->
        <div class="p-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Peta Koridor & Posisi Armada</h3>
            </div>

            <!-- QUICK FILTER & LEGEND -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Quick Focus Dropdown -->
                <select id="shipment-focus-selector" class="pl-2.5 pr-7 py-1 rounded-lg border border-slate-200 bg-slate-50 text-xs font-medium text-slate-700 focus:outline-none focus:border-teal-600 cursor-pointer shadow-2xs">
                    <option value="ALL">🌐 Tampilkan Semua Rute</option>
                    @foreach($mapShipmentsData as $s)
                        <option value="{{ $s['id'] }}">
                            {{ $s['carrier']['transport_type'] === 'air' ? '✈️' : ($s['carrier']['transport_type'] === 'land' ? '🚚' : '🚢') }}
                            {{ $s['shipment_number'] }} • {{ $s['carrier']['name'] }}
                        </option>
                    @endforeach
                </select>

                <!-- Legend -->
                <div class="flex items-center gap-2.5 text-[11px] text-slate-500">
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-teal-600"></span> ✈️ Udara
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span> 🚚 Darat
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> ⚠️ Delay
                    </span>
                </div>
            </div>
        </div>

        <!-- MAP CANVAS -->
        <div id="full-tracking-map" class="w-full h-[500px] z-0"></div>
    </div>

    <!-- MONITORED SHIPMENTS LIST -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="layers" class="w-4 h-4 text-teal-600"></i>
                <h3 class="text-sm font-bold text-slate-900">Daftar Pengiriman Farmasi & Vaksin</h3>
            </div>
            <span class="text-xs text-slate-400">Total: {{ $allShipments->total() }} pengiriman</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-medium">
                    <tr>
                        <th class="py-3 px-4 font-semibold">No. Pengiriman (AWB)</th>
                        <th class="py-3 px-4 font-semibold">Moda Transportasi</th>
                        <th class="py-3 px-4 font-semibold">Komoditas & Vaksin</th>
                        <th class="py-3 px-4 font-semibold">Asal</th>
                        <th class="py-3 px-4 font-semibold">Tujuan</th>
                        <th class="py-3 px-4 font-semibold">Status Muatan</th>
                        <th class="py-3 px-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($allShipments as $shipment)
                    <tr class="hover:bg-slate-50/80 transition-colors cursor-pointer group" onclick="focusShipmentOnMap({{ $shipment->id }})">
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">
                            <span class="group-hover:text-teal-700 flex items-center gap-1.5">
                                <span class="text-xs">
                                    {{ $shipment->carrier?->transport_type === 'land' ? '🚚' : ($shipment->carrier?->transport_type === 'sea' ? '🚢' : '✈️') }}
                                </span>
                                {{ $shipment->shipment_number }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-medium text-slate-800 block">{{ $shipment->carrier?->name }}</span>
                            <span class="text-[10px] text-slate-400 uppercase font-mono">{{ $shipment->carrier?->transport_type ?? 'Air' }} Freight</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-medium text-slate-900 block">{{ $shipment->items->first()?->product_name ?? $shipment->cargo_type }}</span>
                            @if($shipment->items->first())
                                <span class="text-[10px] text-teal-700 font-mono">
                                    Batch: {{ $shipment->items->first()->batch_number }} • {{ number_format($shipment->items->first()->quantity_vials) }} vials
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $shipment->origin->city }} ({{ $shipment->origin->code }})
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $shipment->destination->city }} ({{ $shipment->destination->code }})
                        </td>
                        <td class="py-3.5 px-4">
                            @if($shipment->is_delayed)
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-600 text-white shadow-2xs">DELAYED (+{{ $shipment->delay_duration_hours }}h)</span>
                            @elseif($shipment->current_status === 'DELIVERED')
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-600 text-white shadow-2xs">DELIVERED</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-teal-600 text-white shadow-2xs">IN TRANSIT</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right space-x-2">
                            <button type="button" onclick="event.stopPropagation(); focusShipmentOnMap({{ $shipment->id }})" class="px-2 py-1 rounded bg-slate-100 hover:bg-teal-50 text-teal-800 font-semibold text-[11px]">
                                Sorot di Peta 🎯
                            </button>
                            <a href="{{ route('shipments.show', $shipment->id) }}" class="text-teal-700 hover:text-teal-900 font-semibold hover:underline">
                                Detail &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 font-mono">
                            Tidak ada data pengiriman yang cocok.
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
            `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-600 text-white">DELAYED</span>` : 
            `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-600 text-white">${shipment.current_status}</span>`;

        // Node / Waypoint label
        let locLabel = '';
        if (nodeType === 'ORIGIN') {
            locLabel = `🛫 <b>Asal:</b> ${nodeData.name || nodeData.city} (${nodeData.code || ''})`;
        } else if (nodeType === 'DESTINATION') {
            locLabel = `🛬 <b>Tujuan:</b> ${nodeData.name || nodeData.city} (${nodeData.code || ''})`;
        } else if (nodeType === 'CHECKPOINT') {
            locLabel = `🔄 <b>Transit:</b> ${nodeData.name || nodeData.city} (${nodeData.code || ''})`;
        } else {
            locLabel = `${modeIcon} <b>Armada Aktif:</b> ${shipment.latest_event ? shipment.latest_event.status_snapshot : 'En-route'}`;
        }

        const containerTemp = (nodeData && nodeData.container_temp !== undefined && nodeData.container_temp !== null) ? 
            nodeData.container_temp : (shipment.latest_event ? shipment.latest_event.container_temp : null);

        return `
            <div class="font-sans text-slate-800 text-xs p-3 space-y-2">
                <!-- HEADER: NO RESI & STATUS -->
                <div class="flex items-center justify-between pb-1.5 border-b border-slate-100">
                    <div class="flex items-center gap-1.5 font-bold text-slate-900">
                        <span class="text-sm">${modeIcon}</span>
                        <span class="font-mono text-xs text-teal-800 font-bold">${shipment.shipment_number}</span>
                    </div>
                    ${statusBadge}
                </div>

                <!-- LOKASI SAAT INI / WAYPOINT -->
                <div class="text-[11px] text-slate-600 bg-slate-50 px-2 py-1 rounded border border-slate-200/60 leading-tight">
                    ${locLabel}
                </div>

                <!-- DETAIL VAKSIN / MUATAN PENTING -->
                <div class="space-y-0.5 pt-0.5">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Muatan Vaksin</span>
                    <div class="font-bold text-slate-900 text-xs leading-snug">${item.product_name}</div>
                    <div class="flex items-center gap-2 text-[11px] text-slate-600 pt-0.5">
                        <span>Batch: <b class="font-mono text-teal-900">${item.batch_number}</b></span>
                        <span>&bull;</span>
                        <span><b class="text-slate-800">${Number(item.quantity_vials).toLocaleString()} vials</b></span>
                    </div>
                </div>

                <!-- MONITORING SUHU CONTAINER -->
                <div class="p-2 rounded bg-teal-50/70 border border-teal-200/80 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-[10px] text-teal-800 font-semibold block">Suhu Kontainer:</span>
                        <span class="font-mono font-bold text-sm ${containerTemp !== null ? 'text-teal-900' : 'text-slate-500'}">
                            ${containerTemp !== null ? containerTemp + '&deg;C' : 'N/A'}
                        </span>
                        <span class="text-[10px] text-emerald-700 font-semibold ml-1">✓ Batas Aman</span>
                    </div>
                    <div class="text-right text-[10px] text-slate-500">
                        <span class="block">Target:</span>
                        <span class="font-mono font-bold text-slate-700">${shipment.target_temp_min}&deg;C ~ ${shipment.target_temp_max}&deg;C</span>
                    </div>
                </div>

                <!-- ACTION LINK -->
                <div class="pt-1 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400 text-[10px] truncate max-w-[140px]">${shipment.carrier.name}</span>
                    <a href="${shipment.show_url}" class="font-bold text-teal-700 hover:text-teal-900 hover:underline inline-flex items-center gap-0.5">
                        Detail Lengkap &rarr;
                    </a>
                </div>
            </div>
        `;
    }

    // Function to focus on a particular shipment on the map
    window.focusShipmentOnMap = function(shipmentId) {
        const shipment = mapShipmentsData.find(s => s.id === shipmentId);
        if (!shipment) return;

        const selector = document.getElementById('shipment-focus-selector');
        if (selector) selector.value = shipment.id;

        if (shipmentMarkers[shipment.id] && shipmentMarkers[shipment.id].vehicle) {
            const vMarker = shipmentMarkers[shipment.id].vehicle;
            map.flyTo(vMarker.getLatLng(), 6, { duration: 1.0 });
            setTimeout(() => {
                vMarker.openPopup();
            }, 1050);
        } else if (shipmentBounds[shipment.id]) {
            map.fitBounds(shipmentBounds[shipment.id], { padding: [50, 50], maxZoom: 6 });
        }
    };

    document.addEventListener("DOMContentLoaded", function () {
        // High-Quality Tile Basemaps
        const osmStreet = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors'
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
            layers: [osmStreet]
        });

        L.control.layers({
            "OpenStreetMap": osmStreet,
            "Esri World Street": esriStreet,
            "Esri Satellite": esriSatellite
        }, null, { position: 'topright' }).addTo(map);

        setTimeout(() => { map.invalidateSize(); }, 300);

        const allMapBounds = [];

        // RENDER EACH ACTIVE SHIPMENT
        mapShipmentsData.forEach(shipment => {
            shipmentMarkers[shipment.id] = { waypoints: [] };
            const thisShipmentBounds = [];
            const routeCoords = [];

            const mode = shipment.carrier.transport_type; // 'air', 'land', 'sea'

            // 1. ORIGIN MARKER
            if (shipment.origin && shipment.origin.lat && shipment.origin.lng) {
                const originPt = [shipment.origin.lat, shipment.origin.lng];
                routeCoords.push(originPt);
                thisShipmentBounds.push(originPt);
                allMapBounds.push(originPt);

                const originIconHtml = `
                    <div class="relative group cursor-pointer flex items-center justify-center w-7 h-7 rounded-full bg-teal-800 border-2 border-white shadow-md text-white font-bold text-xs hover:scale-110 transition-transform">
                        <span>${mode === 'air' ? '🛫' : '🏭'}</span>
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

                const isDelayed = shipment.is_delayed;
                const cpColor = isDelayed ? 'bg-amber-600 border-amber-200' : 'bg-teal-700 border-teal-200';

                const cpIconHtml = `
                    <div class="relative group cursor-pointer flex items-center justify-center w-6 h-6 rounded-full ${cpColor} border-2 shadow-sm text-white text-[9px] hover:scale-110 transition-transform">
                        <span>🔄</span>
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

                const destIconHtml = `
                    <div class="relative group cursor-pointer flex items-center justify-center w-7 h-7 rounded-full bg-emerald-600 border-2 border-white shadow-md text-white font-bold text-xs hover:scale-110 transition-transform">
                        <span>${mode === 'air' ? '🛬' : '🏥'}</span>
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

            // 4. ROUTE POLYLINE
            if (routeCoords.length >= 2) {
                const polylineColor = shipment.is_delayed ? '#f59e0b' : (mode === 'air' ? '#0d9488' : '#059669');
                const polyline = L.polyline(routeCoords, {
                    color: polylineColor,
                    weight: 3,
                    opacity: 0.85,
                    dashArray: mode === 'air' ? '8, 8' : 'none'
                }).addTo(map);

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
                            <div class="absolute -inset-2 rounded-full bg-teal-400 opacity-40 animate-ping"></div>
                            <div class="relative flex items-center justify-center w-9 h-9 rounded-full bg-slate-900 border-2 border-teal-400 shadow-xl text-white transform hover:scale-115 transition-transform">
                                <svg style="transform: rotate(${bearing}deg); transform-origin: center;" class="w-5 h-5 text-teal-300 drop-shadow" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                                </svg>
                            </div>
                            <div class="absolute -bottom-4.5 left-1/2 -translate-x-1/2 whitespace-nowrap px-1.5 py-0.2 rounded-full bg-slate-900/90 text-[9px] font-mono font-bold text-teal-300 border border-teal-500/30 shadow">
                                ✈️ ${shipment.carrier.code}
                            </div>
                        </div>
                    `;
                } else if (mode === 'land') {
                    vehicleHtml = `
                        <div class="relative group cursor-pointer">
                            <div class="absolute -inset-2 rounded-full bg-emerald-400 opacity-40 animate-ping"></div>
                            <div class="relative flex items-center justify-center w-9 h-9 rounded-full bg-slate-900 border-2 border-emerald-400 shadow-xl text-white transform hover:scale-115 transition-transform">
                                <svg class="w-5 h-5 text-emerald-300 drop-shadow" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18 18.5a1.5 1.5 0 0 1-1.5-1.5 1.5 1.5 0 0 1 1.5-1.5 1.5 1.5 0 0 1 1.5 1.5 1.5 1.5 0 0 1-1.5 1.5m1.5-9H17V12h4.5M6 18.5A1.5 1.5 0 0 1 4.5 17 1.5 1.5 0 0 1 6 15.5 1.5 1.5 0 0 1 7.5 17 1.5 1.5 0 0 1 6 18.5M20 8h-3V4H1v13h2a3 3 0 0 0 6 0h6a3 3 0 0 0 6 0h2v-6l-3-3z"/>
                                </svg>
                            </div>
                            <div class="absolute -bottom-4.5 left-1/2 -translate-x-1/2 whitespace-nowrap px-1.5 py-0.2 rounded-full bg-slate-900/90 text-[9px] font-mono font-bold text-emerald-300 border border-emerald-500/30 shadow">
                                🚚 ${shipment.carrier.code}
                            </div>
                        </div>
                    `;
                } else {
                    vehicleHtml = `
                        <div class="relative group cursor-pointer">
                            <div class="absolute -inset-2 rounded-full bg-cyan-400 opacity-40 animate-ping"></div>
                            <div class="relative flex items-center justify-center w-9 h-9 rounded-full bg-slate-900 border-2 border-cyan-400 shadow-xl text-white transform hover:scale-115 transition-transform">
                                <svg class="w-5 h-5 text-cyan-300 drop-shadow" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 21c-1.39 0-2.78-.47-4-1.32-2.44 1.71-5.56 1.71-8 0C6.78 20.53 5.39 21 4 21H2v2h2c1.38 0 2.74-.35 4-.99 2.52 1.29 5.48 1.29 8 0 1.26.64 2.62.99 4 .99h2v-2h-2M3.95 19H4c1.6 0 3.02-.88 4-2 .98 1.12 2.4 2 4 2s3.02-.88 4-2c.98 1.12 2.4 2 4 2h.05l1.89-6.68c.08-.26.06-.54-.06-.78s-.32-.42-.58-.49L20 10.7V6c0-1.11-.89-2-2-2h-3V1h-2v3h-2V1H9v3H6c-1.11 0-2 .89-2 2v4.7l-1.3.35c-.26.07-.46.25-.58.49s-.14.52-.06.78L3.95 19z"/>
                                </svg>
                            </div>
                            <div class="absolute -bottom-4.5 left-1/2 -translate-x-1/2 whitespace-nowrap px-1.5 py-0.2 rounded-full bg-slate-900/90 text-[9px] font-mono font-bold text-cyan-300 border border-cyan-500/30 shadow">
                                🚢 ${shipment.carrier.code}
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
                if (this.value === 'ALL') {
                    if (allMapBounds.length > 0) {
                        map.fitBounds(L.latLngBounds(allMapBounds), { padding: [50, 50], maxZoom: 6 });
                    }
                } else {
                    const sId = parseInt(this.value, 10);
                    focusShipmentOnMap(sId);
                }
            });
        }
    });
</script>
@endpush
@endsection
