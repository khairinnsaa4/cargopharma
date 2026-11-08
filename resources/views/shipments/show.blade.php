@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- BREADCRUMB & HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 mb-1 text-xs text-slate-500 font-medium">
                <a href="{{ route('shipments.index') }}" class="hover:text-teal-700">Shipments</a>
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
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-teal-600 text-white shadow-xs">
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
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-medium text-xs flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i> Mark BOOKED
                </button>
            </form>
            @elseif($shipment->current_status === 'BOOKED')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="PICKED_UP">
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-medium text-xs flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="truck" class="w-3.5 h-3.5"></i> Picked Up
                </button>
            </form>
            @elseif($shipment->current_status === 'PICKED_UP')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="IN_TRANSIT">
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-medium text-xs flex items-center gap-1.5 shadow-xs">
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
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-teal-600"></i> Add Checkpoint
            </button>

            <button onclick="document.getElementById('modal-exception').classList.remove('hidden')" class="px-3 py-1.5 rounded-lg border border-rose-300 bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium text-xs flex items-center gap-1.5">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-rose-600"></i> Log Exception
            </button>
            @endif
        </div>
        @endif
    </div>

    <!-- 4 CORPORATE TELEMETRY TILES -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                <span class="text-xs text-slate-500">{{ $shipment->target_temp_min }}°C to {{ $shipment->target_temp_max }}°C</span>
            </div>
            <div class="mt-2 text-xs text-slate-600 truncate">
                Unit: <span class="font-medium text-slate-800">{{ $shipment->items->first()->container_type ?? 'Envirotainer RKN e1' }}</span>
            </div>
        </div>

        <!-- 2. Atmospheric Weather at Hub -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                <span class="flex items-center gap-1 text-slate-700 font-medium">
                    <i data-lucide="cloud-sun" class="w-3.5 h-3.5 text-teal-600"></i> OpenWeather Hub
                </span>
                <span class="text-[11px] text-slate-400">Atmosphere</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold font-mono text-slate-900">{{ number_format($weatherData['temp'], 1) }}°C</span>
                <span class="text-xs text-slate-600 truncate">{{ $weatherData['condition'] }} • {{ $weatherData['city'] }}</span>
            </div>
            <div class="mt-2 text-xs text-slate-600 flex items-center justify-between">
                <span>Humidity: {{ $weatherData['humidity'] }}%</span>
                @if($weatherData['is_extreme_heat'])
                <span class="text-amber-700 font-semibold">Heatwave Alert</span>
                @else
                <span class="text-emerald-600 font-medium">Optimal Status</span>
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
                ETA: {{ $shipment->expected_arrival->format('d M Y') }}
            </div>
            <div class="mt-2 text-xs text-slate-600">
                Departed: <span class="font-mono text-slate-800">{{ $shipment->expected_departure->format('d M, H:i') }}</span>
            </div>
        </div>

        <!-- 4. Declared Valuation (Live ECB FX Rates) -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                <span class="flex items-center gap-1 text-slate-700 font-medium">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i> Cargo Insurance Value
                </span>
                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-100 text-slate-600">Live ECB</span>
            </div>
            <div class="text-xl font-bold font-mono text-slate-900">
                ${{ number_format($declaredUsd, 0) }} <span class="text-xs font-sans text-slate-500 font-normal">USD</span>
            </div>
            <div class="mt-2 text-xs text-slate-600 flex items-center justify-between font-mono">
                <span class="text-emerald-700 font-semibold">Rp {{ number_format($declaredIdr / 1000000000, 2) }} Milyar</span>
                <span class="text-slate-400">€{{ number_format($declaredEur, 0) }}</span>
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
                        <span class="font-mono font-semibold text-teal-700 block">{{ $shipment->items->first()->batch_number ?? 'BF-LOT-001' }}</span>
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
                        <div class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-white border-2 {{ $event->event_type === 'EXCEPTION_OCCURRED' ? 'border-rose-600' : ($event->event_type === 'DELIVERED' ? 'border-emerald-600' : 'border-teal-600') }}"></div>
                        <div class="text-xs">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="font-bold text-slate-900">{{ $event->event_type }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $event->event_time->format('d M, H:i') }}</span>
                            </div>
                            <div class="font-medium text-teal-700 text-xs mb-1">
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
    <div class="w-full max-w-lg bg-white rounded-xl p-6 shadow-xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Log Checkpoint Telemetry</h3>
                <p class="text-[11px] text-slate-500">Append authenticated timeline checkpoint with live telemetry</p>
            </div>
            <button onclick="document.getElementById('modal-checkpoint').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- LIVE OPENSTREETMAP GEOCODING SEARCH -->
        <div class="p-3 rounded-lg bg-teal-50/70 border border-teal-200/80 space-y-2 text-xs">
            <div class="flex items-center justify-between">
                <label class="font-bold text-teal-950 flex items-center gap-1.5">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-teal-600"></i> OpenStreetMap Live Geocoding
                </label>
                <span class="text-[10px] font-mono font-semibold text-teal-700 bg-teal-100/70 px-1.5 py-0.5 rounded">Nominatim API</span>
            </div>
            <div class="flex gap-2">
                <input type="text" id="geocode-search-input" placeholder="Search airport, seaport, or hub (e.g. Dubai Airport, Changi, Schiphol)..." 
                    class="flex-1 p-2 rounded-lg border border-slate-200 bg-white text-slate-800 text-xs focus:outline-none focus:border-teal-600">
                <button type="button" id="btn-geocode-search" class="px-3 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg shrink-0 flex items-center gap-1">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i> Find
                </button>
            </div>
            <div id="geocode-results" class="hidden space-y-1 max-h-36 overflow-y-auto border border-teal-200 rounded-lg p-1.5 bg-white text-xs"></div>
            <div id="geocode-live-feedback" class="hidden text-[11px] text-emerald-700 font-medium flex items-center gap-1">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> <span id="geocode-feedback-text"></span>
            </div>
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
                <label class="block font-medium text-slate-700 mb-1">Assigned Master Location (Optional)</label>
                <select name="location_id" id="checkpoint-location-id" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                    <option value="">Auto / Custom Geocoded Coordinates</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" data-lat="{{ $loc->latitude }}" data-lng="{{ $loc->longitude }}">{{ $loc->name }} ({{ $loc->city }})</option>
                    @endforeach
                </select>
            </div>

            <!-- GEODATA COORDINATES -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Recorded Latitude</label>
                    <input type="text" name="recorded_lat" id="checkpoint-lat" placeholder="-6.8996" class="w-full p-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Recorded Longitude</label>
                    <input type="text" name="recorded_lng" id="checkpoint-lng" placeholder="107.5997" class="w-full p-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-800 font-mono">
                </div>
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
                <input type="text" name="status_snapshot" id="checkpoint-headline" required placeholder="e.g. Cleared transit holding area at Dubai DXB" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>

            <div>
                <label class="block font-medium text-slate-700 mb-1">Observation Details</label>
                <textarea name="description" rows="2" required placeholder="Dry-ice level nominal, datalogger inspected..." class="w-full p-2 rounded-lg border border-slate-200 text-slate-800"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-checkpoint').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold">Save Checkpoint</button>
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

        const map = L.map('shipment-map', {
            center: [originLat, originLng],
            zoom: 4,
            layers: [osmStreet]
        });

        L.control.layers({
            "OpenStreetMap": osmStreet,
            "Esri Street": esriStreet,
            "Satellite": esriSatellite
        }, null, { position: 'topright' }).addTo(map);

        setTimeout(() => { map.invalidateSize(); }, 300);

        const routeCoords = [];

        // Origin marker
        const originMarker = L.circleMarker([originLat, originLng], {
            radius: 7,
            fillColor: '#0f766e',
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
                    fillColor: '{{ $ev->event_type === "EXCEPTION_OCCURRED" ? "#ef4444" : "#0d9488" }}',
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
            color: '{{ $shipment->is_delayed ? "#f59e0b" : "#0d9488" }}',
            weight: 3,
            opacity: 0.8,
            dashArray: '6, 6'
        }).addTo(map);

        map.fitBounds(polyline.getBounds(), { padding: [40, 40] });

        // 4. Live Geocoding Search Handler (Nominatim API via /api/geocode)
        const geocodeInput = document.getElementById('geocode-search-input');
        const geocodeBtn = document.getElementById('btn-geocode-search');
        const geocodeResults = document.getElementById('geocode-results');
        const geocodeFeedback = document.getElementById('geocode-live-feedback');
        const feedbackText = document.getElementById('geocode-feedback-text');
        const latInput = document.getElementById('checkpoint-lat');
        const lngInput = document.getElementById('checkpoint-lng');
        const headlineInput = document.getElementById('checkpoint-headline');

        if (geocodeBtn && geocodeInput) {
            async function performGeocodeSearch() {
                const query = geocodeInput.value.trim();
                if (query.length < 2) {
                    alert('Please enter at least 2 characters to search.');
                    return;
                }

                geocodeBtn.disabled = true;
                geocodeBtn.innerHTML = '<span class="animate-spin mr-1">⏳</span> Searching...';

                try {
                    const response = await fetch(`/api/geocode?q=${encodeURIComponent(query)}`);
                    const json = await response.json();

                    geocodeResults.innerHTML = '';
                    if (json.success && json.data.length > 0) {
                        geocodeResults.classList.remove('hidden');
                        json.data.forEach(item => {
                            const opt = document.createElement('div');
                            opt.className = 'p-2 hover:bg-teal-50 cursor-pointer rounded transition-colors text-slate-800 border-b border-slate-100 last:border-0';
                            opt.innerHTML = `
                                <div class="font-bold text-slate-900">${item.name}</div>
                                <div class="text-[11px] text-slate-500 truncate">${item.display_name}</div>
                                <div class="text-[10px] font-mono text-teal-700 font-semibold">GPS: ${item.lat}, ${item.lng} (${item.source})</div>
                            `;
                            opt.addEventListener('click', () => {
                                latInput.value = item.lat;
                                lngInput.value = item.lng;
                                headlineInput.value = `Transit Checkpoint at ${item.name}`;
                                geocodeResults.classList.add('hidden');
                                geocodeFeedback.classList.remove('hidden');
                                feedbackText.textContent = `Coordinates locked: ${item.name} (${item.lat}, ${item.lng})`;
                            });
                            geocodeResults.appendChild(opt);
                        });
                    } else {
                        geocodeResults.classList.remove('hidden');
                        geocodeResults.innerHTML = '<div class="p-2 text-slate-500 text-center">No location coordinates found.</div>';
                    }
                } catch (err) {
                    console.error('Geocode search failed:', err);
                    alert('Geocode request failed. Please check your network.');
                } finally {
                    geocodeBtn.disabled = false;
                    geocodeBtn.innerHTML = '<i data-lucide="search" class="w-3.5 h-3.5"></i> Find';
                    if (window.lucide) lucide.createIcons();
                }
            }

            geocodeBtn.addEventListener('click', performGeocodeSearch);
            geocodeInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performGeocodeSearch();
                }
            });
        }

        // Live Geocoding Handler (OpenStreetMap Nominatim)
        const geocodeInput = document.getElementById('geocode-search-input');
        const btnGeocode = document.getElementById('btn-geocode-search');
        const geocodeResults = document.getElementById('geocode-results');
        const feedbackDiv = document.getElementById('geocode-live-feedback');
        const feedbackText = document.getElementById('geocode-feedback-text');

        if (btnGeocode && geocodeInput) {
            const doGeocode = function () {
                const q = geocodeInput.value.trim();
                if (q.length < 2) return;

                btnGeocode.disabled = true;
                btnGeocode.innerHTML = '<span class="animate-spin text-xs">⌛</span>';

                fetch('{{ route("ajax.geocode") }}?q=' + encodeURIComponent(q))
                    .then(res => res.json())
                    .then(data => {
                        btnGeocode.disabled = false;
                        btnGeocode.innerHTML = '<i data-lucide="search" class="w-3.5 h-3.5"></i> Find';
                        if (window.lucide) lucide.createIcons();

                        if (data.success && data.data && data.data.length > 0) {
                            geocodeResults.innerHTML = '';
                            geocodeResults.classList.remove('hidden');

                            data.data.forEach(item => {
                                const row = document.createElement('div');
                                row.className = 'p-2 hover:bg-teal-50 rounded-lg cursor-pointer border-b border-slate-100 last:border-b-0 flex items-start justify-between gap-2';
                                row.innerHTML = `
                                    <div class="flex-1">
                                        <div class="font-semibold text-slate-900">${item.name}</div>
                                        <div class="text-[10px] text-slate-400 truncate max-w-xs">${item.display_name}</div>
                                    </div>
                                    <span class="text-[10px] font-mono font-bold text-teal-700 bg-teal-50 border border-teal-200 px-1.5 py-0.5 rounded shrink-0">
                                        ${item.lat.toFixed(4)}, ${item.lng.toFixed(4)}
                                    </span>
                                `;
                                row.addEventListener('click', function () {
                                    document.getElementById('checkpoint-lat').value = item.lat;
                                    document.getElementById('checkpoint-lng').value = item.lng;
                                    document.getElementById('checkpoint-headline').value = 'Checkpoint verified at ' + item.name;
                                    geocodeResults.classList.add('hidden');
                                    feedbackDiv.classList.remove('hidden');
                                    feedbackText.textContent = `Applied GPS: ${item.name} (${item.lat.toFixed(4)}, ${item.lng.toFixed(4)})`;
                                });
                                geocodeResults.appendChild(row);
                            });
                        } else {
                            geocodeResults.innerHTML = '<div class="p-2 text-slate-400 text-center text-xs">No coordinates found for query.</div>';
                            geocodeResults.classList.remove('hidden');
                        }
                    })
                    .catch(err => {
                        btnGeocode.disabled = false;
                        btnGeocode.innerHTML = 'Find';
                        console.error('Geocoding error:', err);
                    });
            };

            btnGeocode.addEventListener('click', doGeocode);
            geocodeInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    doGeocode();
                }
            });
        }

        // Location dropdown auto-fill
        const locSelect = document.getElementById('checkpoint-location-id');
        if (locSelect) {
            locSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const lat = opt.getAttribute('data-lat');
                const lng = opt.getAttribute('data-lng');
                if (lat && lng) {
                    document.getElementById('checkpoint-lat').value = lat;
                    document.getElementById('checkpoint-lng').value = lng;
                    document.getElementById('checkpoint-headline').value = 'Checkpoint verified at ' + opt.text;
                }
            });
        }
    });
</script>
@endpush
@endsection
