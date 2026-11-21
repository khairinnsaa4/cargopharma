@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER & ACTIONS -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pengiriman</h1>

        @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
        <div>
            <a href="{{ route('shipments.create') }}" class="px-3.5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-xs transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah</span>
            </a>
        </div>
        @endif
    </div>

    <!-- FILTER BAR -->
    <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
        <form method="GET" action="{{ route('shipments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <!-- Search Keyword -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Cari Resi</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari resi / komoditas..." 
                        class="w-full pl-9 pr-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-600 font-mono">
                </div>
            </div>

            <!-- Moda Filter (Darat, Laut, Udara) -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Moda Rute</label>
                <select name="transport_type" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-teal-600">
                    <option value="">Semua Moda</option>
                    <option value="air" {{ request('transport_type') === 'air' ? 'selected' : '' }}>✈️ Udara (Air)</option>
                    <option value="land" {{ request('transport_type') === 'land' ? 'selected' : '' }}>🚚 Darat (Land)</option>
                    <option value="sea" {{ request('transport_type') === 'sea' ? 'selected' : '' }}>🚢 Laut (Sea)</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-teal-600">
                    <option value="">Semua Status</option>
                    @php
                        $statusLabels = [
                            'CREATED' => 'Dibuat',
                            'BOOKED' => 'Dipesan',
                            'PICKED_UP' => 'Dijemput',
                            'IN_TRANSIT' => 'Dalam Perjalanan',
                            'DELIVERED' => 'Terkirim',
                            'CANCELLED' => 'Dibatalkan',
                        ];
                    @endphp
                    @foreach($statusLabels as $stKey => $stLabel)
                        <option value="{{ $stKey }}" {{ request('status') === $stKey ? 'selected' : '' }}>{{ $stLabel }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Carrier Filter -->
            <div>
                <label class="block font-medium text-slate-700 mb-1">Kurir</label>
                <select name="carrier_id" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-teal-600">
                    <option value="">Semua Kurir</option>
                    @foreach($carriers as $carrier)
                        <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                            {{ $carrier->transport_type === 'land' ? '🚚' : ($carrier->transport_type === 'sea' ? '🚢' : '✈️') }}
                            {{ $carrier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs flex items-center justify-center gap-1.5 transition-all">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filter</span>
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
                        <th class="py-3 px-4 font-semibold">Resi &amp; Moda</th>
                        <th class="py-3 px-4 font-semibold">Penerima</th>
                        <th class="py-3 px-4 font-semibold">Kurir</th>
                        <th class="py-3 px-4 font-semibold">Rute</th>
                        <th class="py-3 px-4 font-semibold">ETA</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($shipments as $shipment)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">
                            <div class="flex items-center gap-1.5">
                                @if($shipment->carrier?->transport_type === 'land')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200" title="Kargo Darat">🚚 Darat</span>
                                @elseif($shipment->carrier?->transport_type === 'sea')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-200" title="Kargo Laut">🚢 Laut</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-800 border border-sky-200" title="Kargo Udara">✈️ Udara</span>
                                @endif
                                <span>{{ $shipment->shipment_number }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-800">
                            {{ $shipment->customer->organization ?? $shipment->customer->name }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $shipment->carrier->name ?? 'Armada Standar' }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 font-medium">
                            <span class="flex items-center gap-1">
                                <span>{{ $shipment->origin->city }}</span>
                                <span class="text-teal-600">&rarr;</span>
                                <span>{{ $shipment->destination->city }}</span>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-600">
                            {{ $shipment->expected_arrival->format('Y-m-d') }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($shipment->is_delayed)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold bg-rose-600 text-white shadow-2xs">
                                    TERTUNDA
                                </span>
                            @elseif($shipment->current_status === 'DELIVERED')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-600 text-white shadow-2xs">
                                    TERKIRIM
                                </span>
                            @elseif($shipment->current_status === 'IN_TRANSIT')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold bg-teal-600 text-white shadow-2xs">
                                    DALAM PERJALANAN
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-medium bg-slate-200 text-slate-700">
                                    {{ $shipment->current_status }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('tracking.index', ['q' => $shipment->shipment_number]) }}" title="Lacak di Peta" class="p-1.5 rounded-lg text-slate-400 hover:text-teal-700 hover:bg-slate-100 transition-colors inline-flex items-center">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('shipments.show', $shipment->id) }}" title="Detail" class="p-1.5 rounded-lg text-slate-400 hover:text-teal-700 hover:bg-slate-100 transition-colors inline-flex items-center">
                                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 font-mono">
                            Tidak ada pengiriman terdaftar.
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
