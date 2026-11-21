@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- HEADER -->
    <div class="pb-3 border-b border-slate-200">
        <div class="flex items-center gap-2 mb-1 text-xs text-slate-500 font-medium">
            <a href="{{ route('shipments.index') }}" class="hover:text-teal-700">Pengiriman</a>
            <span>/</span>
            <span class="text-slate-800 font-bold">Baru</span>
        </div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pengiriman Baru</h1>
    </div>

    <!-- FORM CARD -->
    <div class="p-6 rounded-xl bg-white border border-slate-200/90 shadow-sm space-y-6">

        <!-- QUICK PRESET TEMPLATES BANNER (OPSI SIAP PAKAI) -->
        <div class="p-4 rounded-xl bg-gradient-to-r from-teal-50 via-slate-50 to-emerald-50/40 border border-teal-200 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="p-1.5 rounded-lg bg-teal-600 text-white shadow-2xs">
                        <i data-lucide="zap" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Opsi Cepat Manifes (One-Click Auto-Fill)</h4>
                        <p class="text-[11px] text-slate-600">Admin tidak perlu mengetik manual — cukup klik salah satu opsi vaksin/obat di bawah untuk otomatis mengisi formulir:</p>
                    </div>
                </div>
            </div>

            <!-- PRESET BUTTONS / CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5">
                <button type="button" onclick="applyPreset('nopv2')" class="p-3 rounded-lg border border-teal-300 bg-white hover:bg-teal-600 hover:text-white text-left transition-all shadow-2xs group cursor-pointer">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold group-hover:text-white text-slate-900">🧊 Vaksin Polio nOPV2</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-cyan-100 text-cyan-800 group-hover:bg-white/20 group-hover:text-white">-20°C</span>
                    </div>
                    <p class="text-[11px] text-slate-500 group-hover:text-teal-100 line-clamp-1">Vaksin Beku Bio Farma (50k Vial)</p>
                </button>

                <button type="button" onclick="applyPreset('hepb')" class="p-3 rounded-lg border border-emerald-300 bg-white hover:bg-emerald-600 hover:text-white text-left transition-all shadow-2xs group cursor-pointer">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold group-hover:text-white text-slate-900">💉 Hepatitis B & BCG</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 group-hover:bg-white/20 group-hover:text-white">+2°C to +8°C</span>
                    </div>
                    <p class="text-[11px] text-slate-500 group-hover:text-emerald-100 line-clamp-1">Chilled Cold-Chain (25k Vial)</p>
                </button>

                <button type="button" onclick="applyPreset('insulin')" class="p-3 rounded-lg border border-blue-300 bg-white hover:bg-blue-600 hover:text-white text-left transition-all shadow-2xs group cursor-pointer">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold group-hover:text-white text-slate-900">🧬 Insulin & Biologik</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-100 text-blue-800 group-hover:bg-white/20 group-hover:text-white">+2°C to +8°C</span>
                    </div>
                    <p class="text-[11px] text-slate-500 group-hover:text-blue-100 line-clamp-1">Hormon & Onkologi (15k Cartridge)</p>
                </button>

                <button type="button" onclick="applyPreset('mrna')" class="p-3 rounded-lg border border-indigo-300 bg-white hover:bg-indigo-600 hover:text-white text-left transition-all shadow-2xs group cursor-pointer">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold group-hover:text-white text-slate-900">❄️ Vaksin mRNA Ultra-Cold</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-indigo-100 text-indigo-800 group-hover:bg-white/20 group-hover:text-white">-70°C</span>
                    </div>
                    <p class="text-[11px] text-slate-500 group-hover:text-indigo-100 line-clamp-1">Dry Ice VIP Shipper (30k Dosis)</p>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('shipments.store') }}" class="space-y-6 text-xs">
            @csrf

            <!-- 1. Consignment Identity & Routing -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4 text-teal-600"></i> Rute &amp; Armada
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nomor Resi / AWB *</label>
                        <input type="text" name="shipment_number" value="{{ old('shipment_number', 'SHP-' . date('Y') . '-' . rand(100000, 999999)) }}" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-900 font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Penerima (Klien / Organisasi) *</label>
                        <select name="customer_id" required class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800">
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->organization ?? $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Mitra Armada Kurir *</label>
                        <select name="carrier_id" required class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800">
                            @foreach($carriers as $car)
                            <option value="{{ $car->id }}" {{ old('carrier_id') == $car->id ? 'selected' : '' }}>{{ $car->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Titik Keberangkatan (Asal) *</label>
                        <select name="origin_location_id" required class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800">
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $loc->code === 'BDO-PLANT' ? 'selected' : '' }}>{{ $loc->name }} ({{ $loc->city }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Titik Tujuan (Penerima) *</label>
                        <select name="destination_location_id" required class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800">
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $loc->code === 'ABV-COLD' ? 'selected' : '' }}>{{ $loc->name }} ({{ $loc->city }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- 2. Cargo & Thermal Thresholds -->
            <div class="space-y-4 pt-2">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="package" class="w-4 h-4 text-teal-600"></i> Manifes &amp; Suhu
                    </h3>
                    <button type="button" onclick="addItemRow()" class="px-2.5 py-1 rounded-lg border border-teal-300 bg-teal-50 hover:bg-teal-100 text-teal-800 text-[11px] font-semibold flex items-center gap-1 shadow-xs transition-colors">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Item
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block font-medium text-slate-700 mb-1">Deskripsi Kargo *</label>
                        <input type="text" name="cargo_type" value="Biological Vaccine - Cold Chain (nOPV2)" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Target Suhu Min (°C) *</label>
                        <input type="number" step="0.5" name="target_temp_min" id="target_temp_min" value="-25.0" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Target Suhu Maks (°C) *</label>
                        <input type="number" step="0.5" name="target_temp_max" id="target_temp_max" value="-15.0" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                </div>

                <!-- OPSI CEPAT RENTANG SUHU STANDAR FARMASI -->
                <div class="flex items-center gap-1.5 flex-wrap p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                    <span class="text-[10px] text-slate-600 font-bold uppercase flex items-center gap-1">
                        <i data-lucide="thermometer-snowflake" class="w-3.5 h-3.5 text-teal-600"></i> Pilih Suhu Cepat:
                    </span>
                    <button type="button" onclick="setTempRange(-25, -15)" class="px-2 py-0.5 rounded bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                        🧊 Beku (-25°C s/d -15°C)
                    </button>
                    <button type="button" onclick="setTempRange(2, 8)" class="px-2 py-0.5 rounded bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                        💉 Dingin (+2°C s/d +8°C)
                    </button>
                    <button type="button" onclick="setTempRange(-80, -60)" class="px-2 py-0.5 rounded bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                        ❄️ Ultra-Cold (-80°C s/d -60°C)
                    </button>
                    <button type="button" onclick="setTempRange(15, 25)" class="px-2 py-0.5 rounded bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                        🌡️ Terkendali (+15°C s/d +25°C)
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Total Berat Kotor (kg) *</label>
                        <input type="number" step="0.1" name="total_weight_kg" value="850.0" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Total Volume (CBM)</label>
                        <input type="number" step="0.01" name="total_volume_cbm" value="4.25" 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                </div>

                <!-- DYNAMIC PHARMACEUTICAL ITEMS REPOSITORY -->
                <div id="items-container" class="space-y-3 pt-2">
                    <div class="item-row p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-teal-800 flex items-center gap-1.5">
                                <i data-lucide="pill" class="w-3.5 h-3.5 text-teal-600"></i> Item Farmasi #1
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block font-medium text-slate-700 mb-1">Nama Produk *</label>
                                <input type="text" name="items[0][product_name]" value="nOPV2 Polio Vaccine (10-dose)" required 
                                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800">
                            </div>
                            <div>
                                <label class="block font-medium text-slate-700 mb-1">Nomor Batch / Lot *</label>
                                <input type="text" name="items[0][batch_number]" value="BF-2024-LOT{{ rand(100, 999) }}" required 
                                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                            </div>
                            <div>
                                <label class="block font-medium text-slate-700 mb-1">Kode Item *</label>
                                <input type="text" name="items[0][item_code]" value="BF-NOPV2-10D" required 
                                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block font-medium text-slate-700 mb-1">Jumlah Vial *</label>
                                <input type="number" name="items[0][quantity_vials]" value="50000" required 
                                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                            </div>
                            <div>
                                <label class="block font-medium text-slate-700 mb-1">Dosis per Vial</label>
                                <input type="number" name="items[0][doses_per_vial]" value="10" required 
                                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                            </div>
                            <div>
                                <label class="block font-medium text-slate-700 mb-1">Tanggal Kedaluwarsa *</label>
                                <input type="date" name="items[0][expiry_date]" value="{{ date('Y-m-d', strtotime('+2 years')) }}" required 
                                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                            </div>
                            <div>
                                <label class="block font-medium text-slate-700 mb-1">Tipe Kontainer Pendingin</label>
                                <input type="text" name="items[0][container_type]" value="Envirotainer RKN e1 Active Cold Unit" required 
                                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800">
                                <!-- Opsi Cepat Kontainer -->
                                <div class="flex items-center gap-1 flex-wrap mt-1">
                                    <span class="text-[9px] text-slate-500 font-semibold">Pilih Cepat:</span>
                                    <button type="button" onclick="setContainerType(this, 'Envirotainer RKN e1 Active Cold Unit')" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-[9px] text-slate-700 border border-slate-200 cursor-pointer">Envirotainer RKN</button>
                                    <button type="button" onclick="setContainerType(this, 'Pelican BioThermal Credo Cube')" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-[9px] text-slate-700 border border-slate-200 cursor-pointer">Pelican Credo</button>
                                    <button type="button" onclick="setContainerType(this, 'VaxSafe Active Temp-Controlled Unit')" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-[9px] text-slate-700 border border-slate-200 cursor-pointer">VaxSafe Unit</button>
                                    <button type="button" onclick="setContainerType(this, 'Dry Ice VIP Passive Shipper (-75°C)')" class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-[9px] text-slate-700 border border-slate-200 cursor-pointer">Dry Ice Shipper</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Departure & Arrival Schedule -->
            <div class="space-y-3 pt-2">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-teal-600"></i> Jadwal &amp; ETA
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Perkiraan Berangkat *</label>
                        <input type="datetime-local" name="expected_departure" id="expected_departure" value="{{ now()->format('Y-m-d\TH:i') }}" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Perkiraan Tiba (ETA) *</label>
                        <input type="datetime-local" name="expected_arrival" id="expected_arrival" value="{{ now()->addDays(3)->format('Y-m-d\TH:i') }}" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                </div>

                <!-- OPSI CEPAT ESTIMASI HARI -->
                <div class="flex items-center gap-1.5 flex-wrap p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                    <span class="text-[10px] text-slate-600 font-bold uppercase flex items-center gap-1">
                        <i data-lucide="timer" class="w-3.5 h-3.5 text-teal-600"></i> Pilih Durasi Tiba (ETA):
                    </span>
                    <button type="button" onclick="setETA(1)" class="px-2 py-0.5 rounded bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                        ⚡ 1 Hari (Express Air Cargo)
                    </button>
                    <button type="button" onclick="setETA(3)" class="px-2 py-0.5 rounded bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                        ✈️ 3 Hari (Standard Air Cargo)
                    </button>
                    <button type="button" onclick="setETA(7)" class="px-2 py-0.5 rounded bg-white hover:bg-teal-600 hover:text-white text-slate-700 text-[10px] font-semibold border border-slate-200 shadow-2xs transition-colors cursor-pointer">
                        🚢 7 Hari (Sea Freight / Overland)
                    </button>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Panduan penanganan khusus..." 
                        class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800"></textarea>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('shipments.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-all">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold flex items-center gap-1.5 shadow-xs transition-all">
                    <i data-lucide="check" class="w-4 h-4"></i> Simpan
                </button>
            </div>
        </form>
    </div>

</div>

<script>
let itemCount = 1;

const presets = {
    nopv2: {
        shipment_number: 'CP-2026-AIR-' + Math.floor(100000 + Math.random() * 900000),
        cargo_type: 'Biological Vaccine - Deep Cold Chain (nOPV2 Polio)',
        target_temp_min: -25.0,
        target_temp_max: -15.0,
        total_weight_kg: 850.0,
        total_volume_cbm: 4.25,
        product_name: 'nOPV2 Polio Vaccine (10-dose)',
        batch_number: 'BF-2026-NOPV' + Math.floor(100 + Math.random() * 900),
        item_code: 'BF-NOPV2-10D',
        quantity_vials: 50000,
        doses_per_vial: 10,
        container_type: 'Envirotainer RKN e1 Active Cold Unit',
        notes: 'Wajib suhu beku konstan (-20°C). Dilarang paparan suhu > -15°C melebihi 30 menit. Standar WHO PQS.'
    },
    hepb: {
        shipment_number: 'CP-2026-AIR-' + Math.floor(100000 + Math.random() * 900000),
        cargo_type: 'Biological Vaccine - Chilled Cold Chain (+2°C to +8°C)',
        target_temp_min: 2.0,
        target_temp_max: 8.0,
        total_weight_kg: 420.0,
        total_volume_cbm: 2.10,
        product_name: 'Hepatitis B Recombinant Vaccine (5ml Vial)',
        batch_number: 'BF-2026-HEPB' + Math.floor(100 + Math.random() * 900),
        item_code: 'BF-HEPB-5ML',
        quantity_vials: 25000,
        doses_per_vial: 5,
        container_type: 'Pelican BioThermal Credo Cube passive shipper',
        notes: 'PERINGATAN: Jangan sampai beku (Freeze-sensitive). Jaga stabilitas suhu 2°C - 8°C. Standar GDP Bio Farma.'
    },
    insulin: {
        shipment_number: 'CP-2026-LAND-' + Math.floor(100000 + Math.random() * 900000),
        cargo_type: 'Pharmaceutical Biologicals - Insulin & Oncology Monoclonal',
        target_temp_min: 2.0,
        target_temp_max: 8.0,
        total_weight_kg: 310.0,
        total_volume_cbm: 1.80,
        product_name: 'Human Insulin Glargine Cartridge 100 IU/ml',
        batch_number: 'BF-2026-INSL' + Math.floor(100 + Math.random() * 900),
        item_code: 'BF-INSL-100IU',
        quantity_vials: 15000,
        doses_per_vial: 1,
        container_type: 'VaxSafe Active Temp-Controlled Unit',
        notes: 'Kargo bernilai tinggi. Hindari getaran ekstrem dan guncangan mekanis selama pengangkutan darat.'
    },
    mrna: {
        shipment_number: 'CP-2026-AIR-' + Math.floor(100000 + Math.random() * 900000),
        cargo_type: 'mRNA Biologic Formulation - Ultra-Low Temperature (-70°C)',
        target_temp_min: -80.0,
        target_temp_max: -60.0,
        total_weight_kg: 620.0,
        total_volume_cbm: 3.50,
        product_name: 'mRNA Target Biologic Formulation (0.3ml)',
        batch_number: 'BF-2026-MRNA' + Math.floor(100 + Math.random() * 900),
        item_code: 'BF-MRNA-ULTRA',
        quantity_vials: 30000,
        doses_per_vial: 6,
        container_type: 'Dry Ice VIP Passive Shipper (-75°C)',
        notes: 'Wajib verifikasi level dry-ice setiap 48 jam. Pengisian ulang es kering di hub transit jika ada penundaan penerbangan.'
    }
};

function applyPreset(key) {
    const data = presets[key];
    if (!data) return;

    const shpInput = document.querySelector('input[name="shipment_number"]');
    if (shpInput) shpInput.value = data.shipment_number;
    const cargoInput = document.querySelector('input[name="cargo_type"]');
    if (cargoInput) cargoInput.value = data.cargo_type;
    const tMin = document.querySelector('input[name="target_temp_min"]');
    if (tMin) tMin.value = data.target_temp_min;
    const tMax = document.querySelector('input[name="target_temp_max"]');
    if (tMax) tMax.value = data.target_temp_max;
    const wKg = document.querySelector('input[name="total_weight_kg"]');
    if (wKg) wKg.value = data.total_weight_kg;
    const vCbm = document.querySelector('input[name="total_volume_cbm"]');
    if (vCbm) vCbm.value = data.total_volume_cbm;
    
    // First item
    const pName = document.querySelector('input[name="items[0][product_name]"]');
    if (pName) pName.value = data.product_name;
    const bNum = document.querySelector('input[name="items[0][batch_number]"]');
    if (bNum) bNum.value = data.batch_number;
    const iCode = document.querySelector('input[name="items[0][item_code]"]');
    if (iCode) iCode.value = data.item_code;
    const qVials = document.querySelector('input[name="items[0][quantity_vials]"]');
    if (qVials) qVials.value = data.quantity_vials;
    const dVial = document.querySelector('input[name="items[0][doses_per_vial]"]');
    if (dVial) dVial.value = data.doses_per_vial;
    const cType = document.querySelector('input[name="items[0][container_type]"]');
    if (cType) cType.value = data.container_type;
    
    const notes = document.querySelector('textarea[name="notes"]');
    if (notes) notes.value = data.notes;
}

function setTempRange(min, max) {
    const tMin = document.getElementById('target_temp_min');
    const tMax = document.getElementById('target_temp_max');
    if (tMin) tMin.value = min;
    if (tMax) tMax.value = max;
}

function setContainerType(btn, typeName) {
    const parentRow = btn.closest('.item-row');
    if (parentRow) {
        const input = parentRow.querySelector('input[name*="[container_type]"]');
        if (input) input.value = typeName;
    }
}

function setETA(days) {
    const depInput = document.getElementById('expected_departure');
    const arrInput = document.getElementById('expected_arrival');
    
    const now = new Date();
    depInput.value = now.toISOString().slice(0, 16);
    
    const arrival = new Date(now.getTime() + days * 24 * 60 * 60 * 1000);
    arrInput.value = arrival.toISOString().slice(0, 16);
}

function addItemRow() {
    const container = document.getElementById('items-container');
    const idx = itemCount;
    itemCount++;

    const row = document.createElement('div');
    row.className = 'item-row p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3 relative';
    row.innerHTML = `
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-teal-800 flex items-center gap-1.5">
                <i data-lucide="pill" class="w-3.5 h-3.5 text-teal-600"></i> Item Farmasi #${idx + 1}
            </span>
            <button type="button" onclick="this.closest('.item-row').remove()" class="text-rose-600 hover:text-rose-800 text-xs font-semibold flex items-center gap-1">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="block font-medium text-slate-700 mb-1">Nama Produk *</label>
                <input type="text" name="items[${idx}][product_name]" placeholder="Contoh: Vaksin Polio / Rabies" required 
                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Nomor Batch / Lot *</label>
                <input type="text" name="items[${idx}][batch_number]" placeholder="BF-2024-LOT..." required 
                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Kode Item *</label>
                <input type="text" name="items[${idx}][item_code]" placeholder="BF-RAB-01" required 
                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block font-medium text-slate-700 mb-1">Jumlah Vial *</label>
                <input type="number" name="items[${idx}][quantity_vials]" value="10000" required 
                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Dosis per Vial</label>
                <input type="number" name="items[${idx}][doses_per_vial]" value="1" required 
                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Tanggal Kedaluwarsa *</label>
                <input type="date" name="items[${idx}][expiry_date]" value="{{ date('Y-m-d', strtotime('+2 years')) }}" required 
                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Tipe Kontainer Pendingin</label>
                <input type="text" name="items[${idx}][container_type]" value="Envirotainer RKN e1 Active Cold Unit" required 
                    class="w-full p-2 rounded-lg border border-slate-200 bg-white text-slate-800">
            </div>
        </div>
    `;

    container.appendChild(row);
    if (window.lucide) {
        lucide.createIcons({ root: row });
    }
}
</script>
@endsection
