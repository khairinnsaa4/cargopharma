@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER & ACTIONS -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">CONSIGNMENT REGISTRY</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Vaccine Consignments</h1>
            <p class="text-xs text-slate-400">Search and filter international vaccine exports under active cold-chain supervision.</p>
        </div>

        @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
        <div>
            <a href="{{ route('shipments.create') }}" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20 transition-all">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Register Consignment</span>
            </a>
        </div>
        @endif
    </div>

    <!-- FILTER BAR (Slide 10: Search & Filter) -->
    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 backdrop-blur-xl">
        <form method="GET" action="{{ route('shipments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <!-- Search Keyword -->
            <div class="md:col-span-2">
                <label class="block text-[10px] font-mono uppercase text-slate-400 mb-1">Search Identifier / Batch</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. CT-BIO-2024-001 or LOT089" 
                        class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-950/80 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 font-mono">
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[10px] font-mono uppercase text-slate-400 mb-1">Lifecycle Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl bg-slate-950/80 border border-slate-800 text-xs text-white focus:outline-none focus:border-cyan-500 font-mono">
                    <option value="">All Statuses</option>
                    @foreach(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Carrier Filter -->
            <div>
                <label class="block text-[10px] font-mono uppercase text-slate-400 mb-1">Carrier</label>
                <select name="carrier_id" class="w-full px-3 py-2 rounded-xl bg-slate-950/80 border border-slate-800 text-xs text-white focus:outline-none focus:border-cyan-500">
                    <option value="">All Carriers</option>
                    @foreach($carriers as $carrier)
                        <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>{{ $carrier->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-400 font-bold text-xs flex items-center justify-center gap-1.5 transition-all">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Apply</span>
                </button>
                <a href="{{ route('shipments.index') }}" class="py-2 px-3 rounded-xl bg-slate-950/80 hover:bg-slate-800 text-slate-400 text-xs flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- SHIPMENTS TABLE -->
    <div class="rounded-2xl bg-slate-900/80 border border-slate-800 backdrop-blur-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 border-b border-slate-800 text-slate-400 font-mono text-[10px] uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Waybill & Consignee</th>
                        <th class="py-3 px-4">Product & Specification</th>
                        <th class="py-3 px-4">Corridor & Carrier</th>
                        <th class="py-3 px-4">Cold-Chain ETA</th>
                        <th class="py-3 px-4">Status & Telemetry</th>
                        <th class="py-3 px-4 text-right">Inspect</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($shipments as $shipment)
                    <tr class="hover:bg-slate-800/40 transition-colors">
                        <!-- Waybill & Customer -->
                        <td class="py-4 px-4">
                            <div class="font-mono font-bold text-white text-xs flex items-center gap-1.5">
                                <i data-lucide="box" class="w-4 h-4 text-cyan-400"></i>
                                <span>{{ $shipment->shipment_number }}</span>
                            </div>
                            <div class="text-[11px] text-slate-400 truncate max-w-[200px] mt-0.5">
                                {{ $shipment->customer->organization ?? $shipment->customer->name }}
                            </div>
                        </td>

                        <!-- Vaccine Product -->
                        <td class="py-4 px-4">
                            <div class="text-xs font-semibold text-white">
                                {{ $shipment->cargo_type }}
                            </div>
                            <div class="text-[11px] font-mono text-cyan-400 mt-0.5">
                                Target: {{ $shipment->target_temp_min }}°C to {{ $shipment->target_temp_max }}°C
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono">
                                Weight: {{ number_format($shipment->total_weight_kg, 0) }} kg
                            </div>
                        </td>

                        <!-- Corridor -->
                        <td class="py-4 px-4 font-mono">
                            <div class="text-white text-xs flex items-center gap-1">
                                <span>{{ $shipment->origin->city }}</span>
                                <i data-lucide="arrow-right" class="w-3 h-3 text-slate-400"></i>
                                <span class="text-cyan-300">{{ $shipment->destination->city }}</span>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $shipment->carrier->name ?? 'Standard Convoy' }}
                            </div>
                        </td>

                        <!-- ETA & Departure -->
                        <td class="py-4 px-4 font-mono text-xs">
                            <div class="text-white">
                                ETA: {{ $shipment->expected_arrival->format('d M Y, H:i') }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                Dep: {{ $shipment->expected_departure->format('d M Y') }}
                            </div>
                        </td>

                        <!-- Status & Delay Severity -->
                        <td class="py-4 px-4">
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
                                    DELAY +{{ $shipment->delay_duration_hours }}H
                                </span>
                            </div>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-4 text-right">
                            <a href="{{ route('shipments.show', $shipment->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-cyan-500 hover:text-slate-950 text-slate-200 text-xs font-semibold transition-all">
                                <span>Track Mission</span>
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400 font-mono text-xs">
                            No shipments matching your filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination (Slide 10: Server-side pagination) -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            {{ $shipments->links() }}
        </div>
    </div>

</div>
@endsection
