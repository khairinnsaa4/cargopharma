@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER & MODA TABS -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Armada Mitra Logistik</h1>
            <p class="text-xs text-slate-500">Mitra transportasi rantai dingin multimoda (Udara, Laut, dan Darat)</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Filter Tabs: ALL, AIR, SEA, LAND -->
            <div class="inline-flex items-center rounded-lg bg-slate-200/70 p-0.5 text-xs font-semibold">
                <button type="button" onclick="filterCarriers('ALL')" id="carrier-tab-ALL" class="carrier-tab-btn px-2.5 py-1 rounded-md bg-teal-600 text-white shadow-xs transition-all cursor-pointer">
                    Semua ({{ count($carriers) }})
                </button>
                <button type="button" onclick="filterCarriers('air')" id="carrier-tab-air" class="carrier-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all cursor-pointer">
                    ✈️ Udara ({{ $carriers->where('transport_type', 'air')->count() }})
                </button>
                <button type="button" onclick="filterCarriers('sea')" id="carrier-tab-sea" class="carrier-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all cursor-pointer">
                    🚢 Laut ({{ $carriers->where('transport_type', 'sea')->count() }})
                </button>
                <button type="button" onclick="filterCarriers('land')" id="carrier-tab-land" class="carrier-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all cursor-pointer">
                    🚚 Darat ({{ $carriers->where('transport_type', 'land')->count() }})
                </button>
            </div>

            @if(Auth::user()->isAdmin())
            <button onclick="document.getElementById('modal-add-carrier').classList.remove('hidden')" class="px-3.5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-xs">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah
            </button>
            @endif
        </div>
    </div>

    <!-- CARRIER GRID / CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($carriers as $carrier)
        @php
            $mode = $carrier->transport_type;
            $modeBadgeBg = $mode === 'air' ? 'bg-sky-50 text-sky-800 border-sky-200' : ($mode === 'sea' ? 'bg-teal-50 text-teal-800 border-teal-200' : 'bg-amber-50 text-amber-800 border-amber-200');
            $modeLabel = $mode === 'air' ? '✈️ Udara' : ($mode === 'sea' ? '🚢 Laut' : '🚚 Darat');
            $iconBg = $mode === 'air' ? 'bg-sky-50 text-sky-700 border-sky-100' : ($mode === 'sea' ? 'bg-teal-50 text-teal-700 border-teal-100' : 'bg-amber-50 text-amber-700 border-amber-100');
        @endphp
        <div class="carrier-card p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-3 transition-all" data-mode="{{ $mode }}">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg {{ $iconBg }} flex items-center justify-center shrink-0 border">
                        <i data-lucide="{{ $mode === 'air' ? 'plane' : ($mode === 'sea' ? 'ship' : 'truck') }}" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-snug">{{ $carrier->name }}</h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[10px] font-mono text-slate-500 uppercase font-bold">
                                {{ $carrier->code }}
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold border {{ $modeBadgeBg }}">
                                {{ $modeLabel }}
                            </span>
                        </div>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    AKTIF
                </span>
            </div>

            <div class="grid grid-cols-3 gap-2 pt-2.5 border-t border-slate-100 text-center text-xs">
                <div>
                    <span class="text-slate-400 text-[10px] block">Total</span>
                    <span class="font-bold text-slate-800">{{ $carrier->shipments_count ?? 0 }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] block">Aktif</span>
                    <span class="font-bold text-teal-700">{{ $carrier->active_shipments_count ?? 0 }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] block">Tertunda</span>
                    <span class="font-bold {{ ($carrier->delayed_shipments_count ?? 0) > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                        {{ $carrier->delayed_shipments_count ?? 0 }}
                    </span>
                </div>
            </div>

            <div class="text-[11px] text-slate-500 pt-2 border-t border-slate-100 flex items-center justify-between">
                <span class="truncate">{{ $carrier->contact_email ?? '-' }}</span>
                @if(Auth::user()->isAdmin())
                <button onclick="openEditCarrierModal({{ json_encode($carrier) }})" class="text-xs text-teal-700 font-semibold hover:text-teal-900 flex items-center gap-1">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit
                </button>
                @endif
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
            <h3 class="text-sm font-bold text-slate-900">Tambah Mitra Armada Baru</h3>
            <button onclick="document.getElementById('modal-add-carrier').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('carriers.store') }}" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-medium text-slate-700 mb-1">Kode Kurir *</label>
                <input type="text" name="code" required placeholder="Contoh: SQ-CARGO / GIA-CARGO" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Nama Mitra Kurir *</label>
                <input type="text" name="name" required placeholder="Contoh: Garuda Indonesia Cargo" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Moda Transportasi *</label>
                <select name="transport_type" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                    <option value="air">Jalur Udara (Air Freight)</option>
                    <option value="land">Jalur Darat (Ground Transport)</option>
                    <option value="sea">Jalur Laut (Maritime Cargo)</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Email Kontak</label>
                <input type="email" name="contact_email" placeholder="ops@airline.com" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Nomor Telepon</label>
                <input type="text" name="contact_phone" placeholder="+62 21 550 1234" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-add-carrier').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold shadow-xs">Simpan Mitra Kurir</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT CARRIER -->
