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
            <a href="{{ route('shipments.create') }}" class="px-3.5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-xs transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>New Consignment</span>
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
            <div class="w-11 h-11 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0 border border-teal-100">
                <i data-lucide="package" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Total Shipments</span>
                <span class="text-xl font-bold text-slate-900 leading-tight">{{ $totalShipments }}</span>
            </div>
        </div>

        <!-- 2. In Transit -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0 border border-teal-100">
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

        <!-- Card 2: Delay Rate / Cold-Chain Integrity (Donut Chart) -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs flex flex-col justify-between space-y-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">{{ $carrierChartTitle }}</h3>
                <p class="text-xs text-slate-500">{{ $carrierChartSubtitle }}</p>
            </div>

            <!-- Centered Donut Canvas with Dead-Center Metric -->
            <div class="h-36 relative flex items-center justify-center my-0.5">
                <canvas id="carrierDonutChart"></canvas>
                <!-- Center % metric inside the donut hole -->
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                    <span class="text-xl font-black text-slate-900 leading-tight">{{ $carrierMetricValue }}</span>
                    <span class="text-[9px] text-slate-400 font-semibold tracking-wider uppercase">{{ $carrierMetricLabel }}</span>
                </div>
            </div>

            <!-- Structured HTML Legend: Zero clipping, zero overlap -->
            <div class="pt-2.5 border-t border-slate-100 space-y-2">
                <div class="space-y-1.5 text-[11px]">
                    @foreach($carrierLegendItems as $item)
                    <div class="flex items-center justify-between py-1 px-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="flex items-center gap-2 truncate">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-2xs" style="background-color: {{ $item['color'] }}"></span>
                            <span class="text-slate-700 font-medium truncate" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                        </span>
                        <span class="font-mono font-bold text-slate-900 ml-2 shrink-0">{{ $item['value'] }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="pt-1 flex items-center justify-between text-xs text-slate-500">
                    <span>Benchmark:</span>
                    {!! $carrierBenchmark !!}
                </div>
            </div>
        </div>

        <!-- Card 3: Shipment Map (Interactive Mini Leaflet Map) -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-4 flex flex-col">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Shipment Map</h3>
                    <p class="text-xs text-slate-500">Real-time corridor routes & checkpoints</p>
                </div>
                <a href="{{ route('tracking.index') }}" class="text-xs text-teal-700 hover:text-teal-900 font-semibold flex items-center gap-1">
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
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold bg-teal-600 text-white shadow-xs">
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
                            <a href="{{ route('shipments.show', $shipment->id) }}" class="text-teal-700 hover:text-teal-900 font-semibold hover:underline">
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

    <!-- 4. GLOBAL BIOPHARMA LOGISTICS RADAR SUMMARY -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-200 shrink-0">
                <i data-lucide="globe-2" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-slate-900">Global Biopharma Logistics Radar</h3>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                        Live FX Rates
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Live Satellite Wx
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Live Global Logistics &amp; Advisories, konversi valuta asing real-time (bebas pilih negara), dan peta radar cuaca bandara dunia.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('global-radar.index') }}" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-xs transition-all flex items-center gap-1.5">
                <span>Buka Global Radar</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
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
                        '#0d9488', // Medical Teal
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

        // 2. Delay Rate / Cold-Chain Integrity (Donut Chart)
        const donutCtx = document.getElementById('carrierDonutChart').getContext('2d');
        const carrierLabels = @json($carrierChartLabels);
        const carrierData = @json($carrierChartData);
        const carrierColors = @json($carrierChartColors);

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: carrierLabels,
                datasets: [{
                    data: carrierData,
                    backgroundColor: carrierColors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false // Turned off so donut stays dead-centered!
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.raw + '%';
                            }
                        }
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

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap'
        }).addTo(miniMap);

        const latLngs = [];
        routes.forEach(r => {
            const originPt = [r.origin.lat, r.origin.lng];
            const destPt = [r.destination.lat, r.destination.lng];
            latLngs.push(originPt, destPt);

            // Add clinical circular markers
            L.circleMarker(originPt, { radius: 5, color: '#0f766e', fillColor: '#0f766e', fillOpacity: 1 }).addTo(miniMap);
            L.circleMarker(destPt, { radius: 5, color: '#10b981', fillColor: '#10b981', fillOpacity: 1 }).addTo(miniMap);

            // Draw line
            L.polyline([originPt, destPt], {
                color: r.is_delayed ? '#f59e0b' : '#0d9488',
                weight: 2,
                opacity: 0.8,
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
