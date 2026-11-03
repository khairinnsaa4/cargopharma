@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- PAGE TITLE & BREADCRUMB -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
            <p class="text-xs text-slate-500">Real-time overview of global pharmaceutical & cold-chain shipment visibility.</p>
        </div>
        <div class="flex items-center gap-2">
            @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
            <a href="{{ route('shipments.create') }}" class="px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-sm transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>New Shipment</span>
            </a>
            @endif
        </div>
    </div>

    <!-- REAL-TIME FINANCIAL & CURRENCY TELEMETRY TICKER (ECB / Frankfurter API) -->
    <div class="px-4 py-3 rounded-xl bg-slate-900 text-white border border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 shrink-0">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold tracking-wider uppercase text-emerald-400 font-mono flex items-center gap-1">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> Live FX Rates
                </span>
                <span class="hidden sm:inline-block text-[11px] text-slate-400">| European Central Bank Benchmark</span>
            </div>
        </div>

        <div class="flex items-center gap-3 text-xs font-mono overflow-x-auto py-0.5">
            @foreach($currencyData['pairs'] as $pair)
            <div class="flex items-center gap-1.5 shrink-0 px-2.5 py-1 rounded-lg bg-slate-800/90 border border-slate-700/60">
                <span class="text-slate-400 text-[11px]">{{ $pair['label'] }}:</span>
                <span class="font-bold text-white">{{ $pair['symbol'] }} {{ number_format($pair['rate'], $pair['code'] === 'IDR' ? 0 : 4) }}</span>
            </div>
            @endforeach
            <span class="text-[10px] text-slate-400 shrink-0 font-sans pl-1">Ref: {{ $currencyData['date'] }}</span>
        </div>
    </div>

    <!-- 1. TOP STATS ROW: 5 KPI CARDS (Matching Example Photo) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- 1. Total Shipments -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <i data-lucide="package" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Total Shipments</span>
                <span class="text-xl font-bold text-slate-900 leading-tight">{{ $totalShipments }}</span>
            </div>
        </div>

        <!-- 2. In Transit -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <i data-lucide="truck" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">In Transit</span>
                <span class="text-xl font-bold text-slate-900 leading-tight">{{ $inTransitCount }}</span>
            </div>
        </div>

        <!-- 3. Delivered -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Delivered</span>
                <span class="text-xl font-bold text-slate-900 leading-tight">{{ $deliveredCount }}</span>
            </div>
        </div>

        <!-- 4. Delayed -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Delayed</span>
                <span class="text-xl font-bold text-slate-900 leading-tight">{{ $delayedCount }}</span>
            </div>
        </div>

        <!-- 5. Open Exceptions -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3 col-span-2 sm:col-span-1">
            <div class="w-11 h-11 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Open Exceptions</span>
                <span class="text-xl font-bold text-rose-600 leading-tight">{{ $openExceptionsCount }}</span>
            </div>
        </div>
    </div>

    <!-- 2. MIDDLE 3-CARD ROW (Matching Example Photo: Bar Chart, Donut Chart, Mini Map) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Card 1: Shipments by Status (Bar Chart) -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Shipments by Status</h3>
                <p class="text-xs text-slate-500">Overview of active consignments by lifecycle stage</p>
            </div>

            <div class="h-44 relative">
                <canvas id="statusBarChart"></canvas>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-mono">
                <span>Total Active Pool:</span>
                <span class="font-bold text-slate-800">{{ $totalShipments }} Units</span>
            </div>
        </div>

        <!-- Card 2: Delay Rate by Carrier (Donut Chart) -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Delay Rate by Carrier</h3>
                <p class="text-xs text-slate-500">Distribution of delay occurrences across carrier partners</p>
            </div>

            <div class="h-44 relative flex items-center justify-center">
                <canvas id="carrierDonutChart"></canvas>
                <!-- Center % metric -->
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-lg font-black text-slate-900">{{ $averageDelayRate }}%</span>
                    <span class="text-[9px] text-slate-400 font-medium">Avg Delay</span>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
                <span>Compliance Benchmark:</span>
                <span class="text-emerald-600 font-semibold font-mono">&lt; 15% Safe</span>
            </div>
        </div>

        <!-- Card 3: Shipment Map (Interactive Mini Leaflet Map) -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-4 flex flex-col">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Shipment Map</h3>
                    <p class="text-xs text-slate-500">Real-time corridor routes & checkpoints</p>
                </div>
                <a href="{{ route('tracking.index') }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1">
                    <span>Full Map</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <!-- Mini Map Canvas -->
            <div id="dashboard-mini-map" class="flex-1 w-full min-h-[175px] rounded-lg border border-slate-200 overflow-hidden z-0"></div>
        </div>

    </div>

    <!-- 3. BOTTOM SECTION: RECENT SHIPMENTS TABLE (Matching Example Photo) -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <!-- Table Header & Action Controls -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Recent Shipments</h3>
                <p class="text-xs text-slate-500">Live operational overview of all active and delivered consignments</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('shipments.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-all">
                    <i data-lucide="filter" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Filter</span>
                </a>
                <a href="{{ route('shipments.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-all">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Export</span>
                </a>
            </div>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-medium">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Shipment No.</th>
                        <th class="py-3 px-4 font-semibold">Customer</th>
                        <th class="py-3 px-4 font-semibold">Carrier</th>
                        <th class="py-3 px-4 font-semibold">Origin</th>
                        <th class="py-3 px-4 font-semibold">Destination</th>
                        <th class="py-3 px-4 font-semibold">ETA</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentShipments as $shipment)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <!-- Shipment No -->
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">
                            {{ $shipment->shipment_number }}
                        </td>

                        <!-- Customer -->
                        <td class="py-3.5 px-4">
                            <span class="font-medium text-slate-800">{{ $shipment->customer->organization ?? $shipment->customer->name }}</span>
                        </td>

                        <!-- Carrier -->
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $shipment->carrier->name ?? 'Standard Fleet' }}
                        </td>

                        <!-- Origin -->
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $shipment->origin->city }}, {{ $shipment->origin->country }}
                        </td>

                        <!-- Destination -->
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $shipment->destination->city }}, {{ $shipment->destination->country }}
                        </td>

                        <!-- ETA -->
                        <td class="py-3.5 px-4 font-mono text-slate-600">
                            {{ $shipment->expected_arrival->format('Y-m-d') }}
                        </td>

                        <!-- Status Badge (Matching Photo colors) -->
                        <td class="py-3.5 px-4">
                            @if($shipment->is_delayed)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold bg-rose-600 text-white shadow-xs">
                                    DELAYED
                                </span>
                            @elseif($shipment->current_status === 'DELIVERED')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-600 text-white shadow-xs">
                                    DELIVERED
                                </span>
                            @elseif($shipment->current_status === 'IN_TRANSIT')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold bg-blue-600 text-white shadow-xs">
                                    IN TRANSIT
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-medium bg-slate-200 text-slate-700">
                                    {{ $shipment->current_status }}
                                </span>
                            @endif
                        </td>

                        <!-- Action -->
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('shipments.show', $shipment->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 font-mono">
                            No shipments registered in the database.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. REAL-TIME LOGISTICS & COLD-CHAIN ADVISORIES (ReliefWeb / WHO Bulletins) -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="radio" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Live Global Logistics &amp; Health Advisories</h3>
                    <p class="text-xs text-slate-500">Real-time alerts, WHO vaccine distribution notices, and airport hub bulletins</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Live Telemetry Feed
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            @foreach($advisories as $advisory)
            <div class="p-5 space-y-2 hover:bg-slate-50/50 transition-colors">
                <div class="flex items-center justify-between gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $advisory['badge_color'] }}">
                        {{ $advisory['category'] }}
                    </span>
                    <span class="text-[11px] font-mono text-slate-400">{{ $advisory['date'] }}</span>
                </div>
                <h4 class="text-xs font-bold text-slate-900 leading-snug">
                    {{ $advisory['title'] }}
                </h4>
                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                    {{ $advisory['summary'] }}
                </p>
                <div class="pt-1 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400 font-medium">Source: <strong class="text-slate-700">{{ $advisory['source'] }}</strong></span>
                    <a href="{{ $advisory['url'] }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center gap-1">
                        <span>Read Notice</span>
                        <i data-lucide="external-link" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // 1. Shipments by Status (Bar Chart)
        const barCtx = document.getElementById('statusBarChart').getContext('2d');
        const barData = @json($barChartData);

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['In Transit', 'Delivered', 'Delayed', 'Exceptions'],
                datasets: [{
                    label: 'Shipments',
                    data: [
                        barData.in_transit || 0,
                        barData.delivered || 0,
                        barData.delayed || 0,
                        barData.exceptions || 0
                    ],
                    backgroundColor: [
                        '#0284c7', // Sky blue
                        '#10b981', // Emerald green
                        '#f59e0b', // Amber
                        '#ef4444'  // Rose red
                    ],
                    borderRadius: 6,
                    maxBarThickness: 36
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10, family: 'Inter' } } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1, font: { size: 10 } } }
                }
            }
        });

        // 2. Delay Rate by Carrier (Donut Chart)
        const donutCtx = document.getElementById('carrierDonutChart').getContext('2d');
        const carrierLabels = @json($carrierChartLabels);
        const carrierData = @json($carrierChartData);

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: carrierLabels,
                datasets: [{
                    data: carrierData,
                    backgroundColor: ['#0284c7', '#f97316', '#64748b', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '74%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 10, font: { size: 10, family: 'Inter' }, padding: 8 }
                    }
                }
            }
        });

        // 3. Mini Leaflet Map (Matching Photo's mini route display)
        const routes = @json($mapRoutes);
        const miniMap = L.map('dashboard-mini-map', {
            zoomControl: false,
            attributionControl: false
        }).setView([-6.89, 107.59], 2);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 18
        }).addTo(miniMap);

        const latLngs = [];
        routes.forEach(r => {
            const originPt = [r.origin.lat, r.origin.lng];
            const destPt = [r.destination.lat, r.destination.lng];
            latLngs.push(originPt, destPt);

            // Add simple circular markers
            L.circleMarker(originPt, { radius: 5, color: '#0284c7', fillColor: '#0284c7', fillOpacity: 1 }).addTo(miniMap);
            L.circleMarker(destPt, { radius: 5, color: '#10b981', fillColor: '#10b981', fillOpacity: 1 }).addTo(miniMap);

            // Draw line
            L.polyline([originPt, destPt], {
                color: r.is_delayed ? '#f59e0b' : '#0284c7',
                weight: 2,
                opacity: 0.7,
                dashArray: '4, 4'
            }).addTo(miniMap);
        });

        if (latLngs.length > 0) {
            miniMap.fitBounds(L.latLngBounds(latLngs), { padding: [20, 20] });
        }
    });
</script>
@endpush
@endsection
