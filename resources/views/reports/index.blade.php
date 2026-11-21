@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Laporan</h1>

        <div class="flex items-center gap-2">
            <a href="{{ route('reports.exportCsv', request()->query()) }}" class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i> Ekspor CSV
            </a>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block font-medium text-slate-600 mb-1">Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-600 mb-1">Selesai</label>
                <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-600 mb-1">Kurir &amp; Armada</label>
                <select name="carrier_id" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                    <option value="">Semua Armada (Udara, Laut, Darat)</option>
                    @foreach($carriers as $c)
                    <option value="{{ $c->id }}" {{ request('carrier_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->transport_type === 'land' ? '🚚' : ($c->transport_type === 'sea' ? '🚢' : '✈️') }} {{ $c->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold shadow-xs flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('reports.index') }}" class="py-2 px-3 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- KPI SUMMARY CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Consignments -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Pengiriman</span>
            <div class="flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-slate-900">{{ $totalFiltered }}</span>
                <span class="text-xs text-slate-500 font-mono">{{ $inTransitFiltered }} Aktif</span>
            </div>
            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100 flex items-center justify-between">
                <span>Terkirim: {{ $deliveredFiltered }}</span>
                <span>Tertunda: {{ $delayedFiltered }}</span>
            </div>
        </div>

        <!-- 2. Temperature Compliance -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kepatuhan Rantai Dingin</span>
            <div class="flex items-baseline justify-between">
                @if($tempComplianceRate !== null)
                <span class="text-2xl font-extrabold {{ $tempComplianceRate >= 95 ? 'text-emerald-600' : ($tempComplianceRate >= 80 ? 'text-amber-600' : 'text-rose-600') }}">
                    {{ $tempComplianceRate }}%
                </span>
                @else
                <span class="text-sm font-bold text-slate-400 italic">Belum ada telemetri</span>
                @endif
                <span class="text-[10px] text-slate-400 font-mono">{{ $safeReadings }}/{{ $totalReadings }}</span>
            </div>
            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                Titik telemetri dalam batas suhu aman
            </div>
        </div>

        <!-- 3. Overall Delay Rate -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tingkat Delay (SLA)</span>
            <div class="flex items-baseline justify-between">
                @php
                    $delayRate = $totalFiltered > 0 ? round(($delayedFiltered / $totalFiltered) * 100, 1) : 0;
                @endphp
                <span class="text-2xl font-extrabold {{ $delayRate <= 10 ? 'text-emerald-600' : ($delayRate <= 25 ? 'text-amber-600' : 'text-rose-600') }}">
                    {{ $delayRate }}%
                </span>
                <span class="text-xs text-slate-500 font-mono">{{ $delayedFiltered }} Pengiriman</span>
            </div>
            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                Tepat Waktu: {{ 100 - $delayRate }}%
            </div>
        </div>

        <!-- 4. Open Incidents / Exceptions -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Insiden Terbuka</span>
            <div class="flex items-baseline justify-between">
                <span class="text-2xl font-extrabold {{ $openExceptions > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $openExceptions }}
                </span>
                <span class="text-xs text-slate-400">Suhu / Bea Cukai</span>
            </div>
            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                Membutuhkan tindakan investigasi
            </div>
        </div>
    </div>

    <!-- CARRIER PERFORMANCE BREAKDOWN -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="truck" class="w-4 h-4 text-teal-600"></i> Performa Kurir &amp; Rincian SLA
            </h3>
            <span class="text-[11px] text-slate-400 font-mono">{{ count($carrierStats) }} Kurir Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Nama Mitra Kurir &amp; Moda</th>
                        <th class="py-3 px-4 text-center">Total Pengiriman</th>
                        <th class="py-3 px-4 text-center">Tertunda</th>
                        <th class="py-3 px-4 text-center">Tingkat Delay</th>
                        <th class="py-3 px-4 text-center">SLA Tepat Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($carrierStats as $cs)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-slate-900">
                            <div class="flex items-center gap-2">
                                @if(($cs['type'] ?? '') === 'air')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">✈️ UDARA</span>
                                @elseif(($cs['type'] ?? '') === 'sea')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">🚢 LAUT</span>
                                @elseif(($cs['type'] ?? '') === 'land')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">🚚 DARAT</span>
                                @endif
                                <span>{{ $cs['name'] }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold">
                            {{ $cs['total'] }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono {{ $cs['delayed'] > 0 ? 'text-amber-600 font-bold' : 'text-slate-500' }}">
                            {{ $cs['delayed'] }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $cs['delay_rate'] <= 15 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $cs['delay_rate'] }}%
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono text-emerald-700 font-semibold">
                            {{ 100 - $cs['delay_rate'] }}%
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400 italic">
                            Tidak ada pengiriman tercatat untuk kurir dalam rentang tanggal tersebut.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- DETAILED AUDIT TABLE -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-teal-600"></i> Log Pengiriman (Periode Terfilter)
            </h3>
            <span class="text-[11px] text-slate-400 font-mono">{{ $recentShipments->total() }} Data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Nomor Resi</th>
                        <th class="py-3 px-4">Penerima</th>
                        <th class="py-3 px-4">Kurir</th>
                        <th class="py-3 px-4">Rute</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Keterlambatan (SLA)</th>
                        <th class="py-3 px-4 text-right">Terdaftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentShipments as $s)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-teal-800">
                            <a href="{{ route('shipments.show', $s->id) }}" class="hover:underline">
                                {{ $s->shipment_number }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900">
                            {{ $s->customer->organization ?? $s->customer->name ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-1.5 font-medium text-slate-800">
                                @if(($s->carrier->transport_type ?? '') === 'air')
                                    <span class="text-xs" title="Kargo Udara">✈️</span>
                                @elseif(($s->carrier->transport_type ?? '') === 'sea')
                                    <span class="text-xs" title="Kargo Laut">🚢</span>
                                @elseif(($s->carrier->transport_type ?? '') === 'land')
                                    <span class="text-xs" title="Kargo Darat">🚚</span>
                                @endif
                                <span>{{ $s->carrier->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-[11px]">
                            <span class="text-slate-800">{{ $s->origin->city ?? '-' }}</span>
                            <span class="text-slate-400">&rarr;</span>
                            <span class="text-slate-800">{{ $s->destination->city ?? '-' }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono
                                @if($s->current_status === 'DELIVERED') bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($s->current_status === 'IN_TRANSIT') bg-teal-50 text-teal-700 border border-teal-200
                                @elseif($s->current_status === 'CANCELLED') bg-rose-50 text-rose-700 border border-rose-200
                                @else bg-slate-50 text-slate-700 border border-slate-200 @endif">
                                {{ $s->current_status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($s->is_delayed)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                TERTUNDA ({{ $s->delay_severity }})
                            </span>
                            @else
                            <span class="text-emerald-700 font-semibold text-[11px]">TEPAT WAKTU</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-[11px] text-slate-400">
                            {{ $s->created_at->format('d M Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 italic">
                            Tidak ada pengiriman dalam rentang tanggal yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recentShipments->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $recentShipments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
