@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- HEADER -->
    <div class="border-b border-slate-800 pb-3">
        <div class="flex items-center gap-2 mb-1.5 font-mono text-xs text-slate-400">
            <a href="{{ route('shipments.index') }}" class="hover:text-cyan-400">Consignments</a>
            <span>/</span>
            <span class="text-cyan-400 font-bold">New Consignment</span>
        </div>
        <h1 class="text-2xl font-black text-white tracking-tight">Register Cold-Chain Consignment</h1>
        <p class="text-xs text-slate-400">Initialize a certified biopharmaceutical export with strict temperature telemetry targets.</p>
    </div>

    <!-- FORM CARD -->
    <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 backdrop-blur-xl shadow-2xl">
        <form method="POST" action="{{ route('shipments.store') }}" class="space-y-6 text-xs">
            @csrf

            <!-- Section 1: Consignment Logistics Identification -->
            <div class="space-y-4">
                <h3 class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-2">
                    <i data-lucide="file-text" class="w-4 h-4"></i> 1. Consignment Identity & Routing
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Waybill / Shipment ID *</label>
                        <input type="text" name="shipment_number" value="{{ old('shipment_number', 'CT-BIO-' . date('Y') . '-' . rand(100, 999)) }}" required 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono font-bold">
                    </div>

                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Consignee Customer *</label>
                        <select name="customer_id" required class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->organization ?? $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Authorized Carrier *</label>
                        <select name="carrier_id" required class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                            @foreach($carriers as $car)
                            <option value="{{ $car->id }}" {{ old('carrier_id') == $car->id ? 'selected' : '' }}>{{ $car->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Origin Node (Manufacturing Facility) *</label>
                        <select name="origin_location_id" required class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $loc->code === 'BDO-PLANT' ? 'selected' : '' }}>{{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Destination Node (Strategic Cold Store) *</label>
                        <select name="destination_location_id" required class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $loc->code === 'ABV-COLD' ? 'selected' : '' }}>{{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Biopharmaceutical Payload & Active Cooling Specs -->
            <div class="space-y-4 pt-2">
                <h3 class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-2">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i> 2. Vaccine Manifest & Thermal Parameters
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Product Description *</label>
                        <input type="text" name="cargo_type" value="Biological Vaccine - Cold Chain (nOPV2)" required 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    </div>
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Min Target Temp (°C) *</label>
                        <input type="number" step="0.5" name="target_temp_min" value="-25.0" required 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Max Target Temp (°C) *</label>
                        <input type="number" step="0.5" name="target_temp_max" value="-15.0" required 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Batch / Lot Number *</label>
                        <input type="text" name="batch_number" value="BF-NOPV2-LOT{{ rand(100, 999) }}" required 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Item Code *</label>
                        <input type="text" name="item_code" value="BF-NOPV2-10D" required 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Quantity Vials *</label>
                        <input type="number" name="quantity_vials" value="100000" required 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Expiry Date *</label>
                        <input type="date" name="expiry_date" value="{{ date('Y-m-d', strtotime('+2 years')) }}" required 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Total Weight (kg) *</label>
                        <input type="number" step="0.1" name="total_weight_kg" value="1250.0" required 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Total Volume (m³)</label>
                        <input type="number" step="0.1" name="total_volume_cbm" value="6.5" 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Container Unit Type *</label>
                        <input type="text" name="container_type" value="Envirotainer RKN e1 (Active Dry Ice)" required 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    </div>
                </div>
            </div>

            <!-- Section 3: Schedule Expectations -->
            <div class="space-y-4 pt-2">
                <h3 class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-800 pb-2">
                    <i data-lucide="clock" class="w-4 h-4"></i> 3. Departure & Cold-Chain ETA
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Expected Departure *</label>
                        <input type="datetime-local" name="expected_departure" value="{{ now()->format('Y-m-d\TH:i') }}" required 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-mono text-slate-400 mb-1">Expected Arrival (ETA) *</label>
                        <input type="datetime-local" name="expected_arrival" value="{{ now()->addDays(3)->format('Y-m-d\TH:i') }}" required 
                            class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-mono text-slate-400 mb-1">Operational Instructions / Notes</label>
                    <textarea name="notes" rows="2" placeholder="Cold chain handling guidelines, WHO prequalification notes..." 
                        class="w-full p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white"></textarea>
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                <a href="{{ route('shipments.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-300 font-bold hover:bg-slate-700 transition-all">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold flex items-center gap-2 shadow-lg shadow-cyan-500/20 transition-all">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Register Consignment into Registry</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
