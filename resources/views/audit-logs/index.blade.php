@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="pb-3 border-b border-slate-200 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">System Audit Trail</h1>
            <p class="text-xs text-slate-500">Immutable governance ledger tracking state transitions, user activities, and data updates.</p>
        </div>
        <span class="text-xs font-mono text-slate-500 bg-slate-100 px-3 py-1 rounded-lg border border-slate-200">
            Admin Clearance Only
        </span>
    </div>

    <!-- AUDIT TABLE -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-medium">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Timestamp</th>
                        <th class="py-3 px-4 font-semibold">Actor</th>
                        <th class="py-3 px-4 font-semibold">Action Event</th>
                        <th class="py-3 px-4 font-semibold">Entity Type</th>
                        <th class="py-3 px-4 font-semibold">Entity ID</th>
                        <th class="py-3 px-4 font-semibold">Client IP</th>
                        <th class="py-3 px-4 font-semibold">Audit Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/80 transition-colors font-mono">
                        <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="py-3.5 px-4 font-sans font-medium text-slate-900">
                            {{ $log->user->name ?? 'System Automated' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 font-sans">
                            {{ $log->entity_type }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            #{{ $log->entity_id }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                        <td class="py-3.5 px-4 max-w-xs truncate text-slate-500 text-[10px]">
                            {{ json_encode($log->new_values) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 font-mono">
                            No audit trail entries recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
