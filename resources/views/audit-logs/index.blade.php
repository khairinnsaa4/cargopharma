@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1.5 font-mono text-xs text-slate-400">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">SECURITY & GOVERNANCE</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">System Audit Trail</h1>
            <p class="text-xs text-slate-400">Immutable ledger of state transitions, sensor adjustments, and incident resolutions.</p>
        </div>
        <div class="text-right text-xs font-mono text-slate-400">
            <span>Admin Clearance Level 1</span>
        </div>
    </div>

    <!-- LOGS TABLE -->
    <div class="rounded-2xl bg-slate-900/90 border border-slate-800 backdrop-blur-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-950/80 border-b border-slate-800 text-slate-400 text-[10px] uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">Actor</th>
                        <th class="py-3 px-4">Action Event</th>
                        <th class="py-3 px-4">Entity Type</th>
                        <th class="py-3 px-4">Entity ID</th>
                        <th class="py-3 px-4">Client IP</th>
                        <th class="py-3 px-4">Audit Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-800/40 transition-colors text-xs font-mono">
                        <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="py-3.5 px-4 text-white font-semibold">
                            {{ $log->user->name ?? 'System Automated' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-300">
                            {{ $log->entity_type }}
                        </td>
                        <td class="py-3.5 px-4 text-cyan-400 font-bold">
                            #{{ $log->entity_id }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                        <td class="py-3.5 px-4 max-w-xs truncate text-slate-400 text-[10px]">
                            @if($log->new_values)
                                {{ json_encode($log->new_values) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 font-mono text-xs">
                            No audit trail entries recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
