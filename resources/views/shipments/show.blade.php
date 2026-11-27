@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- BREADCRUMB & HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 mb-1 text-xs text-slate-500 font-medium">
                <a href="{{ route('shipments.index') }}" class="hover:text-teal-700">Daftar Pengiriman</a>
                <span>/</span>
                <span class="text-slate-800 font-mono font-bold">{{ $shipment->shipment_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-slate-900 font-mono">{{ $shipment->shipment_number }}</h1>
                @if($shipment->is_delayed)
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-rose-600 text-white shadow-xs">
                        TERTUNDA (+{{ $shipment->delay_duration_hours }} Jam)
                    </span>
                @elseif($shipment->current_status === 'DELIVERED')
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-600 text-white shadow-xs">
                        TERKIRIM
                    </span>
                @elseif($shipment->current_status === 'IN_TRANSIT')
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-teal-600 text-white shadow-xs">
                        DALAM PERJALANAN
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-medium bg-slate-200 text-slate-700">
                        {{ $shipment->current_status }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">Penerima: <span class="font-semibold text-slate-700">{{ $shipment->customer->organization ?? $shipment->customer->name }}</span> • Kurir: <span class="text-slate-700 font-medium">{{ $shipment->carrier->name ?? 'Armada Standar' }}</span></p>
        </div>

        <!-- OPERATOR / ADMIN ACTION TOOLBAR -->
        @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Advance Status Button -->
            @if($shipment->current_status === 'CREATED')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="BOOKED">
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-medium text-xs flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i> Dipesan
                </button>
            </form>
            @elseif($shipment->current_status === 'BOOKED')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="PICKED_UP">
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-medium text-xs flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="truck" class="w-3.5 h-3.5"></i> Dijemput
                </button>
            </form>
            @elseif($shipment->current_status === 'PICKED_UP')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="IN_TRANSIT">
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-medium text-xs flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="plane" class="w-3.5 h-3.5"></i> Dalam Perjalanan
                </button>
            </form>
            @elseif($shipment->current_status === 'IN_TRANSIT')
            <form method="POST" action="{{ route('shipments.status.update', $shipment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="new_status" value="DELIVERED">
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs flex items-center gap-1.5 shadow-xs">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Terkirim
                </button>
            </form>
            @endif

            @if($shipment->current_status !== 'DELIVERED')
            <button onclick="document.getElementById('modal-checkpoint').classList.remove('hidden')" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-medium text-xs flex items-center gap-1.5">
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-teal-600"></i> Transit
            </button>

            <button onclick="document.getElementById('modal-exception').classList.remove('hidden'); updateCauseOptions();" class="px-3 py-1.5 rounded-lg border border-rose-300 bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium text-xs flex items-center gap-1.5">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-rose-600"></i> Insiden
            </button>
            @endif
        </div>
        @endif
    </div>

    <!-- 4 CORPORATE TELEMETRY TILES -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Cold-Chain Sensor Status -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                <span>Telemetri Suhu Inti</span>
                <span class="text-emerald-600 font-semibold flex items-center gap-1 text-[11px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sensor Normal
                </span>
            </div>
            @php
                $currentTemp = $latestEvent?->container_temperature ?? $shipment->target_temp_min;
            @endphp
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold font-mono text-slate-900">{{ number_format($currentTemp, 1) }}°C</span>
                <span class="text-xs text-slate-500">{{ $shipment->target_temp_min }}°C s/d {{ $shipment->target_temp_max }}°C</span>
            </div>
            <div class="mt-2 text-xs text-slate-600 truncate">
                Unit Pendingin: <span class="font-medium text-slate-800">{{ $shipment->items->first()->container_type ?? 'Envirotainer RKN e1' }}</span>
            </div>
        </div>

        <!-- 2. Atmospheric Weather at Hub -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                <span class="flex items-center gap-1 text-slate-700 font-medium">
                    <i data-lucide="cloud-sun" class="w-3.5 h-3.5 text-teal-600"></i> Cuaca Terkini Hub
                </span>
                <span class="text-[11px] text-slate-400">Atmosfer</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold font-mono text-slate-900">{{ number_format($weatherData['temp'], 1) }}°C</span>
                <span class="text-xs text-slate-600 truncate">{{ $weatherData['condition'] }} • {{ $weatherData['city'] }}</span>
            </div>
            <div class="mt-2 text-xs text-slate-600 flex items-center justify-between">
                <span>Kelembapan: {{ $weatherData['humidity'] }}%</span>
                @if($weatherData['is_extreme_heat'])
                <span class="text-amber-700 font-semibold">Peringatan Suhu Panas</span>
                @else
                <span class="text-emerald-600 font-medium">Kondisi Optimal</span>
                @endif
            </div>
        </div>

        <!-- 3. Schedule & Routing ETA -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                <span>Jadwal Pengiriman</span>
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
            </div>
            <div class="text-base font-bold text-slate-900 font-mono">
                ETA: {{ $shipment->expected_arrival->format('d M Y') }}
            </div>
            <div class="mt-2 text-xs text-slate-600">
                Keberangkatan: <span class="font-mono text-slate-800">{{ $shipment->expected_departure->format('d M, H:i') }}</span>
            </div>
        </div>

        <!-- 4. Declared Valuation (Live ECB FX Rates) -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                <span class="flex items-center gap-1 text-slate-700 font-medium">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i> Nilai Kargo &amp; Asuransi
                </span>
                <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-100 text-slate-600">Kurs ECB</span>
            </div>
            <div class="text-xl font-bold font-mono text-slate-900">
                ${{ number_format($declaredUsd, 0) }} <span class="text-xs font-sans text-slate-500 font-normal">USD</span>
            </div>
            <div class="mt-2 text-xs text-slate-600 flex items-center justify-between font-mono">
                <span class="text-emerald-700 font-semibold">Rp {{ number_format($declaredIdr / 1000000000, 2) }} Milyar</span>
                <span class="text-slate-400">€{{ number_format($declaredEur, 0) }}</span>
            </div>
        </div>
    </div>

    <!-- MAIN GRID: MAP & TIMELINE -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT 2 COLS: MAP & PRODUCT SPECIFICATION -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Leaflet Map Card -->
            <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                            <h3 class="text-sm font-bold text-slate-900">Peta Rute Koridor</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono bg-teal-50 text-teal-800 border border-teal-200">
                                {{ $shipment->carrier->transport_type === 'land' ? '🚚 Jalur Darat' : ($shipment->carrier->transport_type === 'sea' ? '🚢 Jalur Laut' : '✈️ Jalur Udara') }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5 font-mono">
                            {{ $shipment->origin->name }} ({{ $shipment->origin->city }}, {{ $shipment->origin->country }}) 
                            &rarr; 
                            {{ $shipment->destination->name }} ({{ $shipment->destination->city }}, {{ $shipment->destination->country }})
                        </p>
                    </div>

                    <div class="flex items-center gap-3 text-[11px] text-slate-500">
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-teal-800"></span>
                            @if($shipment->carrier->transport_type === 'sea') ⚓ Pelabuhan Asal @elseif($shipment->carrier->transport_type === 'land') 🏭 Gudang Asal @else 🛫 Asal Bandara @endif
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-teal-500"></span> 🔄 Titik Transit
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            @if($shipment->carrier->transport_type === 'sea') ⚓ Pelabuhan Tujuan @elseif($shipment->carrier->transport_type === 'land') 🏥 Fasilitas Tujuan @else 🛬 Tujuan Bandara @endif
                        </span>
                        <button type="button" onclick="window.recenterShipmentMap && window.recenterShipmentMap()" class="px-2 py-1 text-[10px] font-bold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 transition-colors ml-1">
                            🎯 Pusatkan
                        </button>
                    </div>
                </div>

                <div class="relative w-full h-[420px] overflow-hidden bg-slate-100">
                    <div id="shipment-map" class="w-full h-full z-0"></div>

                    <!-- Floating Pan & Navigation D-Pad Controls -->
                    <div class="absolute top-3 left-14 z-[400] flex items-center gap-1 bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-lg p-1 shadow-md">
                        <span class="text-[10px] font-bold text-slate-500 uppercase px-1.5 hidden sm:inline">Geser:</span>
                        <button type="button" onclick="panShipmentMap(0, -150)" title="Geser ke Atas"
                            class="w-7 h-7 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                            <i data-lucide="arrow-up" class="w-3.5 h-3.5"></i>
                        </button>
                        <button type="button" onclick="panShipmentMap(0, 150)" title="Geser ke Bawah"
                            class="w-7 h-7 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                            <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
                        </button>
                        <button type="button" onclick="panShipmentMap(-150, 0)" title="Geser ke Kiri"
                            class="w-7 h-7 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        </button>
                        <button type="button" onclick="panShipmentMap(150, 0)" title="Geser ke Kanan"
                            class="w-7 h-7 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Manifest Specifications -->
            <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-3">
                <h3 class="text-sm font-bold text-slate-900">Rincian Manifes Farmasi</h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[11px] block">Produk</span>
                        <span class="font-semibold text-slate-800 block truncate">{{ $shipment->items->first()->product_name ?? $shipment->cargo_type }}</span>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[11px] block">Nomor Batch / Lot</span>
                        <span class="font-mono font-semibold text-teal-700 block">{{ $shipment->items->first()->batch_number ?? 'BF-LOT-001' }}</span>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[11px] block">Jumlah Vial</span>
                        <span class="font-semibold text-slate-800 block">{{ number_format($shipment->items->first()->quantity_vials ?? 0) }}</span>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[11px] block">Berat Kotor</span>
                        <span class="font-semibold text-slate-800 block">{{ number_format($shipment->total_weight_kg) }} kg</span>
                    </div>
                </div>
            </div>

            <!-- Exception Management Panel -->
            @if($shipment->exceptions->count() > 0)
            <div class="p-5 rounded-xl bg-white border border-rose-200 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-rose-800 flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i> Insiden &amp; Kendala Aktif
                    </h3>
                    <span class="text-xs text-rose-600 font-semibold">{{ $shipment->exceptions->count() }} Tercatat</span>
                </div>

                <div class="space-y-3">
                    @foreach($shipment->exceptions as $exc)
                    <div class="p-3.5 rounded-lg bg-rose-50/50 border border-rose-100 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">{{ str_replace('_', ' ', $exc->exception_type) }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $exc->status === 'RESOLVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $exc->status }}
                            </span>
                        </div>
                        <p class="text-slate-700">{{ $exc->root_cause }}</p>
                        @if($exc->resolution_notes)
                        <div class="p-2.5 rounded bg-white border border-slate-200 text-slate-600 text-[11px]">
                            <span class="font-semibold text-emerald-700">Tindakan Penyelesaian:</span> {{ $exc->resolution_notes }}
                        </div>
                        @endif

                        @if($exc->status !== 'RESOLVED' && (Auth::user()->isAdmin() || Auth::user()->isOperator()))
                        <form method="POST" action="{{ route('exceptions.resolve', $exc->id) }}" class="pt-3 border-t border-rose-200/60 space-y-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="RESOLVED">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                    Pilih Opsi Tindakan Penyelesaian Standar (SOP Farmasi):
                                </label>
                                <select onchange="applyResolutionOption(this, 'res-input-{{ $exc->id }}')" 
                                    class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white text-xs text-slate-700 focus:outline-none focus:border-teal-600">
                                    <option value="">-- Pilih Rekomendasi Solusi Layak (Klik untuk Mengisi Otomatis) --</option>
                                    <option value="Penggantian & penambahan es kering (dry-ice recharge) serta pemindahan kargo ke cold room 2-8°C.">
                                        ❄️ Penambahan Dry-Ice &amp; Pemindahan ke Cold Room 2-8°C
                                    </option>
                                    <option value="Verifikasi CoA dan dokumen izin rilis selesai oleh Bea Cukai; kargo resmi dilepaskan (cleared) ke jalur hijau.">
                                        📄 Bea Cukai Selesai &amp; Izin Rilis Jalur Hijau Diterbitkan
                                    </option>
                                    <option value="Jadwal penerbangan kargo lanjutan berhasil dipesan ulang (re-booked) dengan prioritas jalur dingin berpendingin.">
                                        ✈️ Jadwal Penerbangan Lanjutan Berhasil Dipesan Ulang (Re-booked)
                                    </option>
                                    <option value="Inspeksi fisik integritas botol vial selesai oleh apoteker penanggung jawab; mutu vaksin dipastikan aman.">
                                        🔬 Inspeksi Fisik Vial Selesai, Integritas Mutu Vaksin Aman
                                    </option>
                                    <option value="Sensor data logger cadangan diaktifkan; suhu kontainer telah kembali normal dan stabil di rentang target.">
                                        🌡️ Sensor Cadangan Aktif, Suhu Kembali Stabil di Rentang Target
                                    </option>
                                    <option value="Kargo dialihkan ke armada darat berpendingin aktif berlisensi GDP untuk mengejar target ketibaan.">
                                        🚚 Pengalihan ke Armada Darat Berpendingin Aktif Berlisensi GDP
                                    </option>
                                    <option value="MANUAL">✏️ Tindakan Kustom (Ketik Manual Sendiri)...</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="text" id="res-input-{{ $exc->id }}" name="resolution_notes" required 
                                    placeholder="Tindakan penyelesaian (otomatis terisi dari pilihan di atas atau ketik manual)..." 
                                    class="flex-1 px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-xs focus:outline-none focus:border-teal-600">
                                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs shrink-0 flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    <span>Selesaikan Insiden</span>
                                </button>
                            </div>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- RIGHT 1 COL: CHRONOLOGICAL TIMELINE -->
        <div class="space-y-4">
            <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Riwayat Titik Transit</h3>
                    <span class="text-xs text-slate-500 font-mono">{{ $shipment->trackingEvents->count() }} Peristiwa</span>
                </div>

                <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse($shipment->trackingEvents as $event)
                    <div class="relative">
                        <div class="absolute -left-[27px] top-1 w-3 h-3 rounded-full bg-white border-2 {{ $event->event_type === 'EXCEPTION_OCCURRED' ? 'border-rose-600' : ($event->event_type === 'DELIVERED' ? 'border-emerald-600' : 'border-teal-600') }}"></div>
                        <div class="text-xs">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="font-bold text-slate-900">{{ $event->event_type }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $event->event_time->format('d M, H:i') }}</span>
                            </div>
                            <div class="font-medium text-teal-700 text-xs mb-1">
                                {{ $event->status_snapshot }}
                            </div>
                            <p class="text-slate-600 text-xs leading-relaxed">
                                {{ $event->description }}
                            </p>
                            <div class="flex items-center gap-2 mt-1.5 text-[10px] font-mono text-slate-500">
                                @if($event->container_temperature !== null)
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200">
                                    Suhu Inti: {{ $event->container_temperature }}°C
                                </span>
                                @endif
                                @if($event->ambient_temperature !== null)
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200">
                                    Suhu Lingkungan: {{ $event->ambient_temperature }}°C
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-slate-400 text-xs py-4 text-center">
                        Belum ada catatan titik transit.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>

<!-- MODAL CHECKPOINT -->
<div id="modal-checkpoint" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg bg-white rounded-xl p-6 shadow-xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Catat Telemetri Titik Transit</h3>
                <p class="text-[11px] text-slate-500">Tambahkan titik transit baru ke garis waktu dengan data telemetri valid</p>
            </div>
            <button onclick="document.getElementById('modal-checkpoint').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- LIVE OPENSTREETMAP GEOCODING SEARCH -->
        <div class="p-3.5 rounded-xl bg-gradient-to-r from-teal-50/90 to-emerald-50/50 border border-teal-200/90 space-y-2.5 text-xs">
            <div class="flex items-center justify-between">
                <label class="font-bold text-teal-950 flex items-center gap-1.5">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-teal-600"></i> Pencarian Lokasi Live (OpenStreetMap)
                </label>
                <span class="text-[10px] font-mono font-semibold text-teal-700 bg-teal-100/70 px-1.5 py-0.5 rounded">Nominatim API</span>
            </div>
            
            <div class="flex gap-2">
                <input type="text" id="geocode-search-input" placeholder="Cari bandara, pelabuhan, atau hub (contoh: Soekarno Hatta, Dubai DXB, Changi)..." 
                    class="flex-1 p-2 rounded-lg border border-slate-200 bg-white text-slate-800 text-xs focus:outline-none focus:border-teal-600 shadow-2xs">
                <button type="button" id="btn-geocode-search" class="px-3.5 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg shrink-0 flex items-center gap-1 shadow-2xs cursor-pointer">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i> Cari
                </button>
            </div>

            <!-- OPSI CEPAT PILIHAN LOKASI POPULER (ONE-CLICK LOCATION PRESET) -->
            <div class="space-y-1.5 pt-1">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-teal-900 uppercase tracking-wider flex items-center gap-1">
                        <i data-lucide="sparkles" class="w-3 h-3 text-teal-600"></i> Opsi Cepat Lokasi Populer:
                    </span>
                    <span class="text-[9px] text-teal-700">Klik opsi untuk langsung menerapkan GPS</span>
                </div>
                
                <div class="flex items-center gap-1.5 flex-wrap">
                    <!-- Bandara Utama Internasional -->
                    <button type="button" onclick="selectQuickLocation('Bandara Soekarno-Hatta (CGK)', -6.1256, 106.6559)" class="px-2 py-1 rounded-md bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-teal-200/90 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>🛫 CGK Jakarta</span>
                    </button>
                    <button type="button" onclick="selectQuickLocation('Bandara Internasional Dubai (DXB)', 25.2532, 55.3657)" class="px-2 py-1 rounded-md bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-teal-200/90 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>🛫 DXB Dubai</span>
                    </button>
                    <button type="button" onclick="selectQuickLocation('Bandara Changi (SIN)', 1.3644, 103.9915)" class="px-2 py-1 rounded-md bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-teal-200/90 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>🛫 SIN Singapura</span>
                    </button>
                    <button type="button" onclick="selectQuickLocation('Bandara Frankfurt Main (FRA)', 50.0379, 8.5622)" class="px-2 py-1 rounded-md bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-teal-200/90 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>🛫 FRA Frankfurt</span>
                    </button>
                    <button type="button" onclick="selectQuickLocation('Bandara Kopenhagen UNICEF Hub (CPH)', 55.6180, 12.6508)" class="px-2 py-1 rounded-md bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-teal-200/90 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>🛫 CPH Kopenhagen</span>
                    </button>
                    <button type="button" onclick="selectQuickLocation('Bandara Tokyo Narita (NRT)', 35.7720, 140.3929)" class="px-2 py-1 rounded-md bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-teal-200/90 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>🛫 NRT Tokyo</span>
                    </button>
                    <button type="button" onclick="selectQuickLocation('Bandara Hamad Doha (DOH)', 25.2731, 51.6081)" class="px-2 py-1 rounded-md bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-teal-200/90 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>🛫 DOH Doha</span>
                    </button>
                    <button type="button" onclick="selectQuickLocation('Bandara Schiphol Amsterdam (AMS)', 52.3105, 4.7683)" class="px-2 py-1 rounded-md bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-teal-200/90 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>🛫 AMS Amsterdam</span>
                    </button>

                    <!-- Pelabuhan & Fasilitas Farmasi -->
                    <button type="button" onclick="selectQuickLocation('Pelabuhan Tanjung Priok (Jakarta)', -6.1017, 106.8837)" class="px-2 py-1 rounded-md bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-teal-200/90 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>⚓ Tanjung Priok</span>
                    </button>
                    <button type="button" onclick="selectQuickLocation('Pelabuhan Port of Singapore', 1.2644, 103.8222)" class="px-2 py-1 rounded-md bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-teal-200/90 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>⚓ Port of Singapore</span>
                    </button>
                    <button type="button" onclick="selectQuickLocation('Pabrik Utama Bio Farma (Bandung)', -6.8996, 107.5997)" class="px-2 py-1 rounded-md bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-teal-200/90 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>🏭 Bio Farma Pasteur</span>
                    </button>
                    <button type="button" onclick="selectQuickLocation('RSUP Dr. Hasan Sadikin (Bandung)', -6.8943, 107.5985)" class="px-2 py-1 rounded-md bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-teal-200/90 shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>🏥 RS Hasan Sadikin</span>
                    </button>
                </div>
            </div>

            <div id="geocode-results" class="hidden space-y-1 max-h-36 overflow-y-auto border border-teal-200 rounded-lg p-1.5 bg-white text-xs"></div>
            <div id="geocode-live-feedback" class="hidden text-[11px] text-emerald-700 font-medium flex items-center gap-1 bg-emerald-50 p-2 rounded-lg border border-emerald-200">
                <i data-lucide="check-circle" class="w-3.5 h-3.5 shrink-0"></i> <span id="geocode-feedback-text"></span>
            </div>
        </div>

        @php
            $idealMidTemp = round(($shipment->target_temp_min + $shipment->target_temp_max) / 2, 1);
        @endphp

        <!-- OPSI CEPAT SKENARIO TITIK TRANSIT -->
        <div class="p-3 rounded-lg bg-teal-50/70 border border-teal-200 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-teal-900 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="zap" class="w-3.5 h-3.5 text-teal-600"></i> Opsi Cepat Skenario Checkpoint
                </span>
                <span class="text-[10px] text-teal-700">Klik skenario untuk otomatis mengisi formulir</span>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                <button type="button" onclick="applyCheckpointPreset('takeoff')" class="p-2 rounded bg-white hover:bg-teal-600 hover:text-white text-left border border-teal-200 transition-all shadow-2xs group cursor-pointer">
                    <div class="font-bold text-[11px] text-slate-800 group-hover:text-white flex items-center gap-1">
                        <span>🛫 Lepas Landas</span>
                    </div>
                    <p class="text-[9px] text-slate-500 group-hover:text-teal-100 line-clamp-1">Berangkat menuju transit</p>
                </button>

                <button type="button" onclick="applyCheckpointPreset('hub_arrive')" class="p-2 rounded bg-white hover:bg-teal-600 hover:text-white text-left border border-teal-200 transition-all shadow-2xs group cursor-pointer">
                    <div class="font-bold text-[11px] text-slate-800 group-hover:text-white flex items-center gap-1">
                        <span>📦 Tiba di Hub Transit</span>
                    </div>
                    <p class="text-[9px] text-slate-500 group-hover:text-teal-100 line-clamp-1">Masuk terminal kargo transit</p>
                </button>

                <button type="button" onclick="applyCheckpointPreset('customs')" class="p-2 rounded bg-white hover:bg-teal-600 hover:text-white text-left border border-teal-200 transition-all shadow-2xs group cursor-pointer">
                    <div class="font-bold text-[11px] text-slate-800 group-hover:text-white flex items-center gap-1">
                        <span>🔍 Cukai & Karantina</span>
                    </div>
                    <p class="text-[9px] text-slate-500 group-hover:text-teal-100 line-clamp-1">Pemeriksaan pabean lolos</p>
                </button>

                <button type="button" onclick="applyCheckpointPreset('temp_check')" class="p-2 rounded bg-white hover:bg-teal-600 hover:text-white text-left border border-teal-200 transition-all shadow-2xs group cursor-pointer">
                    <div class="font-bold text-[11px] text-slate-800 group-hover:text-white flex items-center gap-1">
                        <span>❄️ Inspeksi Suhu IoT</span>
                    </div>
                    <p class="text-[9px] text-slate-500 group-hover:text-teal-100 line-clamp-1">Kalibrasi & log telemetri</p>
                </button>

                <button type="button" onclick="applyCheckpointPreset('delivered')" class="p-2 rounded bg-white hover:bg-emerald-600 hover:text-white text-left border border-emerald-300 transition-all shadow-2xs group cursor-pointer sm:col-span-2">
                    <div class="font-bold text-[11px] text-emerald-800 group-hover:text-white flex items-center gap-1">
                        <span>🏥 Tiba di Fasilitas Tujuan (Selesai)</span>
                    </div>
                    <p class="text-[9px] text-slate-500 group-hover:text-emerald-100 line-clamp-1">Serah terima instalasi farmasi penerima</p>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('tracking.store', $shipment->id) }}" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-medium text-slate-700 mb-1">Tipe Peristiwa *</label>
                <select name="event_type" id="checkpoint-event-type" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                    <option value="IN_TRANSIT">IN_TRANSIT (Dalam Perjalanan / Sedang Mengudara)</option>
                    <option value="TRANSIT_HUB">TRANSIT_HUB (Tiba di Bandara / Terminal Hub Transit)</option>
                    <option value="CUSTOMS_INSPECTION">CUSTOMS_INSPECTION (Pemeriksaan Bea Cukai / Rilis Lot)</option>
                    <option value="DELIVERED">DELIVERED (Tiba di Gudang Dingin Tujuan)</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-slate-700 mb-1">Pilih Master Lokasi (Opsional)</label>
                <select name="location_id" id="checkpoint-location-id" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                    <option value="">Otomatis / Koordinat Hasil Pencarian</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" data-lat="{{ $loc->latitude }}" data-lng="{{ $loc->longitude }}">{{ $loc->name }} ({{ $loc->city }})</option>
                    @endforeach
                </select>
            </div>

            <!-- GEODATA COORDINATES -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Garis Lintang (Latitude)</label>
                    <input type="text" name="recorded_lat" id="checkpoint-lat" placeholder="-6.8996" class="w-full p-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-800 font-mono">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Garis Bujur (Longitude)</label>
                    <input type="text" name="recorded_lng" id="checkpoint-lng" placeholder="107.5997" class="w-full p-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-800 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Suhu Kontainer (°C) *</label>
                    <input type="number" step="0.1" name="container_temperature" id="checkpoint-temp-input" value="{{ $idealMidTemp }}" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
                    <!-- OPSI CEPAT SUHU -->
                    <div class="flex items-center gap-1 flex-wrap mt-1">
                        <button type="button" onclick="setCheckpointTemp({{ $idealMidTemp }})" class="px-1.5 py-0.5 rounded bg-teal-100 hover:bg-teal-600 hover:text-white text-[9px] font-bold text-teal-800 cursor-pointer">Target ({{ $idealMidTemp }}°C)</button>
                        <button type="button" onclick="setCheckpointTemp({{ $shipment->target_temp_min }})" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-[9px] text-slate-700 cursor-pointer">Min ({{ $shipment->target_temp_min }}°C)</button>
                        <button type="button" onclick="setCheckpointTemp({{ $shipment->target_temp_max }})" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-[9px] text-slate-700 cursor-pointer">Max ({{ $shipment->target_temp_max }}°C)</button>
                    </div>
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Waktu Kejadian *</label>
                    <input type="datetime-local" name="event_time" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
                </div>
            </div>

            <div>
                <label class="block font-medium text-slate-700 mb-1">Ringkasan Status *</label>
                <input type="text" name="status_snapshot" id="checkpoint-headline" required placeholder="Contoh: Lolos pemeriksaan transit di Bandara Dubai DXB" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>

            <div>
                <label class="block font-medium text-slate-700 mb-1">Detail Observasi &amp; Catatan</label>
                <textarea name="description" rows="2" required placeholder="Level dry-ice normal, datalogger diperiksa..." class="w-full p-2 rounded-lg border border-slate-200 text-slate-800"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-checkpoint').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold">Simpan Titik Transit</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EXCEPTION -->
<div id="modal-exception" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-lg bg-white rounded-xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-rose-700 flex items-center gap-1.5">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i> Laporkan Insiden &amp; Kendala Pengiriman
                </h3>
                <p class="text-[11px] text-slate-500">Pilih rekomendasi standar farmasi atau ketik detail kejadian</p>
            </div>
            <button onclick="document.getElementById('modal-exception').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('exceptions.store', $shipment->id) }}" class="space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Kategori Insiden *</label>
                <select name="exception_type" id="exception-type-select" required onchange="updateCauseOptions()" class="w-full p-2.5 rounded-lg border border-slate-200 text-slate-800 focus:outline-none focus:border-rose-500 bg-white">
                    <option value="TEMPERATURE_BREACH">Penyimpangan Suhu (TEMPERATURE_BREACH)</option>
                    <option value="WEATHER_DELAY">Keterlambatan Cuaca (WEATHER_DELAY)</option>
                    <option value="CUSTOMS_HOLD">Tertahan Bea Cukai (CUSTOMS_HOLD)</option>
                    <option value="PORT_CONGESTION">Kepadatan Hub / Bandara (PORT_CONGESTION)</option>
                    <option value="DOCUMENTATION_ISSUE">Kendala Dokumen Karantina (DOCUMENTATION_ISSUE)</option>
                    <option value="DAMAGED">Kerusakan Fisik Kemasan (DAMAGED)</option>
                    <option value="MISROUTED">Salah Jalur Transit (MISROUTED)</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tingkat Keparahan (Severity) *</label>
                    <select name="severity" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 bg-white">
                        <option value="LOW">LOW (Rendah)</option>
                        <option value="MEDIUM" selected>MEDIUM (Sedang)</option>
                        <option value="HIGH">HIGH (Tinggi)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Waktu Terdeteksi *</label>
                    <input type="datetime-local" name="detected_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 font-mono">
                </div>
            </div>

            <!-- PRESET OPSI PENYEBAB STANDAR FARMASI -->
            <div class="p-3 rounded-lg bg-rose-50/60 border border-rose-200/70 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-[11px] font-bold text-rose-900">
                        Pilih Opsi Penyebab Standar (Rekomendasi Layak):
                    </label>
                    <span class="text-[10px] text-rose-700 font-mono font-medium">Standar SOP WHO / GDP</span>
                </div>
                <select id="quick-cause-select" onchange="applyCauseOption(this.value)" class="w-full p-2 rounded-lg border border-rose-200 bg-white text-slate-800 text-xs focus:outline-none focus:border-rose-500">
                    <!-- Populated dynamically via JS -->
                </select>

                <!-- Quick clickable pill chips -->
                <div>
                    <span class="text-[10px] text-slate-500 block mb-1">Pilihan Cepat 1-Klik:</span>
                    <div id="cause-chips-container" class="flex flex-wrap gap-1.5">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Penjelasan Penyebab Utama (Bisa Disesuaikan) *</label>
                <textarea name="root_cause" id="root-cause-textarea" rows="3" required placeholder="Pilih opsi standar di atas atau ketik rincian penyebab kejadian di sini..." class="w-full p-2.5 rounded-lg border border-slate-200 text-slate-800 text-xs focus:outline-none focus:border-rose-500"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-exception').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold shadow-xs">Kirim Laporan Insiden</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const shipmentData = @json($mapData);

        const mapEl = document.getElementById('shipment-map');
        if (!mapEl) return;

        const originLat = shipmentData.origin.lat;
        const originLng = shipmentData.origin.lng;
        const destLat = shipmentData.destination.lat;
        const destLng = shipmentData.destination.lng;

        // Tile Layers (High-resolution Satellite Hybrid with clean boundaries & labels)
        const esriSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: 'Tiles &copy; Esri &mdash; Satellite'
        });

        const esriLabels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: 'Labels &copy; Esri'
        });

        const satelliteHybrid = L.layerGroup([esriSatellite, esriLabels]);

        const osmStreet = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>'
        });

        const esriStreet = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: 'Tiles &copy; Esri'
        });

        const map = L.map('shipment-map', {
            center: [originLat, originLng],
            zoom: 4,
            dragging: true,
            touchZoom: true,
            scrollWheelZoom: true,
            doubleClickZoom: true,
            boxZoom: true,
            tap: false,
            keyboard: true,
            inertia: true,
            layers: [satelliteHybrid]
        });

        map.dragging.enable();
        if (map.keyboard) map.keyboard.enable();

        window.panShipmentMap = function(dx, dy) {
            if (map) {
                map.panBy([dx, dy], { animate: true, duration: 0.3 });
            }
        };

        L.control.layers({
            "🛰️ Satelit Rapi (Hybrid)": satelliteHybrid,
            "🌍 Satelit Murni": esriSatellite,
            "🗺️ Peta Jalan (OSM)": osmStreet,
            "📍 Esri Street": esriStreet
        }, null, { position: 'topright' }).addTo(map);

        // Force invalidation to avoid blank white canvas
        setTimeout(() => { map.invalidateSize(); }, 150);
        setTimeout(() => { map.invalidateSize(); }, 500);
        window.addEventListener('resize', () => { map.invalidateSize(); });

        const s = shipmentData;
        const mode = s.transport_type;
        const modeIcon = mode === 'air' ? '✈️' : (mode === 'sea' ? '🚢' : '🚚');
        const originEmoji = mode === 'sea' ? '⚓' : (mode === 'land' ? '🏭' : '🛫');
        const destEmoji = mode === 'sea' ? '⚓' : (mode === 'land' ? '🏥' : '🛬');
        const originTitle = mode === 'sea' ? '⚓ Pelabuhan Muat Asal' : (mode === 'land' ? '🏭 Gudang / Titik Asal' : '🛫 Bandara / Titik Asal');
        const destTitle = mode === 'sea' ? '⚓ Pelabuhan Bongkar Tujuan' : (mode === 'land' ? '🏥 Fasilitas / Titik Tujuan' : '🛬 Bandara / Titik Tujuan');
        const routeColor = s.is_delayed ? '#f59e0b' : (mode === 'sea' ? '#0d9488' : (mode === 'land' ? '#d97706' : '#0284c7'));

        // Helper popup generator
        function createPointPopup(nodeType, title, locationName, cityCountry, temp, timestamp) {
            let tempHtml = '';
            if (temp !== null && temp !== undefined) {
                const isSafe = temp >= s.target_temp_min && temp <= s.target_temp_max;
                tempHtml = `
                    <div class="p-2 rounded bg-teal-50 border border-teal-200/80 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-[10px] text-teal-800 font-semibold block">Suhu Kontainer:</span>
                            <span class="font-mono font-bold text-sm text-teal-950">${temp}&deg;C</span>
                            <span class="text-[10px] font-semibold ml-1 ${isSafe ? 'text-emerald-700' : 'text-rose-600'}">
                                ${isSafe ? '✓ Batas Aman' : '⚠️ OUT OF RANGE'}
                            </span>
                        </div>
                        <div class="text-right text-[10px] text-slate-500">
                            <span class="block">Target:</span>
                            <span class="font-mono font-bold text-slate-700">${s.target_temp_min}&deg;C ~ ${s.target_temp_max}&deg;C</span>
                        </div>
                    </div>
                `;
            }

            return `
                <div class="font-sans text-slate-800 text-xs p-3 space-y-2 min-w-[240px]">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-100">
                        <div class="flex items-center gap-1.5 font-bold text-slate-900">
                            <span class="text-sm">${modeIcon}</span>
                            <span class="font-mono text-xs text-teal-800 font-bold">${s.shipment_number}</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold ${s.is_delayed ? 'bg-amber-100 text-amber-800' : 'bg-teal-100 text-teal-800'}">
                            ${s.current_status}
                        </span>
                    </div>

                    <div class="text-[11px] text-slate-700 bg-slate-50 p-2 rounded border border-slate-200/80">
                        <span class="text-[10px] uppercase font-bold text-teal-700 block">${title}</span>
                        <div class="font-semibold text-slate-900">${locationName}</div>
                        ${cityCountry ? `<div class="text-slate-500 text-[10px]">${cityCountry}</div>` : ''}
                        ${timestamp ? `<div class="text-[10px] font-mono text-slate-400 mt-0.5">${timestamp}</div>` : ''}
                    </div>

                    <div class="space-y-0.5">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Muatan Vaksin / Farmasi</span>
                        <div class="font-bold text-slate-900 text-xs">${s.product_name}</div>
                        <div class="flex items-center gap-2 text-[11px] text-slate-600">
                            <span>Batch: <b class="font-mono text-teal-900">${s.batch_number}</b></span>
                            <span>&bull;</span>
                            <span><b class="text-slate-800">${Number(s.quantity_vials).toLocaleString()} vials</b></span>
                        </div>
                    </div>

                    ${tempHtml}

                    <div class="pt-1 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500">
                        <span>Carrier: <b class="text-slate-700">${s.carrier_name}</b></span>
                        <span class="uppercase font-mono font-semibold">${s.transport_type} FREIGHT</span>
                    </div>
                </div>
            `;
        }

        // Custom DivIcons
        const originIcon = L.divIcon({
            className: 'custom-map-icon',
            html: `<div class="w-8 h-8 rounded-full bg-teal-800 text-white flex items-center justify-center font-bold text-sm shadow-md border-2 border-white ring-2 ring-teal-900/30">${originEmoji}</div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        const destIcon = L.divIcon({
            className: 'custom-map-icon',
            html: `<div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-md border-2 border-white ring-2 ring-emerald-900/30">${destEmoji}</div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        const vehicleIcon = L.divIcon({
            className: 'custom-map-icon',
            html: `<div class="relative flex items-center justify-center w-9 h-9 rounded-full bg-teal-600 text-white shadow-xl text-base border-2 border-white ring-2 ring-teal-500/40"><span class="animate-ping absolute inset-0 rounded-full bg-teal-400 opacity-60"></span><span class="relative z-10">${modeIcon}</span></div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });

        const routeCoords = [];

        // 1. Origin Marker
        const originPopup = createPointPopup('ORIGIN', originTitle, s.origin.name, `${s.origin.city}, ${s.origin.country}`, null, null);
        const originMarker = L.marker([originLat, originLng], { icon: originIcon }).addTo(map);
        originMarker.bindPopup(originPopup);
        routeCoords.push([originLat, originLng]);

        // 2. Checkpoints
        let lastValidCoord = null;
        let lastCheckpointTemp = null;
        let lastEventTime = null;

        s.tracking_events.forEach((ev) => {
            if (ev.lat !== null && ev.lng !== null) {
                routeCoords.push([ev.lat, ev.lng]);
                lastValidCoord = [ev.lat, ev.lng];
                if (ev.container_temperature !== null) {
                    lastCheckpointTemp = ev.container_temperature;
                }
                lastEventTime = ev.formatted_time;

                const isAlert = ev.event_type === 'EXCEPTION_OCCURRED' || (ev.container_temperature !== null && (ev.container_temperature < s.target_temp_min || ev.container_temperature > s.target_temp_max));
                const cpIcon = L.divIcon({
                    className: 'custom-map-icon',
                    html: `<div class="w-6 h-6 rounded-full ${isAlert ? 'bg-rose-600' : 'bg-teal-600'} text-white flex items-center justify-center text-xs shadow-md border-2 border-white font-bold">${isAlert ? '⚠️' : '●'}</div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                const cpPopup = createPointPopup(
                    'CHECKPOINT',
                    `🔄 ${ev.status_snapshot || 'Transit Checkpoint'}`,
                    ev.location_name,
                    ev.city,
                    ev.container_temperature,
                    ev.formatted_time
                );

                L.marker([ev.lat, ev.lng], { icon: cpIcon }).addTo(map).bindPopup(cpPopup);
            }
        });

        // 3. Destination Marker
        const destPopup = createPointPopup('DESTINATION', destTitle, s.destination.name, `${s.destination.city}, ${s.destination.country}`, null, null);
        const destMarker = L.marker([destLat, destLng], { icon: destIcon }).addTo(map);
        destMarker.bindPopup(destPopup);
        routeCoords.push([destLat, destLng]);

        // 4. Active Vehicle Marker (if currently IN_TRANSIT or PICKED_UP)
        if (s.current_status === 'IN_TRANSIT' || s.current_status === 'PICKED_UP') {
            const vehicleCoord = lastValidCoord || [(originLat + destLat) / 2, (originLng + destLng) / 2];
            const vehiclePopup = createPointPopup(
                'VEHICLE',
                `${modeIcon} Armada Kargo Aktif (${s.carrier_name})`,
                lastValidCoord ? 'Posisi Terakhir Terlaporkan' : 'En-Route Midpoint Corridor',
                `${s.origin.city} &rarr; ${s.destination.city}`,
                lastCheckpointTemp,
                lastEventTime || 'Live En-Route Telemetry'
            );

            const vMarker = L.marker(vehicleCoord, { icon: vehicleIcon, zIndexOffset: 1000 }).addTo(map);
            vMarker.bindPopup(vehiclePopup);
        }

        // 5. Draw Flight / Sea / Ground Polyline Corridor
        const polyline = L.polyline(routeCoords, {
            color: routeColor,
            weight: 3.5,
            opacity: 0.85,
            dashArray: mode === 'air' ? '8, 8' : (mode === 'sea' ? '6, 6' : null)
        }).addTo(map);

        window.recenterShipmentMap = function () {
            if (polyline && routeCoords.length > 0) {
                map.fitBounds(polyline.getBounds(), { padding: [50, 50], maxZoom: 8 });
            }
        };

        if (routeCoords.length > 0) {
            map.fitBounds(polyline.getBounds(), { padding: [50, 50], maxZoom: 8 });
        }

        // 6. Live Geocoding Handler (Clean, Single Instance)
        const geocodeInput = document.getElementById('geocode-search-input');
        const btnGeocode = document.getElementById('btn-geocode-search');
        const geocodeResults = document.getElementById('geocode-results');
        const feedbackDiv = document.getElementById('geocode-live-feedback');
        const feedbackText = document.getElementById('geocode-feedback-text');
        const latInput = document.getElementById('checkpoint-lat');
        const lngInput = document.getElementById('checkpoint-lng');
        const headlineInput = document.getElementById('checkpoint-headline');

        if (btnGeocode && geocodeInput) {
            const doGeocode = async function () {
                const q = geocodeInput.value.trim();
                if (q.length < 2) return;

                btnGeocode.disabled = true;
                btnGeocode.innerHTML = '<span class="animate-spin text-xs">⌛</span>';

                try {
                    const res = await fetch('{{ route("ajax.geocode") }}?q=' + encodeURIComponent(q));
                    const data = await res.json();

                    btnGeocode.disabled = false;
                    btnGeocode.innerHTML = '<i data-lucide="search" class="w-3.5 h-3.5"></i> Find';
                    if (window.lucide) lucide.createIcons();

                    if (data.success && data.data && data.data.length > 0) {
                        geocodeResults.innerHTML = '';
                        geocodeResults.classList.remove('hidden');

                        data.data.forEach(item => {
                            const row = document.createElement('div');
                            row.className = 'p-2 hover:bg-teal-50 rounded-lg cursor-pointer border-b border-slate-100 last:border-b-0 flex items-start justify-between gap-2';
                            row.innerHTML = `
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900">${item.name}</div>
                                    <div class="text-[10px] text-slate-400 truncate max-w-xs">${item.display_name}</div>
                                </div>
                                <span class="text-[10px] font-mono font-bold text-teal-700 bg-teal-50 border border-teal-200 px-1.5 py-0.5 rounded shrink-0">
                                    ${item.lat.toFixed(4)}, ${item.lng.toFixed(4)}
                                </span>
                            `;
                            row.addEventListener('click', function () {
                                if (latInput) latInput.value = item.lat;
                                if (lngInput) lngInput.value = item.lng;
                                if (headlineInput) headlineInput.value = 'Titik transit terverifikasi di ' + item.name;
                                geocodeResults.classList.add('hidden');
                                if (feedbackDiv && feedbackText) {
                                    feedbackDiv.classList.remove('hidden');
                                    feedbackText.textContent = `GPS Diterapkan: ${item.name} (${item.lat.toFixed(4)}, ${item.lng.toFixed(4)})`;
                                }
                            });
                            geocodeResults.appendChild(row);
                        });
                    } else {
                        geocodeResults.innerHTML = '<div class="p-2 text-slate-400 text-center text-xs">Koordinat tidak ditemukan untuk pencarian tersebut.</div>';
                        geocodeResults.classList.remove('hidden');
                    }
                } catch (err) {
                    btnGeocode.disabled = false;
                    btnGeocode.innerHTML = 'Cari';
                    console.error('Geocoding error:', err);
                }
            };

            btnGeocode.addEventListener('click', doGeocode);
            geocodeInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    doGeocode();
                }
            });

            // Live auto-search debounce when typing in geocode input
            let geocodeTimer = null;
            geocodeInput.addEventListener('input', function () {
                clearTimeout(geocodeTimer);
                if (this.value.trim().length >= 3) {
                    geocodeTimer = setTimeout(doGeocode, 400);
                } else if (this.value.trim().length === 0) {
                    geocodeResults.classList.add('hidden');
                }
            });
        }

        // Quick Popular Location Selector (One-Click GPS Apply)
        window.selectQuickLocation = function(name, lat, lng) {
            const geocodeInput = document.getElementById('geocode-search-input');
            const latInput = document.getElementById('checkpoint-lat');
            const lngInput = document.getElementById('checkpoint-lng');
            const headlineInput = document.getElementById('checkpoint-headline');
            const geocodeResults = document.getElementById('geocode-results');
            const feedbackDiv = document.getElementById('geocode-live-feedback');
            const feedbackText = document.getElementById('geocode-feedback-text');

            if (geocodeInput) geocodeInput.value = name;
            if (latInput) latInput.value = lat;
            if (lngInput) lngInput.value = lng;

            if (headlineInput) {
                const baseTitle = headlineInput.value.split(' - ')[0] || 'Titik transit terverifikasi';
                headlineInput.value = baseTitle + ' - ' + name;
            }

            if (geocodeResults) geocodeResults.classList.add('hidden');

            if (feedbackDiv && feedbackText) {
                feedbackDiv.classList.remove('hidden');
                feedbackText.textContent = `GPS Diterapkan: ${name} (${Number(lat).toFixed(4)}, ${Number(lng).toFixed(4)})`;
            }

            // Sync master location dropdown if matching
            const locSelect = document.getElementById('checkpoint-location-id');
            if (locSelect) {
                const lowerName = name.toLowerCase();
                for (let i = 0; i < locSelect.options.length; i++) {
                    const optText = locSelect.options[i].text.toLowerCase();
                    if (optText.length > 3 && (lowerName.includes(optText) || optText.includes(lowerName.split('(')[0].trim().toLowerCase()))) {
                        locSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        };

        // Checkpoint Quick Presets System
        const checkpointPresets = {
            takeoff: {
                event_type: 'IN_TRANSIT',
                headline: 'Kargo diberangkatkan dari fasilitas keberangkatan',
                description: 'Unit pendingin aktif kontainer beroperasi normal. Segel tamper-evident utuh dan datalogger aktif merekam posisi koordinat.',
                temp: {{ $idealMidTemp }}
            },
            hub_arrive: {
                event_type: 'TRANSIT_HUB',
                headline: 'Tiba di Terminal Kargo Hub Transit',
                description: 'Kargo tiba di terminal transit internasional. Pemeriksaan visual oleh petugas kargo mengonfirmasi indikator pendingin dalam batas aman prima.',
                temp: {{ $idealMidTemp }}
            },
            customs: {
                event_type: 'CUSTOMS_INSPECTION',
                headline: 'Pemeriksaan kepabeanan & rilis karantina farmasi selesai',
                description: 'Pemeriksaan dokumen pabean, CoA batch, dan inspeksi suhu non-invasif oleh petugas karantina kesehatan & bea cukai telah selesai tanpa kendala.',
                temp: {{ $idealMidTemp }}
            },
            temp_check: {
                event_type: 'TRANSIT_HUB',
                headline: 'Inspeksi berkala cold-chain & kalibrasi sensor IoT',
                description: 'Pengecekan datalogger digital menunjukkan stabilitas suhu terjaga sempurna. Baterai logger 98%, tidak ada anomali atau deviasi termal.',
                temp: {{ $idealMidTemp }}
            },
            delivered: {
                event_type: 'DELIVERED',
                headline: 'Tiba di fasilitas gudang instalasi farmasi tujuan',
                description: 'Kargo berhasil diserahterimakan kepada penanggung jawab instalasi farmasi penerima dalam kondisi dingin optimal dan segel keamanan utuh.',
                temp: {{ $idealMidTemp }}
            }
        };

        window.applyCheckpointPreset = function(key) {
            const p = checkpointPresets[key];
            if (!p) return;

            const eventSelect = document.getElementById('checkpoint-event-type');
            if (eventSelect) eventSelect.value = p.event_type;

            const headlineInput = document.getElementById('checkpoint-headline');
            const locSelect = document.getElementById('checkpoint-location-id');
            let locText = '';
            if (locSelect && locSelect.selectedIndex > 0) {
                locText = ' - ' + locSelect.options[locSelect.selectedIndex].text;
            }
            if (headlineInput) headlineInput.value = p.headline + locText;

            const descTextarea = document.querySelector('#modal-checkpoint textarea[name="description"]');
            if (descTextarea) descTextarea.value = p.description;

            const tempInput = document.getElementById('checkpoint-temp-input');
            if (tempInput) tempInput.value = p.temp;
        };

        window.setCheckpointTemp = function(temp) {
            const tempInput = document.getElementById('checkpoint-temp-input');
            if (tempInput) tempInput.value = temp;
        };

        // Location dropdown auto-fill
        const locSelect = document.getElementById('checkpoint-location-id');
        if (locSelect) {
            locSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const lat = opt.getAttribute('data-lat');
                const lng = opt.getAttribute('data-lng');
                if (lat && lng) {
                    if (latInput) latInput.value = lat;
                    if (lngInput) lngInput.value = lng;
                    if (headlineInput) {
                        const baseTitle = headlineInput.value.split(' - ')[0] || 'Titik transit terverifikasi';
                        headlineInput.value = baseTitle + ' - ' + opt.text;
                    }
                }
            });
        }

        // Standar Opsi Penyebab Insiden (WHO PQS & IATA CEIV Pharma)
        const standardCauses = {
            'TEMPERATURE_BREACH': [
                'Kompresor pendingin aktif kontainer Envirotainer mengalami malfungsi baterai/daya.',
                'Kargo terpapar panas ekstrem (>30°C) di apron bandara saat proses transfer bagasi.',
                'Cadangan es kering (dry-ice) pada kemasan sekunder habis sebelum mencapai hub transit.',
                'Pintu ruang pendingin transit (cold room) terbuka terlalu lama saat proses verifikasi muatan.',
                'Sensor telemetri data logger mendeteksi fluktuasi suhu melebihi ambang batas toleransi aman.'
            ],
            'WEATHER_DELAY': [
                'Penerbangan kargo ditunda akibat badai siklon tropis dan pembekuan landasan pacu bandara.',
                'Penutupan sementara ruang udara dan pembatasan slot pendaratan darurat akibat cuaca buruk.',
                'Suhu ekstrem luar ruang di bandara transit melebihi batas aman operasional ground handling.'
            ],
            'CUSTOMS_HOLD': [
                'Pemeriksaan karantina obat biologi tambahan oleh otoritas bea cukai setempat.',
                'Menunggu verifikasi faktur komersial dan Certificate of Analysis (CoA) resmi dari Bio Farma.',
                'Penyelarasan kode HS (Harmonized System Code) dan dokumen pembebasan bea masuk vaksin WHO.',
                'Perbedaan stempel sertifikasi legalisir impor antar kementerian kesehatan negara tujuan.'
            ],
            'PORT_CONGESTION': [
                'Antrean panjang antarmoda kargo udara internasional di terminal kargo bandara transit.',
                'Kapasitas ruang pendingin (cold storage) di terminal bandara transit sedang terisi penuh (overcapacity).',
                'Keterbatasan personel ground-handling bersertifikat IATA CEIV Pharma pada pergantian shift.'
            ],
            'DOCUMENTATION_ISSUE': [
                'Dokumen Air Waybill (AWB) memerlukan penerbitan ulang akibat revisi data nama consignee.',
                'Surat Izin Edar Khusus (Emergency Use Listing / EUL) belum terverifikasi di portal logistik.',
                'Sertifikat kalibrasi sensor suhu aktif (data logger) belum terlampir pada amplop dokumen fisik.'
            ],
            'DAMAGED': [
                'Palet kemasan sekunder mengalami benturan ringan saat penanganan forklift, vial vaksin tetap aman.',
                'Pita segel tamper-evident pada kontainer luar terkelupas selama pemindahan kontainer kargo.'
            ],
            'MISROUTED': [
                'Kargo tidak sengaja dipindahkan ke terminal kargo domestik oleh petugas ground-handling.',
                'Salah penempatan jadwal penerbangan lanjutan oleh staf maskapai di hub transit penghubung.'
            ]
        };

        window.updateCauseOptions = function () {
            const typeSelect = document.getElementById('exception-type-select');
            const causeSelect = document.getElementById('quick-cause-select');
            const chipsContainer = document.getElementById('cause-chips-container');
            if (!typeSelect || !causeSelect) return;

            const selectedType = typeSelect.value;
            const options = standardCauses[selectedType] || [];

            causeSelect.innerHTML = '<option value="">-- Pilih Rekomendasi Penyebab Standar (Klik untuk Mengisi) --</option>';
            if (chipsContainer) chipsContainer.innerHTML = '';

            options.forEach((opt, idx) => {
                const optionEl = document.createElement('option');
                optionEl.value = opt;
                optionEl.textContent = (idx + 1) + '. ' + (opt.length > 70 ? opt.substring(0, 67) + '...' : opt);
                causeSelect.appendChild(optionEl);

                if (chipsContainer && idx < 3) {
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'px-2 py-1 rounded bg-white hover:bg-rose-100 hover:text-rose-800 text-[10px] text-slate-700 border border-rose-200 transition-colors text-left truncate max-w-full font-medium';
                    chip.textContent = '⚡ ' + (opt.length > 40 ? opt.substring(0, 37) + '...' : opt);
                    chip.title = opt;
                    chip.onclick = function () {
                        causeSelect.value = opt;
                        applyCauseOption(opt);
                    };
                    chipsContainer.appendChild(chip);
                }
            });

            const manualOption = document.createElement('option');
            manualOption.value = 'MANUAL';
            manualOption.textContent = '✏️ Lainnya (Ketik Manual Sendiri)...';
            causeSelect.appendChild(manualOption);
        };

        window.applyCauseOption = function (val) {
            const textarea = document.getElementById('root-cause-textarea');
            if (!textarea) return;
            if (val && val !== 'MANUAL') {
                textarea.value = val;
            } else if (val === 'MANUAL') {
                textarea.value = '';
                textarea.focus();
            }
        };

        window.applyResolutionOption = function (selectEl, inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            if (selectEl.value && selectEl.value !== 'MANUAL') {
                input.value = selectEl.value;
            } else if (selectEl.value === 'MANUAL') {
                input.value = '';
                input.focus();
            }
        };

        // Inisialisasi opsi pada saat halaman selesai dimuat
        updateCauseOptions();
    });
</script>
@endpush
@endsection
