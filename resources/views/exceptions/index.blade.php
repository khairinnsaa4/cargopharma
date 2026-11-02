@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Shipment Exceptions & Incidents</h1>
            <p class="text-xs text-slate-500">Monitor transit delays, customs holds, and cold-chain temperature excursions.</p>
        </div>
    </div>

    <!-- 3 SUMMARY STATS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Open Incidents</span>
                <span class="text-xl font-bold text-rose-600">{{ $openCount }}</span>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="search" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Under Investigation</span>
                <span class="text-xl font-bold text-amber-600">{{ $investigatingCount }}</span>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-medium block">Resolved Incidents</span>
                <span class="text-xl font-bold text-emerald-600">{{ $resolvedCount }}</span>
            </div>
        </div>
    </div>

    <!-- EXCEPTIONS LIST TABLE -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Incident Registry</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-medium">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Incident Type</th>
                        <th class="py-3 px-4 font-semibold">Shipment No.</th>
                        <th class="py-3 px-4 font-semibold">Severity</th>
                        <th class="py-3 px-4 font-semibold">Root Cause Summary</th>
                        <th class="py-3 px-4 font-semibold">Detected At</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($exceptions as $exc)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-slate-900">
                            {{ str_replace('_', ' ', $exc->exception_type) }}
                        </td>
                        <td class="py-3.5 px-4 font-mono font-medium text-blue-600">
                            <a href="{{ route('shipments.show', $exc->shipment_id) }}" class="hover:underline">
                                {{ $exc->shipment->shipment_number }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $exc->severity === 'HIGH' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $exc->severity }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 max-w-xs truncate text-slate-600">
                            {{ $exc->root_cause }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-500">
                            {{ $exc->detected_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($exc->status === 'RESOLVED')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">RESOLVED</span>
                            @elseif($exc->status === 'INVESTIGATING')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">INVESTIGATING</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">OPEN</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('shipments.show', $exc->shipment_id) }}" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">
                                Resolve →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 font-mono">
                            No exception incidents recorded.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $exceptions->links() }}
        </div>
    </div>

</div>
@endsection
