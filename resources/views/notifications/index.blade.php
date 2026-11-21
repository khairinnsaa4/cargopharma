@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Notifikasi</h1>

        @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.markAllRead') }}">
            @csrf
            <button type="submit" class="px-3 py-1.5 rounded-lg border border-teal-300 text-teal-800 bg-teal-50 hover:bg-teal-100 font-semibold text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                <i data-lucide="check-check" class="w-4 h-4"></i> Tandai Dibaca ({{ $unreadCount }})
            </button>
        </form>
        @endif
    </div>

    <!-- TABS -->
    <div class="flex items-center gap-2 border-b border-slate-200 text-xs">
        <a href="{{ route('notifications.index') }}" class="px-4 py-2 font-semibold border-b-2 transition-colors {{ !request('filter') ? 'border-teal-600 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Semua
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="px-4 py-2 font-semibold border-b-2 transition-colors flex items-center gap-1.5 {{ request('filter') === 'unread' ? 'border-teal-600 text-teal-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <span>Belum Dibaca</span>
            @if($unreadCount > 0)
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-rose-500 text-white font-bold">{{ $unreadCount }}</span>
            @endif
        </a>
    </div>

    <!-- NOTIFICATION LIST -->
    <div class="space-y-3">
        @forelse($notifications as $item)
        <div class="p-4 rounded-xl bg-white border transition-all {{ $item->is_read ? 'border-slate-200/80 bg-white opacity-85' : 'border-teal-200 bg-teal-50/20 shadow-xs ring-1 ring-teal-500/10' }}">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 
                        @if($item->type === 'danger') bg-rose-100 text-rose-700
                        @elseif($item->type === 'warning') bg-amber-100 text-amber-700
                        @else bg-teal-100 text-teal-700 @endif">
                        <i data-lucide="{{ $item->type === 'danger' ? 'alert-octagon' : ($item->type === 'warning' ? 'alert-triangle' : 'info') }}" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs font-bold text-slate-900">{{ $item->title }}</h3>
                            @if(!$item->is_read)
                            <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $item->message }}</p>

                        <div class="flex items-center gap-3 mt-2 text-[11px] text-slate-400 font-mono">
                            <span><i data-lucide="clock" class="w-3 h-3 inline"></i> {{ $item->created_at->diffForHumans() }}</span>
                            @if($item->shipment)
                            <span>•</span>
                            <a href="{{ route('shipments.show', $item->shipment_id) }}" class="text-teal-700 hover:text-teal-900 font-semibold underline">
                                Pengiriman {{ $item->shipment->shipment_number }}
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                @if(!$item->is_read)
                <form method="POST" action="{{ route('notifications.read', $item->id) }}" class="shrink-0">
                    @csrf
                    @method('PATCH')
                    <button type="submit" title="Tandai sudah dibaca" class="p-1.5 rounded-lg text-slate-400 hover:text-teal-700 hover:bg-slate-100 transition-colors">
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="p-12 text-center rounded-xl bg-white border border-slate-200/80 text-slate-400">
            <i data-lucide="bell-off" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
            <p class="text-xs font-medium">Tidak ada notifikasi saat ini.</p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="pt-2">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
@endsection
