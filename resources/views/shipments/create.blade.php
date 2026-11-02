@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- HEADER -->
    <div class="pb-3 border-b border-slate-200">
        <div class="flex items-center gap-2 mb-1 text-xs text-slate-500 font-medium">
            <a href="{{ route('shipments.index') }}" class="hover:text-blue-600">Shipments</a>
            <span>/</span>
            <span class="text-slate-800 font-bold">New Consignment</span>
        </div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Create Consignment</h1>
        <p class="text-xs text-slate-500">Register and dispatch a new temperature-controlled consignment into CargoTrace.</p>
    </div>

    <!-- FORM CARD -->
    <div class="p-6 rounded-xl bg-white border border-slate-200/90 shadow-sm space-y-6">
        <form method="POST" action="{{ route('shipments.store') }}" class="space-y-6 text-xs">
            @csrf

            <!-- 1. Consignment Identity & Routing -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4 text-blue-600"></i> 1. Consignment Routing & Identification
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Shipment Waybill No. *</label>
                        <input type="text" name="shipment_number" value="{{ old('shipment_number', 'SHP-' . date('Y') . '-' . rand(100000, 999999)) }}" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-900 font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Consignee Customer *</label>
                        <select name="customer_id" required class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800">
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->organization ?? $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Carrier Partner *</label>
                        <select name="carrier_id" required class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800">
                            @foreach($carriers as $car)
                            <option value="{{ $car->id }}" {{ old('carrier_id') == $car->id ? 'selected' : '' }}>{{ $car->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Origin Node *</label>
                        <select name="origin_location_id" required class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800">
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $loc->code === 'BDO-PLANT' ? 'selected' : '' }}>{{ $loc->name }} ({{ $loc->city }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Destination Node *</label>
                        <select name="destination_location_id" required class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800">
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $loc->code === 'ABV-COLD' ? 'selected' : '' }}>{{ $loc->name }} ({{ $loc->city }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- 2. Cargo & Thermal Thresholds -->
            <div class="space-y-3 pt-2">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="package" class="w-4 h-4 text-blue-600"></i> 2. Cargo Manifest & Cold-Chain Constraints
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Cargo Description *</label>
                        <input type="text" name="cargo_type" value="Biological Vaccine - Cold Chain (nOPV2)" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Min Target Temp (°C) *</label>
                        <input type="number" step="0.5" name="target_temp_min" value="-25.0" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Max Target Temp (°C) *</label>
                        <input type="number" step="0.5" name="target_temp_max" value="-15.0" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Product Name *</label>
                        <input type="text" name="product_name" value="nOPV2 Polio Vaccine (10-dose)" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Batch Number *</label>
                        <input type="text" name="batch_number" value="BF-2024-LOT{{ rand(100, 999) }}" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Item Code *</label>
                        <input type="text" name="item_code" value="BF-NOPV2-10D" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Quantity Vials *</label>
                        <input type="number" name="quantity_vials" value="50000" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Gross Weight (kg) *</label>
                        <input type="number" step="0.1" name="total_weight_kg" value="850.0" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Expiry Date *</label>
                        <input type="date" name="expiry_date" value="{{ date('Y-m-d', strtotime('+2 years')) }}" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Unit Container Type *</label>
                        <input type="text" name="container_type" value="Envirotainer RKN e1 Active Cold Unit" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800">
                    </div>
                </div>
            </div>

            <!-- 3. Departure & Arrival Schedule -->
            <div class="space-y-3 pt-2">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-blue-600"></i> 3. Delivery Schedule & ETA
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Expected Departure *</label>
                        <input type="datetime-local" name="expected_departure" value="{{ now()->format('Y-m-d\TH:i') }}" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Expected Arrival (ETA) *</label>
                        <input type="datetime-local" name="expected_arrival" value="{{ now()->addDays(3)->format('Y-m-d\TH:i') }}" required 
                            class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Operational Handling Notes</label>
                    <textarea name="notes" rows="2" placeholder="Cold chain handling guidelines, WHO guidelines..." 
                        class="w-full p-2.5 rounded-lg border border-slate-200 bg-white text-slate-800"></textarea>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('shipments.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-all">Cancel</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold flex items-center gap-1.5 shadow-sm transition-all">
                    <i data-lucide="check" class="w-4 h-4"></i> Save & Register Consignment
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
