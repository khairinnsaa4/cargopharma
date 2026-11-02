@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER & ACTIONS -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Shipments</h1>
            <p class="text-xs text-slate-500">Manage and inspect all domestic and international freight consignments.</p>
        </div>

        @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
        <div>
            <a href="{{ route('shipments.create') }}" class="px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-sm transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Create Shipment</span>
            </a>
        </div>
        @endif
    </div>

    <!-- FILTER BAR (Slide 10: Search & Filter) -->
    <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
        <form method="GET" action="{{ route('shipments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <!-- Search Keyword -->
            <div class="md:col-span-2">
                <label class="block font-medium text-slate-700 mb-1">Search Identifier / Commodity</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. CT-BIO-2024-001 or Polio Vaccine" 
                        class="w-full pl-9 pr-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 font-mono">
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-blue-500">
                    <option value="">All Statuses</option>
                    @foreach(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Carrier Filter -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Carrier</label>
                <select name="carrier_id" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-blue-500">
                    <option value="">All Carriers</option>
                    @foreach($carriers as $carrier)
                        <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>{{ $carrier->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs flex items-center justify-center gap-1.5 transition-all">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Apply</span>
                </button>
                <a href="{{ route('shipments.index') }}" class="py-1.5 px-3 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- SHIPMENTS TABLE -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-medium">
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
                    @forelse($shipments as $shipment)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">
                            {{ $shipment->shipment_number }}
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-800">
                            {{ $shipment->customer->organization ?? $shipment->customer->name }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $shipment->carrier->name ?? 'Standard Convoy' }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $shipment->origin->city }}, {{ $shipment->origin->country }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $shipment->destination->city }}, {{ $shipment->destination->country }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-600">
                            {{ $shipment->expected_arrival->format('Y-m-d') }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($shipment->is_delayed)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold bg-rose-600 text-white">
                                    DELAYED
                                </span>
                            @elseif($shipment->current_status === 'DELIVERED')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-600 text-white">
                                    DELIVERED
                                </span>
                            @elseif($shipment->current_status === 'IN_TRANSIT')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold bg-blue-600 text-white">
                                    IN TRANSIT
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-medium bg-slate-200 text-slate-700">
                                    {{ $shipment->current_status }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('shipments.show', $shipment->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">
                                View Details →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 font-mono">
                            No shipments matching your filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $shipments->links() }}
        </div>
    </div>

</div>
@endsection
