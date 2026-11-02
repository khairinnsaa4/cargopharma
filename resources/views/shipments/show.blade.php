@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- BREADCRUMB & HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 mb-1 text-xs text-slate-500 font-medium">
                <a href="{{ route('shipments.index') }}" class="hover:text-blue-600">Shipments</a>
                <span>/</span>
                <span class="text-slate-800 font-mono font-bold">{{ $shipment->shipment_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-slate-900 font-mono">{{ $shipment->shipment_number }}</h1>
                @if($shipment->is_delayed)
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-rose-600 text-white shadow-xs">
                        DELAYED (+{{ $shipment->delay_duration_hours }}H)
                    </span>
                @elseif($shipment->current_status === 'DELIVERED')
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-600 text-white shadow-xs">
                        DELIVERED
                    </span>
                @elseif($shipment->current_status === 'IN_TRANSIT')
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-blue-600 text-white shadow-xs">
                        IN TRANSIT
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-medium bg-slate-200 text-slate-700">
                        {{ $shipment->current_status }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">Consignee: <span class="font-semibold text-slate-700">{{ $shipment->customer->organization ?? $shipment->customer->name }}</span> • Carrier: <span class="text-slate-700 font-medium">{{ $shipment->carrier->name ?? 'Standard Fleet' }}</span></p>
        </div>

        <!-- OPERATOR / ADMIN ACTION TOOLBAR -->
        @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Advance Status Button -->
            @if($shipment->current_status === 'CREATED')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="BOOKED">
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i> Mark BOOKED
                </button>
            </form>
            @elseif($shipment->current_status === 'BOOKED')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="PICKED_UP">
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="truck" class="w-3.5 h-3.5"></i> Picked Up
                </button>
            </form>
            @elseif($shipment->current_status === 'PICKED_UP')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="IN_TRANSIT">
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="plane" class="w-3.5 h-3.5"></i> In Transit
                </button>
            </form>
            @elseif($shipment->current_status === 'IN_TRANSIT')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="DELIVERED">
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Mark DELIVERED
                </button>
            </form>
            @endif

            @if($shipment->current_status !== 'DELIVERED')
            <button onclick="document.getElementById('modal-checkpoint').classList.remove('hidden')" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs flex items-center gap-1.5">
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-blue-600"></i> Add Checkpoint
            </button>

            <button onclick="document.getElementById('modal-exception').classList.remove('hidden')" class="px-3 py-1.5 rounded-lg border border-rose-300 bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium text-xs flex items-center gap-1.5">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-rose-600"></i> Log Exception
            </button>
            @endif
        </div>
        @endif
    </div>

    <!-- 3 CORPORATE TELEMETRY TILES -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- 1. Cold-Chain Sensor Status -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                <span>Core Temperature Telemetry</span>
                <span class="text-emerald-600 font-semibold flex items-center gap-1 text-[11px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sensor Nominal
                </span>
            </div>
            @php
                $currentTemp = $latestEvent?->container_temperature ?? $shipment->target_temp_min;
            @endphp
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold font-mono text-slate-900">{{ number_format($currentTemp, 1) }}°C</span>
                <span class="text-xs text-slate-500">Target Range: {{ $shipment->target_temp_min }}°C to {{ $shipment->target_temp_max }}°C</span>
            </div>
            <div class="mt-2 text-xs text-slate-600">
                Unit: <span class="font-medium text-slate-800">{{ $shipment->items->first()->container_type ?? 'Envirotainer RKN e1' }}</span>
            </div>
        </div>

        <!-- 2. Atmospheric Weather at Hub -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                <span class="flex items-center gap-1 text-slate-700 font-medium">
                    <i data-lucide="cloud-sun" class="w-3.5 h-3.5 text-blue-600"></i> OpenWeather Hub Reading
                </span>
                <span class="text-[11px] text-slate-400">Atmosphere</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold font-mono text-slate-900">{{ number_format($weatherData['temp'], 1) }}°C</span>
                <span class="text-xs text-slate-600">{{ $weatherData['condition'] }} • {{ $weatherData['city'] }}</span>
            </div>
            <div class="mt-2 text-xs text-slate-600 flex items-center justify-between">
                <span>Humidity: {{ $weatherData['humidity'] }}%</span>
                @if($weatherData['is_extreme_heat'])
                <span class="text-amber-700 font-semibold">Tarmac Heatwave Alert</span>
                @else
                <span class="text-emerald-600 font-medium">Optimal Conditions</span>
                @endif
            </div>
        </div>

        <!-- 3. Schedule & Routing ETA -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                <span>Delivery Schedule</span>
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
            </div>
            <div class="text-base font-bold text-slate-900 font-mono">
                ETA: {{ $shipment->expected_arrival->format('d M Y, H:i') }}
            </div>
            <div class="mt-2 text-xs text-slate-600">
                Departed: <span class="font-mono text-slate-800">{{ $shipment->expected_departure->format('d M Y, H:i') }}</span>
            </div>
        </div>
    </div>

    <!-- MAIN GRID: MAP & TIMELINE -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT 2 COLS: MAP & PRODUCT SPECIFICATION -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Leaflet Map Card -->
            <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900">Geospatial Corridor Route</h3>
                    <div class="text-xs text-slate-500">
                        {{ $shipment->origin->city }} → {{ $shipment->destination->city }}
                    </div>
                </div>

                <div id="shipment-map" class="w-full h-[380px] z-0"></div>
            </div>

            <!-- Manifest Specifications -->
            <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-3">
                <h3 class="text-sm font-bold text-slate-900">Pharmaceutical Manifest Details</h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[11px] block">Product</span>
                        <span class="font-semibold text-slate-800 block truncate">{{ $shipment->items->first()->product_name ?? $shipment->cargo_type }}</span>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[11px] block">Batch Number</span>
                        <span class="font-mono font-semibold text-blue-700 block">{{ $shipment->items->first()->batch_number ?? 'BF-LOT-001' }}</span>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[11px] block">Quantity Vials</span>
                        <span class="font-semibold text-slate-800 block">{{ number_format($shipment->items->first()->quantity_vials ?? 0) }}</span>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[11px] block">Gross Weight</span>
                        <span class="font-semibold text-slate-800 block">{{ number_format($shipment->total_weight_kg) }} kg</span>
                    </div>
                </div>
            </div>

            <!-- Exception Management Panel -->
            @if($shipment->exceptions->count() > 0)
            <div class="p-5 rounded-xl bg-white border border-rose-200 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-rose-800 flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i> Active Incidents & Exceptions
                    </h3>
                    <span class="text-xs text-rose-600 font-semibold">{{ $shipment->exceptions->count() }} Recorded</span>
                </div>

                <div class="space-y-3">
                    @foreach($shipment->exceptions as $exc)
                    <div class="p-3.5 rounded-lg bg-rose-50/50 border border-rose-100 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $exc->exception_type) }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $exc->status === 'RESOLVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $exc->status }}
                            </span>
                        </div>
                        <p class="text-slate-700">{{ $exc->root_cause }}</p>
                        @if($exc->resolution_notes)
                        <div class="p-2.5 rounded bg-white border border-slate-200 text-slate-600 text-[11px]">
                            <span class="font-semibold text-emerald-700">Resolution:</span> {{ $exc->resolution_notes }}
                        </div>
                        @endif

                        @if($exc->status !== 'RESOLVED' && (Auth::user()->isAdmin() || Auth::user()->isOperator()))
                        <form method="POST" action="{{ route('exceptions.resolve', $exc->id) }}" class="pt-2 flex items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="RESOLVED">
                            <input type="text" name="resolution_notes" required placeholder="Resolution actions taken..." 
                                class="flex-1 px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-xs">
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs">
                                Resolve Incident
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- RIGHT 1 COL: CHRONOLOGICAL TIMELINE -->
        <div class="space-y-4">
            <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Checkpoint History</h3>
                    <span class="text-xs text-slate-500 font-mono">{{ $shipment->trackingEvents->count() }} Events</span>
                </div>

                <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse($shipment->trackingEvents as $event)
                    <div class="relative">
                        <div class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-white border-2 {{ $event->event_type === 'EXCEPTION_OCCURRED' ? 'border-rose-600' : ($event->event_type === 'DELIVERED' ? 'border-emerald-600' : 'border-blue-600') }}"></div>
                        <div class="text-xs">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="font-bold text-slate-900">{{ $event->event_type }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $event->event_time->format('d M, H:i') }}</span>
                            </div>
                            <div class="font-medium text-blue-700 text-xs mb-1">
                                {{ $event->status_snapshot }}
                            </div>
                            <p class="text-slate-600 text-xs leading-relaxed">
                                {{ $event->description }}
                            </p>
                            <div class="flex items-center gap-2 mt-1.5 text-[10px] font-mono text-slate-500">
                                @if($event->container_temperature !== null)
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200">
                                    Core: {{ $event->container_temperature }}°C
                                </span>
                                @endif
                                @if($event->ambient_temperature !== null)
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200">
                                    Ambient: {{ $event->ambient_temperature }}°C
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-slate-400 text-xs py-4 text-center">
                        No events logged yet.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>

<!-- MODAL CHECKPOINT -->
<div id="modal-checkpoint" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md bg-white rounded-xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Log Checkpoint Event</h3>
            <button onclick="document.getElementById('modal-checkpoint').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('tracking.store', $shipment->id) }}" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-medium text-slate-700 mb-1">Event Type *</label>
                <select name="event_type" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                    <option value="IN_TRANSIT">IN_TRANSIT (In Flight / En-route)</option>
                    <option value="TRANSIT_HUB">TRANSIT_HUB (Arrival at Airport Hub)</option>
                    <option value="CUSTOMS_INSPECTION">CUSTOMS_INSPECTION (Lot release check)</option>
                    <option value="DELIVERED">DELIVERED (Arrival at destination cold room)</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-700 mb-1">Location / Airport</label>
                <select name="location_id" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                    <option value="">Auto Location</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->city }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Container Temp (°C) *</label>
                    <input type="number" step="0.1" name="container_temperature" value="-20.0" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Timestamp *</label>
                    <input type="datetime-local" name="event_time" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
                </div>
            </div>

            <div>
                <label class="block font-medium text-slate-700 mb-1">Status Headline *</label>
                <input type="text" name="status_snapshot" required placeholder="e.g. Cleared transit holding area" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>

            <div>
                <label class="block font-medium text-slate-700 mb-1">Observation Details</label>
                <textarea name="description" rows="2" required placeholder="Seal condition, power endurance..." class="w-full p-2 rounded-lg border border-slate-200 text-slate-800"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-checkpoint').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold">Save Checkpoint</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EXCEPTION -->
<div id="modal-exception" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md bg-white rounded-xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-rose-700">Report Shipment Exception</h3>
            <button onclick="document.getElementById('modal-exception').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('exceptions.store', $shipment->id) }}" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-medium text-slate-700 mb-1">Exception Category *</label>
                <select name="exception_type" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                    <option value="TEMPERATURE_BREACH">TEMPERATURE_BREACH</option>
                    <option value="WEATHER_DELAY">WEATHER_DELAY</option>
                    <option value="CUSTOMS_HOLD">CUSTOMS_HOLD</option>
                    <option value="PORT_CONGESTION">PORT_CONGESTION</option>
                    <option value="DOCUMENTATION_ISSUE">DOCUMENTATION_ISSUE</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Severity Rating *</label>
                    <select name="severity" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                        <option value="LOW">LOW</option>
                        <option value="MEDIUM" selected>MEDIUM</option>
                        <option value="HIGH">HIGH</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Detected Time *</label>
                    <input type="datetime-local" name="detected_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
                </div>
            </div>

            <div>
                <label class="block font-medium text-slate-700 mb-1">Root Cause Explanation *</label>
                <textarea name="root_cause" rows="3" required placeholder="Explain factors causing incident..." class="w-full p-2 rounded-lg border border-slate-200 text-slate-800"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-exception').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 text-white font-semibold">Submit Exception</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const originLat = {{ $shipment->origin->latitude }};
        const originLng = {{ $shipment->origin->longitude }};
        const destLat = {{ $shipment->destination->latitude }};
        const destLng = {{ $shipment->destination->longitude }};

        const map = L.map('shipment-map').setView([originLat, originLng], 4);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);

        const routeCoords = [];

        // Origin marker
        const originMarker = L.circleMarker([originLat, originLng], {
            radius: 7,
            fillColor: '#0284c7',
            color: '#ffffff',
            weight: 2,
            fillOpacity: 1
        }).addTo(map);
        originMarker.bindPopup(`<b>ORIGIN: {{ $shipment->origin->name }}</b><br>{{ $shipment->origin->city }}`);
        routeCoords.push([originLat, originLng]);

        // Checkpoints
        @foreach($shipment->trackingEvents as $ev)
            @if($ev->recorded_lat && $ev->recorded_lng)
                routeCoords.push([{{ $ev->recorded_lat }}, {{ $ev->recorded_lng }}]);
                L.circleMarker([{{ $ev->recorded_lat }}, {{ $ev->recorded_lng }}], {
                    radius: 5,
                    fillColor: '{{ $ev->event_type === "EXCEPTION_OCCURRED" ? "#ef4444" : "#0284c7" }}',
                    color: '#ffffff',
                    weight: 2,
                    fillOpacity: 1
                }).addTo(map).bindPopup(`
                    <b>{{ $ev->status_snapshot }}</b><br>
                    <span>Time: {{ $ev->event_time->format("d M, H:i") }}</span><br>
                    <span>Core: {{ $ev->container_temperature ?? "N/A" }}°C</span>
                `);
            @endif
        @endforeach

        // Destination marker
        const destMarker = L.circleMarker([destLat, destLng], {
            radius: 7,
            fillColor: '#10b981',
            color: '#ffffff',
            weight: 2,
            fillOpacity: 1
        }).addTo(map);
        destMarker.bindPopup(`<b>DESTINATION: {{ $shipment->destination->name }}</b><br>{{ $shipment->destination->city }}`);
        routeCoords.push([destLat, destLng]);

        const polyline = L.polyline(routeCoords, {
            color: '{{ $shipment->is_delayed ? "#f59e0b" : "#0284c7" }}',
            weight: 3,
            opacity: 0.8,
            dashArray: '6, 6'
        }).addTo(map);

        map.fitBounds(polyline.getBounds(), { padding: [40, 40] });
    });
</script>
@endpush
@endsection
