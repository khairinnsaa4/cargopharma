@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- BREADCRUMB & HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-800">
        <div>
            <div class="flex items-center gap-2 mb-1.5 font-mono text-xs text-slate-400">
                <a href="{{ route('shipments.index') }}" class="hover:text-cyan-400">Consignments</a>
                <span>/</span>
                <span class="text-cyan-400 font-bold">{{ $shipment->shipment_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-white tracking-tight font-mono">{{ $shipment->shipment_number }}</h1>
                @if($shipment->current_status === 'DELIVERED')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                        DELIVERED INTACT
                    </span>
                @elseif($shipment->current_status === 'IN_TRANSIT')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span> IN TRANSIT
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-800 text-slate-300 border border-slate-700">
                        {{ $shipment->current_status }}
                    </span>
                @endif

                @if($shipment->is_delayed)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3 h-3"></i>
                        DELAY: +{{ $shipment->delay_duration_hours }}H ({{ $shipment->delay_severity }})
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-400 mt-1">Consignee: <span class="text-white font-semibold">{{ $shipment->customer->organization ?? $shipment->customer->name }}</span> • Carrier: <span class="text-cyan-300">{{ $shipment->carrier->name ?? 'Direct Fleet' }}</span></p>
        </div>

        <!-- OPERATOR / ADMIN ACTION TOOLBAR -->
        @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Advance Status Button -->
            @if($shipment->current_status === 'CREATED')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="BOOKED">
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs flex items-center gap-1.5 transition-all">
                    <i data-lucide="calendar-check" class="w-4 h-4 text-cyan-400"></i> Advance to BOOKED
                </button>
            </form>
            @elseif($shipment->current_status === 'BOOKED')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="PICKED_UP">
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-all">
                    <i data-lucide="truck" class="w-4 h-4"></i> Pick-up Vaccine at Plant
                </button>
            </form>
            @elseif($shipment->current_status === 'PICKED_UP')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="IN_TRANSIT">
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-all">
                    <i data-lucide="plane-takeoff" class="w-4 h-4"></i> Mark IN TRANSIT
                </button>
            </form>
            @elseif($shipment->current_status === 'IN_TRANSIT')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="DELIVERED">
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-all shadow-lg shadow-emerald-500/20">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> Mark DELIVERED
                </button>
            </form>
            @endif

            <!-- Log Checkpoint Button (Opens Modal) -->
            @if($shipment->current_status !== 'DELIVERED')
            <button onclick="document.getElementById('modal-checkpoint').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-300 font-bold text-xs flex items-center gap-1.5 border border-slate-700 transition-all">
                <i data-lucide="map-pin" class="w-4 h-4 text-cyan-400"></i> Log Checkpoint
            </button>

            <!-- Raise Exception Button (Opens Modal) -->
            <button onclick="document.getElementById('modal-exception').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl bg-rose-950/60 hover:bg-rose-900/60 text-rose-300 font-bold text-xs flex items-center gap-1.5 border border-rose-800 transition-all">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400"></i> Report Incident
            </button>
            @endif
        </div>
        @endif
    </div>

    <!-- TELEMETRY HUD BAR (3 Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- 1. Cold-Chain Integrity HUD -->
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 backdrop-blur-xl relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-mono text-slate-400 mb-2">
                <span>Active Core Sensor</span>
                <span class="text-emerald-400 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> TELEMETRY OK
                </span>
            </div>
            @php
                $currentTemp = $latestEvent?->container_temperature ?? $shipment->target_temp_min;
                $isSafe = ($currentTemp >= $shipment->target_temp_min && $currentTemp <= $shipment->target_temp_max);
            @endphp
            <div class="flex items-baseline gap-3">
                <div class="text-3xl font-black font-mono {{ $isSafe ? 'text-cyan-300' : 'text-rose-400' }}">
                    {{ number_format($currentTemp, 1) }}°C
                </div>
                <div class="text-xs font-mono {{ $isSafe ? 'text-emerald-400' : 'text-rose-400 font-bold' }}">
                    {{ $isSafe ? 'Nominal Cryo Range' : 'EXCURSION ALERT' }}
                </div>
            </div>
            <div class="mt-2 text-[11px] font-mono text-slate-400">
                Target Envelope: <span class="text-white font-bold">{{ $shipment->target_temp_min }}°C</span> to <span class="text-white font-bold">{{ $shipment->target_temp_max }}°C</span>
            </div>
        </div>

        <!-- 2. External Atmospheric Weather (From Weather API - Slide 9) -->
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 backdrop-blur-xl relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-mono text-slate-400 mb-2">
                <span class="flex items-center gap-1 text-cyan-400">
                    <i data-lucide="cloud-sun" class="w-3.5 h-3.5"></i> OpenWeather Live API
                </span>
                <span class="text-[10px] font-mono uppercase text-slate-400">Checkpoint Ambience</span>
            </div>
            <div class="flex items-baseline gap-3">
                <div class="text-3xl font-black font-mono {{ $weatherData['is_extreme_heat'] ? 'text-amber-400' : 'text-white' }}">
                    {{ number_format($weatherData['temp'], 1) }}°C
                </div>
                <div class="text-xs text-slate-300">
                    {{ $weatherData['condition'] }} • {{ $weatherData['city'] }}
                </div>
            </div>
            <div class="mt-2 text-[11px] text-slate-400 flex items-center justify-between font-mono">
                <span>Humidity: {{ $weatherData['humidity'] }}%</span>
                @if($weatherData['is_extreme_heat'])
                <span class="text-amber-400 font-bold flex items-center gap-1">
                    <i data-lucide="flame" class="w-3 h-3"></i> TARMAC HEAT RISK
                </span>
                @else
                <span class="text-emerald-400">Optimal Transit Air</span>
                @endif
            </div>
        </div>

        <!-- 3. ETA & Schedule Schedule -->
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 backdrop-blur-xl relative overflow-hidden">
            <div class="flex items-center justify-between text-xs font-mono text-slate-400 mb-2">
                <span>Consignment Timeline</span>
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
            </div>
            <div class="text-sm font-bold text-white font-mono">
                ETA: {{ $shipment->expected_arrival->format('d M Y, H:i') }}
            </div>
            <div class="mt-1 text-xs text-slate-400 font-mono">
                Departed: {{ $shipment->expected_departure->format('d M Y, H:i') }}
            </div>
            <div class="mt-2 text-[11px] font-mono {{ $shipment->is_delayed ? 'text-amber-400' : 'text-emerald-400' }}">
                {{ $shipment->is_delayed ? "Variance: +{$shipment->delay_duration_hours}h ({$shipment->delay_severity})" : "On Target Schedule" }}
            </div>
        </div>
    </div>

    <!-- MAIN TWO COLUMN: MAP & TIMELINE -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT 2 COLS: INTERACTIVE LEAFLET MAP & CARGO SPECS -->
        <div class="lg:col-span-2 space-y-6">
            <!-- LEAFLET MAP CARD (Slide 9) -->
            <div class="rounded-2xl bg-slate-900/90 border border-slate-800 backdrop-blur-xl overflow-hidden shadow-2xl">
                <div class="p-4 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="map" class="w-4 h-4 text-cyan-400"></i>
                        <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">Corridor Geographic Route Tracker</h3>
                    </div>
                    <div class="text-[10px] font-mono text-slate-400">
                        Leaflet.js • OpenStreetMap Cartography
                    </div>
                </div>

                <!-- Leaflet Map Container -->
                <div id="shipment-map" class="w-full h-[400px] z-0"></div>

                <div class="p-3 bg-slate-950/60 border-t border-slate-800/80 text-[11px] font-mono text-slate-400 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span> Origin: {{ $shipment->origin->name }}</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> Destination: {{ $shipment->destination->name }}</span>
                    </div>
                    <span>Active Geofence Active</span>
                </div>
            </div>

            <!-- CARGO & VACCINE BATCH SPECIFICATIONS -->
            <div class="rounded-2xl bg-slate-900/90 border border-slate-800 p-5 backdrop-blur-xl space-y-4">
                <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4 text-cyan-400"></i> Pharmaceutical Manifest & Batch Details
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs font-mono">
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                        <span class="text-slate-400 text-[10px] block">Product Name</span>
                        <span class="text-white font-bold truncate block">{{ $shipment->items->first()->product_name ?? $shipment->cargo_type }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                        <span class="text-slate-400 text-[10px] block">Batch / Lot Number</span>
                        <span class="text-cyan-300 font-bold block">{{ $shipment->items->first()->batch_number ?? 'BF-LOT-STD' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                        <span class="text-slate-400 text-[10px] block">Quantity Vials</span>
                        <span class="text-white font-bold block">{{ number_format($shipment->items->first()->quantity_vials ?? 0) }} vials</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                        <span class="text-slate-400 text-[10px] block">Cold-Chain Unit</span>
                        <span class="text-white font-bold truncate block">{{ $shipment->items->first()->container_type ?? 'Envirotainer RKN' }}</span>
                    </div>
                </div>
            </div>

            <!-- ACTIVE EXCEPTIONS / INCIDENTS FOR THIS SHIPMENT -->
            @if($shipment->exceptions->count() > 0)
            <div class="rounded-2xl bg-slate-900/90 border border-rose-900/50 p-5 backdrop-blur-xl space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-mono font-bold text-rose-400 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-400"></i> Incident & Exception Log
                    </h3>
                    <span class="text-[10px] font-mono text-slate-400">{{ $shipment->exceptions->count() }} Record(s)</span>
                </div>

                <div class="space-y-3">
                    @foreach($shipment->exceptions as $exc)
                    <div class="p-4 rounded-xl bg-slate-950/80 border border-slate-800 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                    {{ $exc->exception_type }}
                                </span>
                                <span class="text-xs text-white font-semibold">Severity: {{ $exc->severity }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $exc->status === 'RESOLVED' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                                {{ $exc->status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-300">{{ $exc->root_cause }}</p>
                        @if($exc->resolution_notes)
                        <div class="p-2.5 rounded-lg bg-emerald-950/30 border border-emerald-800/40 text-[11px] text-emerald-300 font-mono">
                            Resolution: {{ $exc->resolution_notes }} (by {{ $exc->resolvedBy->name ?? 'System' }})
                        </div>
                        @endif

                        @if($exc->status !== 'RESOLVED' && (Auth::user()->isAdmin() || Auth::user()->isOperator()))
                        <form method="POST" action="{{ route('exceptions.resolve', $exc->id) }}" class="pt-2 flex items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="RESOLVED">
                            <input type="text" name="resolution_notes" required placeholder="Resolution actions taken (e.g. Dry ice replenishment verified)..." 
                                class="flex-1 px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white placeholder-slate-500 font-mono">
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs">
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

        <!-- RIGHT 1 COL: CHRONOLOGICAL TRACKING TIMELINE -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-cyan-400"></i> Event Audit Timeline
                </h3>
                <span class="text-[10px] font-mono text-cyan-400">{{ $shipment->trackingEvents->count() }} Checkpoints</span>
            </div>

            <div class="rounded-2xl bg-slate-900/90 border border-slate-800 p-5 backdrop-blur-xl relative">
                <!-- Vertical Timeline Bar -->
                <div class="absolute left-8 top-8 bottom-8 w-0.5 bg-slate-800"></div>

                <div class="space-y-6 relative">
                    @forelse($shipment->trackingEvents as $event)
                    <div class="flex items-start gap-4">
                        <!-- Node Icon -->
                        <div class="w-7 h-7 rounded-full bg-slate-950 border-2 {{ $event->event_type === 'EXCEPTION_OCCURRED' ? 'border-rose-500 text-rose-400' : ($event->event_type === 'DELIVERED' ? 'border-emerald-500 text-emerald-400' : 'border-cyan-500 text-cyan-400') }} flex items-center justify-center shrink-0 z-10 shadow-lg">
                            <i data-lucide="{{ $event->event_type === 'EXCEPTION_OCCURRED' ? 'alert-triangle' : ($event->event_type === 'DELIVERED' ? 'check' : 'radio') }}" class="w-3.5 h-3.5"></i>
                        </div>

                        <!-- Content Card -->
                        <div class="flex-1 p-3.5 rounded-xl bg-slate-950/70 border border-slate-800/80 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-mono font-bold text-white">{{ $event->event_type }}</span>
                                <span class="text-[10px] font-mono text-slate-400">{{ $event->event_time->format('d M, H:i') }}</span>
                            </div>
                            <div class="text-xs text-cyan-300 font-semibold">
                                {{ $event->status_snapshot }}
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                {{ $event->description }}
                            </p>

                            <!-- Telemetry Pills (Ambient + Container) -->
                            <div class="flex items-center gap-2 pt-1 font-mono text-[10px]">
                                @if($event->container_temperature !== null)
                                <span class="px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">
                                    Core: {{ $event->container_temperature }}°C
                                </span>
                                @endif
                                @if($event->ambient_temperature !== null)
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300">
                                    Ambient: {{ $event->ambient_temperature }}°C
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-slate-400 font-mono text-xs">
                        No tracking events registered yet.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>

<!-- MODAL 1: LOG CHECKPOINT (Operator/Admin) -->
<div id="modal-checkpoint" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-mono font-bold text-white flex items-center gap-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-cyan-400"></i> Append Cold-Chain Checkpoint
            </h3>
            <button onclick="document.getElementById('modal-checkpoint').classList.add('hidden')" class="text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('tracking.store', $shipment->id) }}" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-mono text-slate-400 mb-1">Event Type</label>
                <select name="event_type" required class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                    <option value="IN_TRANSIT">IN_TRANSIT (En-route flight/convoy)</option>
                    <option value="TRANSIT_HUB">TRANSIT_HUB (Airport hub arrival)</option>
                    <option value="CUSTOMS_INSPECTION">CUSTOMS_INSPECTION (Lot release inspection)</option>
                    <option value="DELIVERED">DELIVERED (Arrival at destination cold room)</option>
                </select>
            </div>

            <div>
                <label class="block font-mono text-slate-400 mb-1">Facility / Airport Node</label>
                <select name="location_id" class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <option value="">Auto Coordinate Determination</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-mono text-slate-400 mb-1">Container Temp (°C)</label>
                    <input type="number" step="0.1" name="container_temperature" value="-20.0" required class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                </div>
                <div>
                    <label class="block font-mono text-slate-400 mb-1">Timestamp</label>
                    <input type="datetime-local" name="event_time" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                </div>
            </div>

            <div>
                <label class="block font-mono text-slate-400 mb-1">Status Snapshot Title</label>
                <input type="text" name="status_snapshot" required placeholder="e.g. Container verified at cargo gate" class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
            </div>

            <div>
                <label class="block font-mono text-slate-400 mb-1">Telemetry & Audit Observations</label>
                <textarea name="description" rows="2" required placeholder="Observations regarding cooling battery, dry ice ballast, seal inspection..." class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white"></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-checkpoint').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-bold">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold">Record Telemetry</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: REPORT INCIDENT/EXCEPTION -->
<div id="modal-exception" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg bg-slate-900 border border-rose-900/60 rounded-2xl p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-sm font-mono font-bold text-rose-400 flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i> Raise Cold-Chain Incident
            </h3>
            <button onclick="document.getElementById('modal-exception').classList.add('hidden')" class="text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('exceptions.store', $shipment->id) }}" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-mono text-slate-400 mb-1">Incident Classification</label>
                <select name="exception_type" required class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                    <option value="TEMPERATURE_BREACH">TEMPERATURE_BREACH (Sensor outside target threshold)</option>
                    <option value="WEATHER_DELAY">WEATHER_DELAY (Tarmac heatwave / severe weather)</option>
                    <option value="CUSTOMS_HOLD">CUSTOMS_HOLD (Quarantine / lot certificate check)</option>
                    <option value="PORT_CONGESTION">PORT_CONGESTION (Flight congestion at hub)</option>
                    <option value="DOCUMENTATION_ISSUE">DOCUMENTATION_ISSUE (Missing airway bill)</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-mono text-slate-400 mb-1">Severity Rating</label>
                    <select name="severity" required class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                        <option value="LOW">LOW (Tolerable, dry ice intact)</option>
                        <option value="MEDIUM" selected>MEDIUM (Action required within 12h)</option>
                        <option value="HIGH">HIGH (Urgent! Vaccine degradation hazard)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-mono text-slate-400 mb-1">Detected Time</label>
                    <input type="datetime-local" name="detected_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                </div>
            </div>

            <div>
                <label class="block font-mono text-slate-400 mb-1">Root Cause & Diagnostic Summary</label>
                <textarea name="root_cause" rows="3" required placeholder="Describe factors causing variance (e.g. Flight EK359 reschedule due to air traffic)..." class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white"></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-exception').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-bold">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold">Log Critical Incident</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Prepare coordinates for Leaflet Map (Slide 9: Leaflet.js + OpenStreetMap)
        const originLat = {{ $shipment->origin->latitude }};
        const originLng = {{ $shipment->origin->longitude }};
        const destLat = {{ $shipment->destination->latitude }};
        const destLng = {{ $shipment->destination->longitude }};

        // Initialize Map
        const map = L.map('shipment-map').setView([originLat, originLng], 4);

        // Dark-themed tiles from CartoDB for futuristic aesthetic
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        const routeCoords = [];

        // 1. Origin Marker
        const originMarker = L.circleMarker([originLat, originLng], {
            radius: 8,
            fillColor: '#06b6d4',
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map);
        originMarker.bindPopup(`<b>ORIGIN: {{ $shipment->origin->name }}</b><br>{{ $shipment->origin->city }}, {{ $shipment->origin->country }}`);
        routeCoords.push([originLat, originLng]);

        // 2. Tracking Checkpoints
        @foreach($shipment->trackingEvents as $ev)
            @if($ev->recorded_lat && $ev->recorded_lng)
                routeCoords.push([{{ $ev->recorded_lat }}, {{ $ev->recorded_lng }}]);
                
                L.circleMarker([{{ $ev->recorded_lat }}, {{ $ev->recorded_lng }}], {
                    radius: 6,
                    fillColor: '{{ $ev->event_type === "EXCEPTION_OCCURRED" ? "#ef4444" : "#38bdf8" }}',
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map).bindPopup(`
                    <div style="font-family: sans-serif; font-size: 11px;">
                        <b>{{ $ev->status_snapshot }}</b><br>
                        <span>Time: {{ $ev->event_time->format("d M, H:i") }}</span><br>
                        <span>Core Temp: {{ $ev->container_temperature ?? "N/A" }}°C</span><br>
                        <span>Ambient: {{ $ev->ambient_temperature ?? "N/A" }}°C</span>
                    </div>
                `);
            @endif
        @endforeach

        // 3. Destination Marker
        const destMarker = L.circleMarker([destLat, destLng], {
            radius: 8,
            fillColor: '#10b981',
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map);
        destMarker.bindPopup(`<b>DESTINATION: {{ $shipment->destination->name }}</b><br>{{ $shipment->destination->city }}, {{ $shipment->destination->country }}`);
        routeCoords.push([destLat, destLng]);

        // Draw Polyline connecting route
        const polyline = L.polyline(routeCoords, {
            color: '#06b6d4',
            weight: 3,
            opacity: 0.8,
            dashArray: '8, 8'
        }).addTo(map);

        // Fit bounds to show entire international route
        map.fitBounds(polyline.getBounds(), { padding: [50, 50] });
    });
</script>
@endpush
@endsection
