@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HERO HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">MISSION CONTROL</span>
                <span class="text-xs text-slate-400 font-mono">LIVE TELEMETRY FEED</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Global Vaccine Logistics Oversight</h1>
            <p class="text-xs text-slate-400">Monitoring temperature-controlled biopharmaceutical exports from Indonesia to global health agencies.</p>
        </div>

        @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
        <div class="flex items-center gap-3">
            <a href="{{ route('shipments.create') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-400 hover:from-cyan-400 hover:to-cyan-300 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20 transition-all">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Register New Consignment</span>
            </a>
        </div>
        @endif
    </div>

    <!-- 5 KPI TELEMETRY METRIC TILES (Slide 10) -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- 1. Total Consignments -->
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 backdrop-blur-xl relative overflow-hidden group">
            <div class="text-[10px] font-mono uppercase text-slate-400 font-semibold mb-1 flex items-center justify-between">
                <span>Total Consignments</span>
                <i data-lucide="boxes" class="w-4 h-4 text-slate-400"></i>
            </div>
            <div class="text-2xl font-black text-white font-mono">{{ $totalShipments }}</div>
            <div class="text-[11px] text-cyan-400 font-mono mt-1">Verified Registry</div>
            <div class="absolute -right-3 -bottom-3 w-16 h-16 bg-cyan-500/5 rounded-full pointer-events-none"></div>
        </div>

        <!-- 2. Active In Transit -->
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 backdrop-blur-xl relative overflow-hidden group">
            <div class="text-[10px] font-mono uppercase text-slate-400 font-semibold mb-1 flex items-center justify-between">
                <span>Active In-Transit</span>
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
            </div>
            <div class="text-2xl font-black text-cyan-400 font-mono">{{ $inTransitCount }}</div>
            <div class="text-[11px] text-slate-400 font-mono mt-1">Under Active Cooling</div>
        </div>

        <!-- 3. Safe Delivered -->
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 backdrop-blur-xl relative overflow-hidden group">
            <div class="text-[10px] font-mono uppercase text-slate-400 font-semibold mb-1 flex items-center justify-between">
                <span>Delivered Intact</span>
                <i data-lucide="check-check" class="w-4 h-4 text-emerald-400"></i>
            </div>
            <div class="text-2xl font-black text-emerald-400 font-mono">{{ $deliveredCount }}</div>
            <div class="text-[11px] text-emerald-500/80 font-mono mt-1">Zero Temp Excursion</div>
        </div>

        <!-- 4. Delay Rate -->
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 backdrop-blur-xl relative overflow-hidden group">
            <div class="text-[10px] font-mono uppercase text-slate-400 font-semibold mb-1 flex items-center justify-between">
                <span>Delay Rate</span>
                <i data-lucide="clock" class="w-4 h-4 text-amber-400"></i>
            </div>
            <div class="text-2xl font-black {{ $delayedCount > 0 ? 'text-amber-400' : 'text-slate-200' }} font-mono">
                {{ $delayRate }}%
            </div>
            <div class="text-[11px] text-amber-400/80 font-mono mt-1">{{ $delayedCount }} consignment delayed</div>
        </div>

        <!-- 5. Open Exceptions -->
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 backdrop-blur-xl relative overflow-hidden group col-span-2 md:col-span-1">
            <div class="text-[10px] font-mono uppercase text-slate-400 font-semibold mb-1 flex items-center justify-between">
                <span>Active Incidents</span>
                <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-400"></i>
            </div>
            <div class="text-2xl font-black {{ $openExceptionsCount > 0 ? 'text-rose-400' : 'text-slate-200' }} font-mono">
                {{ $openExceptionsCount }}
            </div>
            <div class="text-[11px] text-rose-400/80 font-mono mt-1">Exceptions Requiring Review</div>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN DASHBOARD GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT (2 Cols): Active Consignments Monitor -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="radar" class="w-4 h-4 text-cyan-400"></i>
                    <h2 class="text-sm font-bold text-white tracking-wide uppercase font-mono">Live Consignment Tracking Grid</h2>
                </div>
                <a href="{{ route('shipments.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 font-mono flex items-center gap-1">
                    <span>View Full Registry</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="rounded-2xl bg-slate-900/80 border border-slate-800 backdrop-blur-xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-mono text-[10px] uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Consignment / Item</th>
                                <th class="py-3 px-4">Corridor (Origin → Dest)</th>
                                <th class="py-3 px-4">Telemetry Temp</th>
                                <th class="py-3 px-4">Status & Delay</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            @forelse($recentShipments as $shipment)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <!-- Consignment Number & Batch -->
                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-white text-xs flex items-center gap-1.5">
                                        <i data-lucide="file-code-2" class="w-3.5 h-3.5 text-cyan-400"></i>
                                        <span>{{ $shipment->shipment_number }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-[180px]">
                                        {{ $shipment->cargo_type }}
                                    </div>
                                </td>

                                <!-- Corridor / Route -->
                                <td class="py-3.5 px-4 font-mono">
                                    <div class="text-white text-xs flex items-center gap-1">
                                        <span>{{ $shipment->origin->city }}</span>
                                        <i data-lucide="arrow-right" class="w-3 h-3 text-slate-400"></i>
                                        <span class="text-cyan-300">{{ $shipment->destination->city }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Carrier: {{ $shipment->carrier->name ?? 'Direct Convoy' }}
                                    </div>
                                </td>

                                <!-- Telemetry Temperature Reading -->
                                <td class="py-3.5 px-4">
                                    @php
                                        $lastEvent = $shipment->latestTrackingEvent;
                                        $containerTemp = $lastEvent?->container_temperature;
                                    @endphp
                                    @if($containerTemp !== null)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-mono text-xs font-bold {{ $containerTemp > $shipment->target_temp_max ? 'bg-rose-500/10 text-rose-400 border border-rose-500/30' : 'bg-cyan-500/10 text-cyan-300 border border-cyan-500/30' }}">
                                        <i data-lucide="thermometer-snowflake" class="w-3 h-3"></i>
                                        <span>{{ number_format($containerTemp, 1) }}°C</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">Target: {{ $shipment->target_temp_min }}°C</div>
                                    @else
                                    <span class="text-slate-400 font-mono text-[11px]">En-route</span>
                                    @endif
                                </td>

                                <!-- Status Badge & Delay Severity -->
                                <td class="py-3.5 px-4">
                                    @if($shipment->current_status === 'DELIVERED')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                            DELIVERED
                                        </span>
                                    @elseif($shipment->current_status === 'IN_TRANSIT')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span> IN TRANSIT
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                            {{ $shipment->current_status }}
                                        </span>
                                    @endif

                                    @if($shipment->is_delayed)
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-mono font-bold {{ $shipment->delay_severity === 'HIGH' ? 'bg-rose-950 text-rose-400 border border-rose-800' : 'bg-amber-950 text-amber-400 border border-amber-800' }}">
                                            <i data-lucide="alert-triangle" class="w-2.5 h-2.5"></i>
                                            DELAY {{ $shipment->delay_duration_hours }}H ({{ $shipment->delay_severity }})
                                        </span>
                                    </div>
                                    @endif
                                </td>

                                <!-- Action -->
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('shipments.show', $shipment->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-cyan-500 hover:text-slate-950 text-slate-200 text-xs font-semibold transition-all">
                                        <span>Track</span>
                                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 font-mono text-xs">
                                    No active consignments registered.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT (1 Col): Status Chart & Active Incidents -->
        <div class="space-y-6">
            <!-- Cold Chain Status Distribution Chart -->
            <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-5 backdrop-blur-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-cyan-400"></i> Lifecycle Distribution
                    </h3>
                </div>

                <div class="h-48 relative flex items-center justify-center">
                    <canvas id="statusChart"></canvas>
                </div>

                <div class="grid grid-cols-2 gap-2 text-center text-xs font-mono pt-2 border-t border-slate-800/80">
                    <div class="p-2 rounded-xl bg-slate-950/60 border border-slate-800">
                        <span class="text-slate-400 text-[10px] block">In Transit</span>
                        <span class="text-cyan-400 font-bold text-sm">{{ $inTransitCount }}</span>
                    </div>
                    <div class="p-2 rounded-xl bg-slate-950/60 border border-slate-800">
                        <span class="text-slate-400 text-[10px] block">Delivered</span>
                        <span class="text-emerald-400 font-bold text-sm">{{ $deliveredCount }}</span>
                    </div>
                </div>
            </div>

            <!-- Active Critical Exceptions Feed -->
            <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-5 backdrop-blur-xl space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-mono font-bold text-rose-400 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="flame" class="w-4 h-4 text-rose-400"></i> Critical Incidents Feed
                    </h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-rose-500/10 text-rose-400 border border-rose-500/30">
                        {{ $activeExceptions->count() }} ACTIVE
                    </span>
                </div>

                <div class="space-y-2.5">
                    @forelse($activeExceptions as $exception)
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-mono font-bold text-white">{{ $exception->shipment->shipment_number }}</span>
                            <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold {{ $exception->severity === 'HIGH' ? 'bg-rose-500/20 text-rose-400' : 'bg-amber-500/20 text-amber-400' }}">
                                {{ $exception->severity }}
                            </span>
                        </div>
                        <div class="text-[11px] font-semibold text-rose-300">
                            {{ str_replace('_', ' ', $exception->exception_type) }}
                        </div>
                        <div class="text-[11px] text-slate-400 line-clamp-2">
                            {{ $exception->root_cause }}
                        </div>
                        <div class="pt-1 flex items-center justify-between text-[10px] font-mono text-slate-400">
                            <span>Detected: {{ $exception->detected_at->diffForHumans() }}</span>
                            <a href="{{ route('shipments.show', $exception->shipment_id) }}" class="text-cyan-400 hover:underline">Investigate →</a>
                        </div>
                    </div>
                    @empty
                    <div class="py-6 text-center text-slate-400 font-mono text-xs">
                        <i data-lucide="shield-check" class="w-8 h-8 text-emerald-400 mx-auto mb-2 opacity-60"></i>
                        No critical cold chain exceptions logged.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    // Lifecycle Chart (Slide 14: Chart.js)
    const ctx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($statusDistribution);

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Created', 'Booked', 'Picked Up', 'In Transit', 'Delivered'],
            datasets: [{
                data: [
                    statusData['CREATED'] || 0,
                    statusData['BOOKED'] || 0,
                    statusData['PICKED_UP'] || 0,
                    statusData['IN_TRANSIT'] || 0,
                    statusData['DELIVERED'] || 0
                ],
                backgroundColor: [
                    '#475569',
                    '#64748b',
                    '#0284c7',
                    '#06b6d4',
                    '#10b981'
                ],
                borderColor: '#0f172a',
                borderWidth: 3,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#94a3b8',
                        font: { size: 10, family: 'JetBrains Mono' },
                        boxWidth: 10
                    }
                }
            },
            cutout: '72%'
        }
    });
</script>
@endpush
@endsection
