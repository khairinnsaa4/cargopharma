@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Carriers & Logistics Partners</h1>
            <p class="text-xs text-slate-500">Authorized air and ground cargo operators for international distribution.</p>
        </div>

        @if(Auth::user()->isAdmin())
        <button onclick="document.getElementById('modal-add-carrier').classList.remove('hidden')" class="px-3.5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-xs">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Carrier
        </button>
        @endif
    </div>

    <!-- CARRIER GRID / CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($carriers as $carrier)
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-4">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0 border border-teal-100">
                        <i data-lucide="{{ $carrier->transport_type === 'air' ? 'plane' : ($carrier->transport_type === 'sea' ? 'ship' : 'truck') }}" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">{{ $carrier->name }}</h3>
                        <span class="text-[10px] font-mono text-slate-400 uppercase">{{ $carrier->code }} • {{ strtoupper($carrier->transport_type) }} FREIGHT</span>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    ACTIVE
                </span>
            </div>

            <div class="grid grid-cols-3 gap-2 pt-3 border-t border-slate-100 text-center text-xs">
                <div>
                    <span class="text-slate-400 text-[10px] block">Shipments</span>
                    <span class="font-bold text-slate-800">{{ $carrier->shipments_count ?? 0 }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] block">In Transit</span>
                    <span class="font-bold text-teal-700">{{ $carrier->active_shipments_count ?? 0 }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] block">Delayed</span>
                    <span class="font-bold {{ ($carrier->delayed_shipments_count ?? 0) > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                        {{ $carrier->delayed_shipments_count ?? 0 }}
                    </span>
                </div>
            </div>

            <div class="text-[11px] text-slate-500 pt-2 border-t border-slate-100 flex items-center justify-between">
                <span class="truncate">Contact: {{ $carrier->contact_email ?? 'operations@partner.com' }}</span>
            </div>
        </div>
        @endforeach
    </div>

</div>

<!-- MODAL ADD CARRIER -->
@if(Auth::user()->isAdmin())
<div id="modal-add-carrier" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md bg-white rounded-xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Add New Carrier Partner</h3>
            <button onclick="document.getElementById('modal-add-carrier').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('carriers.store') }}" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-medium text-slate-700 mb-1">Carrier Code *</label>
                <input type="text" name="code" required placeholder="e.g. SQ-CARGO" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Carrier Name *</label>
                <input type="text" name="name" required placeholder="e.g. Singapore Airlines Cargo" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Freight Type *</label>
                <select name="transport_type" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                    <option value="air">Air Freight</option>
                    <option value="land">Ground Transport</option>
                    <option value="sea">Maritime Cargo</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Contact Email</label>
                <input type="email" name="contact_email" placeholder="ops@airline.com" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-add-carrier').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold shadow-xs">Save Carrier</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