<div id="modal-edit-carrier" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md bg-white rounded-xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Edit Mitra Armada Kurir</h3>
            <button onclick="document.getElementById('modal-edit-carrier').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="edit-carrier-form" method="POST" action="" class="space-y-3 text-xs">
            @csrf
            @method('PATCH')
            <div>
                <label class="block font-medium text-slate-700 mb-1">Kode Kurir</label>
                <input type="text" id="edit-carrier-code" disabled class="w-full p-2 rounded-lg border border-slate-200 bg-slate-100 text-slate-500 font-mono">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Nama Mitra Kurir *</label>
                <input type="text" name="name" id="edit-carrier-name" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Moda Transportasi *</label>
                <select name="transport_type" id="edit-carrier-transport" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                    <option value="air">Jalur Udara (Air Freight)</option>
                    <option value="land">Jalur Darat (Ground Transport)</option>
                    <option value="sea">Jalur Laut (Maritime Cargo)</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Email Kontak</label>
                <input type="email" name="contact_email" id="edit-carrier-email" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Nomor Telepon</label>
                <input type="text" name="contact_phone" id="edit-carrier-phone" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="edit-carrier-active" value="1" class="rounded border-slate-300 text-teal-600">
                <label for="edit-carrier-active" class="font-medium text-slate-700">Mitra Kurir Aktif (Menerima Pengiriman Baru)</label>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-edit-carrier').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold shadow-xs">Perbarui Mitra Kurir</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterCarriers(mode) {
    document.querySelectorAll('.carrier-tab-btn').forEach(btn => {
        btn.classList.remove('bg-teal-600', 'text-white', 'shadow-xs');
        btn.classList.add('text-slate-700');
    });
    const active = document.getElementById('carrier-tab-' + mode);
    if (active) {
        active.classList.remove('text-slate-700');
        active.classList.add('bg-teal-600', 'text-white', 'shadow-xs');
    }

    document.querySelectorAll('.carrier-card').forEach(card => {
        const cardMode = card.getAttribute('data-mode');
        if (mode === 'ALL' || cardMode === mode) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function openEditCarrierModal(carrier) {
    const form = document.getElementById('edit-carrier-form');
    form.action = `/carriers/${carrier.id}`;
    document.getElementById('edit-carrier-code').value = carrier.code;
    document.getElementById('edit-carrier-name').value = carrier.name;
    document.getElementById('edit-carrier-transport').value = carrier.transport_type;
    document.getElementById('edit-carrier-email').value = carrier.contact_email || '';
    document.getElementById('edit-carrier-phone').value = carrier.contact_phone || '';
    document.getElementById('edit-carrier-active').checked = Boolean(carrier.is_active);
    document.getElementById('modal-edit-carrier').classList.remove('hidden');
}
</script>
@endif
@endsection
