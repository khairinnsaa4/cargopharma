@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER & QUICK SECTION NAVIGATION -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 pb-3 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Radar Global</h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live Telemetri
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Visibilitas cuaca negara transit vaksin, valas devisa, pelabuhan reefer, dan penerbangan internasional.</p>
        </div>

        <!-- Quick Section Jump Bar -->
        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <button type="button" onclick="scrollToRadarSection('peta-cuaca-section')" class="px-3 py-1.5 rounded-lg bg-teal-600 text-white font-semibold flex items-center gap-1.5 shadow-2xs hover:bg-teal-700 transition-all cursor-pointer">
                <i data-lucide="cloud-sun" class="w-3.5 h-3.5"></i>
                <span>Peta Cuaca Tiap Negara</span>
            </button>
            <button type="button" onclick="scrollToRadarSection('konversi-valas-section')" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-medium flex items-center gap-1.5 transition-colors cursor-pointer">
                <i data-lucide="coins" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Valas</span>
            </button>
            <button type="button" onclick="scrollToRadarSection('world-clock-section')" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-medium flex items-center gap-1.5 transition-colors cursor-pointer">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Jam Dunia</span>
            </button>
            <button type="button" onclick="scrollToRadarSection('armada-pelabuhan-section')" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-medium flex items-center gap-1.5 transition-colors cursor-pointer">
                <i data-lucide="plane-takeoff" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Armada &amp; Pelabuhan</span>
            </button>
            <button type="button" onclick="scrollToRadarSection('routing-section')" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-medium flex items-center gap-1.5 transition-colors cursor-pointer">
                <i data-lucide="navigation" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Kalkulator Rute</span>
            </button>
            <button type="button" onclick="scrollToRadarSection('news-section')" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-medium flex items-center gap-1.5 transition-colors cursor-pointer">
                <i data-lucide="newspaper" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Berita PBB</span>
            </button>
        </div>
    </div>

    <!-- 1. INTERACTIVE GLOBAL COUNTRY & HUB WEATHER MAP (TOP HERO SECTION) -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden" id="peta-cuaca-section">
        <!-- Weather Map Header & Controls -->
        <div class="p-3.5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 bg-slate-50/50">
            <!-- Left: Title & Continent Filters -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2 mr-1">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-100 shrink-0">
                        <i data-lucide="cloud-sun" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-tight">Peta Cuaca Tiap Negara &amp; Daerah</h3>
                        <span class="text-[10px] text-teal-700 font-mono font-medium">{{ count($globalWeatherHubs) }} Titik Daerah &amp; Hub Internasional</span>
                    </div>
                </div>

                <!-- Continent Filter Pills -->
                <div class="inline-flex items-center rounded-lg bg-slate-200/70 p-0.5 text-xs font-semibold">
                    <button type="button" onclick="filterWeatherContinent('ALL')" id="wx-btn-ALL"
                        class="wx-tab-btn px-2.5 py-1 rounded-md bg-teal-600 text-white shadow-xs transition-all flex items-center gap-1 cursor-pointer">
                        <span>🌐 Semua</span>
                    </button>
                    <button type="button" onclick="filterWeatherContinent('Asia')" id="wx-btn-Asia"
                        class="wx-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all flex items-center gap-1 cursor-pointer">
                        <span>🌏 Asia</span>
                    </button>
                    <button type="button" onclick="filterWeatherContinent('Middle East')" id="wx-btn-Middle-East"
                        class="wx-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all flex items-center gap-1 cursor-pointer">
                        <span>🏜️ Timur Tengah</span>
                    </button>
                    <button type="button" onclick="filterWeatherContinent('Europe')" id="wx-btn-Europe"
                        class="wx-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all flex items-center gap-1 cursor-pointer">
                        <span>🌍 Eropa</span>
                    </button>
                    <button type="button" onclick="filterWeatherContinent('Africa')" id="wx-btn-Africa"
                        class="wx-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all flex items-center gap-1 cursor-pointer">
                        <span>🌍 Afrika</span>
                    </button>
                    <button type="button" onclick="filterWeatherContinent('Americas')" id="wx-btn-Americas"
                        class="wx-tab-btn px-2.5 py-1 rounded-md text-slate-700 hover:text-slate-900 transition-all flex items-center gap-1 cursor-pointer">
                        <span>🌎 Amerika</span>
                    </button>
                </div>
            </div>

            <!-- Right: Country & City Dropdown Selector & Reset View -->
            <div class="flex items-center gap-2">
                <select id="airport-weather-selector" onchange="focusAirportOnMap(this.value)" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-800 focus:outline-none focus:border-teal-600 shadow-2xs max-w-[260px] sm:max-w-xs truncate">
                    <option value="" selected>-- Pilih Negara / Daerah Dunia --</option>
                    @php
                        $groupedHubs = collect($globalWeatherHubs)->groupBy('country');
                    @endphp
                    @foreach($groupedHubs as $countryName => $cityList)
                        <optgroup label="{{ $cityList->first()['flag'] }} {{ $countryName }} ({{ count($cityList) }} Daerah)" data-continent="{{ $cityList->first()['continent'] }}">
                            @foreach($cityList as $wh)
                                @php
                                    $origIdx = 0;
                                    foreach($globalWeatherHubs as $k => $item) {
                                        if ($item['city'] === $wh['city'] && $item['hub_code'] === $wh['hub_code']) {
                                            $origIdx = $k;
                                            break;
                                        }
                                    }
                                @endphp
                                <option value="{{ $origIdx }}" data-continent="{{ $wh['continent'] }}">
                                    {{ $wh['city'] }} ({{ $wh['emoji'] ?? '⛅' }} {{ round($wh['temp'], 1) }}°C)
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>

                <button type="button" onclick="resetWeatherMap()" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs flex items-center gap-1 transition-colors shadow-2xs shrink-0" title="Pusatkan Seluruh Dunia">
                    <i data-lucide="maximize" class="w-3.5 h-3.5 text-teal-600"></i>
                    <span class="hidden sm:inline">Pusatkan</span>
                </button>
            </div>
        </div>

        <!-- Weather Map Container (100% Full Width) with Floating On-Demand Detail Drawer -->
        <div class="relative w-full h-[520px] rounded-b-xl overflow-hidden bg-slate-100">
            <!-- Full Width Interactive Leaflet Map -->
            <div id="weather-interactive-map" class="w-full h-full z-0"></div>

            <!-- Floating Pan & Navigation D-Pad Controls -->
            <div class="absolute top-3 left-14 z-[400] flex items-center gap-1 bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-lg p-1 shadow-md">
                <span class="text-[10px] font-bold text-slate-500 uppercase px-1.5 hidden sm:inline">Geser:</span>
                <button type="button" onclick="panWeatherMap(0, -180)" title="Geser ke Atas"
                    class="w-7 h-7 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                    <i data-lucide="arrow-up" class="w-3.5 h-3.5"></i>
                </button>
                <button type="button" onclick="panWeatherMap(0, 180)" title="Geser ke Bawah"
                    class="w-7 h-7 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                    <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
                </button>
                <button type="button" onclick="panWeatherMap(-180, 0)" title="Geser ke Kiri"
                    class="w-7 h-7 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                </button>
                <button type="button" onclick="panWeatherMap(180, 0)" title="Geser ke Kanan"
                    class="w-7 h-7 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </button>
                <div class="h-4 w-px bg-slate-200 mx-0.5"></div>
                <button type="button" onclick="resetWeatherMapCenter()" title="Pusatkan Peta (Reset)"
                    class="px-2 h-7 rounded bg-teal-50 hover:bg-teal-600 hover:text-white text-teal-800 flex items-center gap-1 transition-colors cursor-pointer text-xs font-semibold">
                    <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                    <span class="text-[11px] hidden sm:inline">Pusat</span>
                </button>
            </div>

            <!-- Floating Hint Badge (Visible initially, hides when country is clicked) -->
            <div id="weather-map-hint" class="absolute bottom-4 left-4 z-10 px-3.5 py-2 rounded-xl bg-slate-900/85 backdrop-blur-md text-white text-xs border border-white/20 shadow-lg pointer-events-none flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-teal-400 shrink-0"></i>
                <span class="text-[11px]">Bisa digeser bebas (drag) ke atas/bawah/kiri/kanan atau klik pin cuaca untuk detail.</span>
            </div>

            <!-- Selected Country/Airport Floating Weather Drawer (Hidden on Initial Load, Appears ONLY on Click) -->
            <div id="weather-detail-panel" class="hidden absolute top-4 right-4 z-20 w-80 sm:w-96 max-h-[480px] overflow-y-auto rounded-2xl bg-white/95 backdrop-blur-md border border-slate-200/90 shadow-2xl p-4 space-y-3 transition-all duration-300">
                <!-- Header with Close Button -->
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span id="panel-flag" class="text-3xl">🇦🇪</span>
                        <div>
                            <h4 id="panel-country" class="text-sm font-bold text-slate-900 leading-snug">Uni Emirat Arab</h4>
                            <span id="panel-city" class="text-[11px] text-slate-500 block leading-tight font-medium">Dubai Hub</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="panel-hub-badge" class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-teal-100 text-teal-800 border border-teal-200">DXB</span>
                        <button type="button" onclick="closeWeatherPanel()" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-500 flex items-center justify-center transition-colors cursor-pointer" title="Tutup Detail">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Temperature & Condition Hero Box -->
                <div class="p-3.5 rounded-xl bg-gradient-to-br from-slate-50 to-teal-50/40 border border-slate-200 shadow-2xs space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span id="panel-weather-emoji" class="text-3xl">☀️</span>
                            <div>
                                <span class="text-2xl font-black text-slate-900 font-mono block tracking-tight" id="panel-temp">30.7°C</span>
                                <span class="text-xs font-bold text-teal-800" id="panel-condition">Cerah</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Cuaca Real-Time
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/60 text-[11px] font-mono text-slate-600">
                        <div>Sensasi: <strong class="text-slate-900 font-bold" id="panel-feels">32°C</strong></div>
                        <div>Angin: <strong class="text-slate-900 font-bold" id="panel-wind">14 km/h</strong></div>
                        <div>Kelembapan: <strong class="text-slate-900 font-bold" id="panel-humidity">45%</strong></div>
                        <div>Kontinen: <strong class="text-slate-900 font-bold" id="panel-continent">Middle East</strong></div>
                    </div>
                </div>

                <!-- Cold-Chain Threat Guidance -->
                <div class="p-3 rounded-xl border bg-white space-y-1.5 text-xs shadow-2xs" id="panel-risk-box">
                    <div class="flex items-center gap-1.5 font-bold text-[11px] text-slate-900">
                        <i data-lucide="shield-check" class="w-4 h-4 text-teal-600 shrink-0"></i>
                        <span id="panel-risk-title">Standar Termal Cold-Chain</span>
                    </div>
                    <p class="text-[11px] text-slate-700 leading-relaxed font-sans" id="panel-risk-desc">
                        Suhu ambien dalam batas wajar penanganan standar farmasi.
                    </p>
                </div>

                <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono pt-1">
                    <span>Live Open-Meteo API</span>
                    <button type="button" onclick="closeWeatherPanel()" class="text-teal-700 font-semibold hover:underline cursor-pointer">Tutup [✕]</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. TWO-WAY CURRENCY CONVERTER -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs p-5 space-y-4" id="konversi-valas-section">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-100 shrink-0">
                    <i data-lucide="coins" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Konversi Valas</h3>
            </div>
            <span class="text-[11px] font-mono text-slate-400">{{ $currencyData['last_updated'] ?? date('d M Y') }}</span>
        </div>

        <!-- Converter Controls Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
            <!-- From Currency & Amount -->
            <div class="lg:col-span-5 p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                <span class="text-xs font-semibold text-slate-700 block">Dari</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <!-- Custom Currency Dropdown (From) -->
                    <div class="relative" id="currency-from-container">
                        <!-- Hidden select for compatibility -->
                        <select id="currency-from" onchange="runConverter()" class="hidden">
                            <option value="IDR" data-symbol="Rp" data-rate="1" data-flag="id" selected>IDR - Indonesia (Rupiah)</option>
                            @foreach($currencyData['all_currencies'] as $c)
                                <option value="{{ $c['code'] }}" data-symbol="{{ $c['symbol'] }}" data-rate="{{ $c['rate'] }}" data-flag="{{ $c['country_code'] }}">
                                    {{ $c['code'] }} - {{ $c['country'] }} ({{ $c['name'] }})
                                </option>
                            @endforeach
                        </select>

                        <!-- Trigger Button -->
                        <button type="button" id="currency-from-btn" onclick="toggleCurrencyDropdown('from')"
                            class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-900 flex items-center justify-between gap-1.5 focus:outline-none focus:border-teal-600 shadow-2xs hover:bg-slate-50 transition-colors">
                            <span class="flex items-center gap-2 truncate">
                                <span id="currency-from-flag" class="fi fi-id fis shrink-0 rounded-xs" style="width: 18px; height: 13px;"></span>
                                <span id="currency-from-label" class="font-mono text-slate-900 truncate">IDR - Indonesia (Rupiah)</span>
                            </span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="currency-from-menu" class="hidden absolute left-0 top-full mt-1 w-72 sm:w-80 max-h-64 bg-white rounded-xl shadow-xl border border-slate-200 z-50 flex flex-col overflow-hidden animate-in fade-in duration-150">
                            <!-- Search box -->
                            <div class="p-2 border-b border-slate-100 bg-slate-50/80 sticky top-0 z-10">
                                <div class="relative">
                                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                                    <input type="text" id="currency-from-search" placeholder="Cari negara / valas..."
                                        oninput="filterCurrencyList(this, 'from')"
                                        class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-teal-500">
                                </div>
                            </div>
                            <!-- Options List -->
                            <div class="overflow-y-auto divide-y divide-slate-100 py-1" id="currency-from-list" style="max-height: 200px;">
                                <button type="button" onclick="selectCurrencyCustom('from', 'IDR', 'id', 'IDR - Indonesia (Rupiah)', 'Rp', 1)"
                                    class="currency-item w-full px-3 py-2 text-left hover:bg-teal-50/80 flex items-center gap-2.5 text-xs text-slate-800 transition-colors"
                                    data-search="idr indonesia rupiah">
                                    <span class="fi fi-id fis shrink-0 rounded-xs" style="width: 18px; height: 13px;"></span>
                                    <span class="font-bold font-mono text-slate-900">IDR</span>
                                    <span class="text-slate-500 truncate text-[11px]">- Indonesia (Rupiah)</span>
                                </button>
                                @foreach($currencyData['all_currencies'] as $c)
                                    <button type="button" onclick="selectCurrencyCustom('from', '{{ $c['code'] }}', '{{ $c['country_code'] }}', '{{ $c['code'] }} - {{ $c['country'] }} ({{ $c['name'] }})', '{{ $c['symbol'] }}', {{ $c['rate'] }})"
                                        class="currency-item w-full px-3 py-2 text-left hover:bg-teal-50/80 flex items-center gap-2.5 text-xs text-slate-800 transition-colors"
                                        data-search="{{ strtolower($c['code'].' '.$c['country'].' '.$c['name']) }}">
                                        <span class="fi fi-{{ $c['country_code'] }} fis shrink-0 rounded-xs" style="width: 18px; height: 13px;"></span>
                                        <span class="font-bold font-mono text-slate-900">{{ $c['code'] }}</span>
                                        <span class="text-slate-500 truncate text-[11px]">- {{ $c['country'] }} ({{ $c['name'] }})</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <input type="number" id="amount-input" value="10000000" min="0" step="any" oninput="runConverter()"
                            class="w-full pl-3 pr-3 py-2 rounded-lg border border-slate-200 bg-white font-mono text-xs font-bold text-slate-900 focus:outline-none focus:border-teal-600">
                    </div>
                </div>
            </div>

            <!-- Swap Button -->
            <div class="lg:col-span-2 flex justify-center">
                <button type="button" onclick="swapCurrencies()" class="p-2.5 rounded-full bg-teal-50 hover:bg-teal-100 border border-teal-200 text-teal-700 transition-all shadow-2xs hover:rotate-180 duration-300" title="Tukar">
                    <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- To Currency & Result -->
            <div class="lg:col-span-5 p-3.5 rounded-xl bg-teal-50/60 border border-teal-200 space-y-2">
                <span class="text-xs font-semibold text-teal-900 block">Ke</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 items-center">
                    <!-- Custom Currency Dropdown (To) -->
                    <div class="relative" id="currency-to-container">
                        <!-- Hidden select for compatibility -->
                        <select id="currency-to" onchange="runConverter()" class="hidden">
                            @foreach($currencyData['all_currencies'] as $c)
                                <option value="{{ $c['code'] }}" data-symbol="{{ $c['symbol'] }}" data-rate="{{ $c['rate'] }}" data-flag="{{ $c['country_code'] }}" {{ $c['code'] === 'NGN' ? 'selected' : '' }}>
                                    {{ $c['code'] }} - {{ $c['country'] }} ({{ $c['name'] }})
                                </option>
                            @endforeach
                            <option value="IDR" data-symbol="Rp" data-rate="1" data-flag="id">IDR - Indonesia (Rupiah)</option>
                        </select>

                        <!-- Trigger Button -->
                        <button type="button" id="currency-to-btn" onclick="toggleCurrencyDropdown('to')"
                            class="w-full px-3 py-2 rounded-lg border border-teal-200 bg-white text-xs font-semibold text-slate-900 flex items-center justify-between gap-1.5 focus:outline-none focus:border-teal-600 shadow-2xs hover:bg-slate-50 transition-colors">
                            <span class="flex items-center gap-2 truncate">
                                <span id="currency-to-flag" class="fi fi-ng fis shrink-0 rounded-xs" style="width: 18px; height: 13px;"></span>
                                <span id="currency-to-label" class="font-mono text-slate-900 truncate">NGN - Nigeria (Nigerian Naira)</span>
                            </span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="currency-to-menu" class="hidden absolute left-0 top-full mt-1 w-72 sm:w-80 max-h-64 bg-white rounded-xl shadow-xl border border-slate-200 z-50 flex flex-col overflow-hidden animate-in fade-in duration-150">
                            <!-- Search box -->
                            <div class="p-2 border-b border-slate-100 bg-slate-50/80 sticky top-0 z-10">
                                <div class="relative">
                                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                                    <input type="text" id="currency-to-search" placeholder="Cari negara / valas..."
                                        oninput="filterCurrencyList(this, 'to')"
                                        class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-teal-500">
                                </div>
                            </div>
                            <!-- Options List -->
                            <div class="overflow-y-auto divide-y divide-slate-100 py-1" id="currency-to-list" style="max-height: 200px;">
                                @foreach($currencyData['all_currencies'] as $c)
                                    <button type="button" onclick="selectCurrencyCustom('to', '{{ $c['code'] }}', '{{ $c['country_code'] }}', '{{ $c['code'] }} - {{ $c['country'] }} ({{ $c['name'] }})', '{{ $c['symbol'] }}', {{ $c['rate'] }})"
                                        class="currency-item w-full px-3 py-2 text-left hover:bg-teal-50/80 flex items-center gap-2.5 text-xs text-slate-800 transition-colors"
                                        data-search="{{ strtolower($c['code'].' '.$c['country'].' '.$c['name']) }}">
                                        <span class="fi fi-{{ $c['country_code'] }} fis shrink-0 rounded-xs" style="width: 18px; height: 13px;"></span>
                                        <span class="font-bold font-mono text-slate-900">{{ $c['code'] }}</span>
                                        <span class="text-slate-500 truncate text-[11px]">- {{ $c['country'] }} ({{ $c['name'] }})</span>
                                    </button>
                                @endforeach
                                <button type="button" onclick="selectCurrencyCustom('to', 'IDR', 'id', 'IDR - Indonesia (Rupiah)', 'Rp', 1)"
                                    class="currency-item w-full px-3 py-2 text-left hover:bg-teal-50/80 flex items-center gap-2.5 text-xs text-slate-800 transition-colors"
                                    data-search="idr indonesia rupiah">
                                    <span class="fi fi-id fis shrink-0 rounded-xs" style="width: 18px; height: 13px;"></span>
                                    <span class="font-bold font-mono text-slate-900">IDR</span>
                                    <span class="text-slate-500 truncate text-[11px]">- Indonesia (Rupiah)</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-2 rounded-lg bg-white border border-teal-200 flex items-center justify-between gap-2 overflow-hidden shadow-2xs">
                        <span id="converted-flag" class="fi fi-ng fis shrink-0 leading-none pl-1" style="font-size: 24px; border-radius: 3px;"></span>
                        <div id="converted-result" class="text-sm font-black font-mono text-teal-950 truncate text-right">
                            ₦ 743.700 NGN
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Rate Info Banner & Quick Hub Chips -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
            <div id="conversion-formula" class="font-mono text-slate-600 text-[11px]">
                1 IDR = 0.07437 NGN
            </div>

            <!-- Quick Currency Selection Chips -->
            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                <button type="button" onclick="setQuickCurrency('NGN')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-medium inline-flex items-center gap-1"><span class="fi fi-ng fis" style="font-size:12px;border-radius:1px;"></span> NGN</button>
                <button type="button" onclick="setQuickCurrency('USD')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-medium inline-flex items-center gap-1"><span class="fi fi-us fis" style="font-size:12px;border-radius:1px;"></span> USD</button>
                <button type="button" onclick="setQuickCurrency('AED')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-medium inline-flex items-center gap-1"><span class="fi fi-ae fis" style="font-size:12px;border-radius:1px;"></span> AED</button>
                <button type="button" onclick="setQuickCurrency('EUR')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-medium inline-flex items-center gap-1"><span class="fi fi-eu fis" style="font-size:12px;border-radius:1px;"></span> EUR</button>
                <button type="button" onclick="setQuickCurrency('SAR')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-medium inline-flex items-center gap-1"><span class="fi fi-sa fis" style="font-size:12px;border-radius:1px;"></span> SAR</button>
                <button type="button" onclick="setQuickCurrency('KES')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono font-medium inline-flex items-center gap-1"><span class="fi fi-ke fis" style="font-size:12px;border-radius:1px;"></span> KES</button>
            </div>
        </div>
    </div>

    <!-- 3. WORLD CLOCK -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs p-5 space-y-3" id="world-clock-section">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-100 shrink-0">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Jam Dunia</h3>
            </div>
            <span class="text-[11px] font-mono text-slate-400">Live</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-3">
            @foreach($globalTimeHubs as $h)
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/90 hover:border-teal-400 hover:bg-teal-50/20 transition-all space-y-1 group">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-800 flex items-center gap-1.5 truncate">
                        <span class="fi fi-{{ $h['country_code'] ?? 'id' }} fis shrink-0 rounded-xs" style="width: 16px; height: 12px;"></span>
                        <span>{{ $h['city'] }}</span>
                    </span>
                    <span class="font-mono text-[9px] px-1 py-0.2 rounded bg-slate-200/70 text-slate-600 font-bold">
                        {{ $h['code'] }}
                    </span>
                </div>
                <div class="flex items-baseline justify-between pt-0.5">
                    <div class="font-mono font-black text-base text-slate-900 group-hover:text-teal-700 tracking-tight transition-colors">
                        {{ $h['time_short'] }}
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 font-semibold">{{ $h['offset'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- 4. AIR CARGO RADAR & MARITIME SEAPORTS -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden" id="armada-pelabuhan-section">
        <div class="p-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-100 shrink-0">
                    <i data-lucide="plane-takeoff" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Armada &amp; Pelabuhan</h3>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="switchTelemetryTab('aviation')" id="tab-btn-aviation" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold bg-teal-600 text-white shadow-2xs border border-teal-700 transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>✈️ Penerbangan ({{ count($liveFlights) }})</span>
                </button>
                <button type="button" onclick="switchTelemetryTab('seaports')" id="tab-btn-seaports" 
                    class="px-3 py-1.5 rounded-lg text-xs font-medium bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>⚓ Pelabuhan ({{ count($majorSeaports) }})</span>
                </button>
            </div>
        </div>

        <!-- AVIATION TAB CONTENT -->
        <div id="tab-content-aviation" class="p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($liveFlights as $flight)
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2 hover:border-teal-400 hover:shadow-xs transition-all">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-200/80">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm">✈️</span>
                            <span class="font-mono font-bold text-xs text-slate-900">{{ $flight['flight_number'] }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">
                            {{ $flight['status'] }}
                        </span>
                    </div>

                    <div class="space-y-0.5 text-xs">
                        <div class="text-[11px] font-semibold text-slate-800 truncate">{{ $flight['carrier'] }}</div>
                        <div class="flex items-center justify-between text-xs font-bold text-slate-900 pt-0.5">
                            <span>{{ $flight['origin'] }}</span>
                            <span class="text-teal-600">&rarr;</span>
                            <span>{{ $flight['destination'] }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-1.5 pt-1.5 border-t border-slate-200/80 text-[10px] font-mono text-center">
                        <div class="bg-white p-1 rounded border border-slate-200">
                            <span class="text-slate-400 block text-[8px]">ALT</span>
                            <b class="text-slate-800">{{ number_format($flight['altitude_ft']) }}ft</b>
                        </div>
                        <div class="bg-white p-1 rounded border border-slate-200">
                            <span class="text-slate-400 block text-[8px]">SPD</span>
                            <b class="text-slate-800">{{ $flight['speed_knots'] }}kts</b>
                        </div>
                        <div class="bg-teal-50 p-1 rounded border border-teal-200">
                            <span class="text-teal-700 block text-[8px]">SUHU</span>
                            <b class="text-teal-900">{{ $flight['temp_sensor'] }}&deg;C</b>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- SEAPORTS TAB CONTENT -->
        <div id="tab-content-seaports" class="p-5 hidden space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold">
                        <tr>
                            <th class="py-2.5 px-3">LOCODE</th>
                            <th class="py-2.5 px-3">Pelabuhan</th>
                            <th class="py-2.5 px-3">Negara</th>
                            <th class="py-2.5 px-3 text-center">Reefer</th>
                            <th class="py-2.5 px-3 text-center">Kedalaman</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($majorSeaports as $port)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 font-mono font-bold text-teal-800">
                                ⚓ {{ $port['locode'] }}
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-slate-900">
                                {{ $port['name'] }}
                            </td>
                            <td class="py-2.5 px-3">
                                <span>{{ $port['flag'] }} {{ $port['country'] }}</span>
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-emerald-800">
                                {{ number_format($port['reefer_plugs']) }} plugs
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono text-slate-600">
                                {{ $port['water_depth_m'] }} m
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                @if($port['status'] === 'OPERATIONAL')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">OPERASIONAL</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">PADAT</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 5. ROUTING CALCULATOR -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs p-5 space-y-4" id="routing-section">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-100 shrink-0">
                    <i data-lucide="navigation" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Kalkulator Rute</h3>
            </div>
            <span class="text-[11px] font-mono text-slate-400">OSRM</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Asal</label>
                <select id="route-calc-origin" class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 font-mono text-xs text-slate-900 focus:outline-none focus:border-teal-600">
                    <option value="-6.9006,107.5761" selected>🇮🇩 Bio Farma Bandung</option>
                    <option value="-6.1256,106.6558">🇮🇩 Bandara Soetta Jakarta</option>
                    <option value="-6.1017,106.8833">🇮🇩 Pelabuhan Tanjung Priok</option>
                    <option value="25.2532,55.3657">🇦🇪 Bandara Dubai DXB</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Tujuan</label>
                <select id="route-calc-dest" class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 font-mono text-xs text-slate-900 focus:outline-none focus:border-teal-600">
                    <option value="-6.1783,106.6319">🇮🇩 RSUD Tangerang</option>
                    <option value="25.2532,55.3657" selected>🇦🇪 Bandara Dubai DXB</option>
                    <option value="9.0068,7.2632">🇳🇬 NPHCDA Abuja</option>
                    <option value="51.9547,4.1378">🇳🇱 Pelabuhan Rotterdam</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-700 block mb-1">Moda</label>
                <select id="route-calc-mode" class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 font-mono text-xs font-semibold text-slate-900 focus:outline-none focus:border-teal-600">
                    <option value="air" selected>✈️ Udara</option>
                    <option value="land">🚚 Darat</option>
                    <option value="sea">🚢 Laut</option>
                </select>
            </div>

            <div>
                <button type="button" onclick="calculateInteractiveRoute()" id="btn-calc-route" 
                    class="w-full py-2 px-3 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="navigation" class="w-4 h-4"></i>
                    <span>Hitung Rute &amp; Navigasi</span>
                </button>
            </div>
        </div>

        <!-- Result Box -->
        <div id="route-calc-result" class="p-3.5 rounded-xl bg-teal-50/70 border border-teal-200 hidden flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
            <div class="space-y-0.5">
                <span class="text-[10px] font-bold text-teal-800 uppercase tracking-wider block">Hasil Navigasi Rute</span>
                <div id="route-calc-summary" class="font-bold text-slate-900 text-sm">--</div>
                <div id="route-calc-mode-label" class="text-[11px] text-slate-600">--</div>
            </div>
            <div class="flex items-center gap-4 text-right">
                <div>
                    <span class="text-[10px] text-slate-500 block">Jarak Tempuh</span>
                    <span id="route-calc-distance" class="font-mono font-bold text-base text-teal-900">-- km</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-500 block">Estimasi Durasi</span>
                    <span id="route-calc-duration" class="font-mono font-bold text-base text-slate-900">--</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. SIMPLIFIED CONTINENTAL INTELLIGENCE -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs p-5 space-y-4" id="news-section">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-100 shrink-0">
                    <i data-lucide="newspaper" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Berita &amp; Peringatan Logistik PBB</h3>
            </div>

            <!-- Continent Filter Dropdown -->
            <div class="flex items-center gap-2">
                <select id="continent-filter" onchange="filterNews(this.value)" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-800 focus:outline-none focus:border-teal-600">
                    <option value="all">Semua Benua (Global)</option>
                    <option value="Asia">Asia 🇮🇩🇸🇬</option>
                    <option value="Middle East">Timur Tengah 🇦🇪🇶🇦</option>
                    <option value="Africa">Afrika 🇳🇬🇰🇪</option>
                    <option value="Europe">Eropa 🇩🇰🇨🇭</option>
                    <option value="Americas">Amerika 🇺🇸🇧🇷</option>
                </select>
            </div>
        </div>

        <!-- News Cards Grid (Clean & Minimal) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" id="radar-news-container">
            @foreach($continentalNews as $continentKey => $newsList)
                @foreach($newsList as $news)
                <div class="radar-news-card p-3.5 rounded-xl bg-slate-50 border border-slate-200 hover:border-teal-400 hover:shadow-xs transition-all flex flex-col justify-between space-y-2" data-continent="{{ $continentKey }}">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-slate-700 flex items-center gap-1">
                                <span>{{ $news['flag'] }}</span>
                                <span>{{ $news['country'] }}</span>
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold border {{ $news['badge_color'] }}">
                                {{ $news['category'] }}
                            </span>
                        </div>

                        <h4 class="text-xs font-bold text-slate-900 leading-snug line-clamp-2">
                            {{ $news['title'] }}
                        </h4>
                    </div>

                    <div class="pt-1.5 border-t border-slate-200/60 flex items-center justify-between text-[10px] text-slate-400">
                        <span>{{ $news['source'] }}</span>
                        <a href="{{ $news['url'] }}" target="_blank" rel="noopener noreferrer" class="text-teal-700 hover:text-teal-900 font-bold inline-flex items-center gap-0.5">
                            <span>Baca &rarr;</span>
                        </a>
                    </div>
                </div>
                @endforeach
            @endforeach
        </div>
    </div>

</div>

@push('scripts')
<script>
    // 1. DATA REPOSITORIES
    const weatherHubs = @json($globalWeatherHubs);
    const currencyRates = @json($currencyData['rates']);

    let weatherMap;
    let weatherMarkers = [];

    document.addEventListener("DOMContentLoaded", function () {
        initWeatherMap();
        runConverter();

        if (window.location.hash) {
            const targetId = window.location.hash.substring(1);
            setTimeout(() => {
                scrollToRadarSection(targetId);
            }, 300);
        }
    });

    window.addEventListener('hashchange', function() {
        if (window.location.hash) {
            scrollToRadarSection(window.location.hash.substring(1));
        }
    });

    function scrollToRadarSection(sectionId) {
        const el = document.getElementById(sectionId);
        const main = document.querySelector('main');
        if (el && main) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            if (sectionId === 'peta-cuaca-section' && weatherMap) {
                setTimeout(() => { weatherMap.invalidateSize(); }, 350);
            }
        }
    }

    // 2. CONVERTER FUNCTIONS
    function toggleCurrencyDropdown(type) {
        const menu = document.getElementById(`currency-${type}-menu`);
        const otherType = type === 'from' ? 'to' : 'from';
        const otherMenu = document.getElementById(`currency-${otherType}-menu`);
        if (otherMenu) otherMenu.classList.add('hidden');

        if (menu) {
            menu.classList.toggle('hidden');
            if (!menu.classList.contains('hidden')) {
                const searchInput = document.getElementById(`currency-${type}-search`);
                if (searchInput) {
                    searchInput.value = '';
                    filterCurrencyList(searchInput, type);
                    setTimeout(() => searchInput.focus(), 60);
                }
                if (window.lucide) { lucide.createIcons(); }
            }
        }
    }

    function filterCurrencyList(input, type) {
        const q = input.value.toLowerCase().trim();
        const list = document.getElementById(`currency-${type}-list`);
        if (!list) return;
        const items = list.querySelectorAll('.currency-item');
        items.forEach(item => {
            const text = item.getAttribute('data-search') || '';
            if (!q || text.includes(q)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function selectCurrencyCustom(type, code, countryCode, label, symbol, rate) {
        const sel = document.getElementById(`currency-${type}`);
        if (sel) {
            sel.value = code;
        }

        const flagEl = document.getElementById(`currency-${type}-flag`);
        if (flagEl) {
            flagEl.className = `fi fi-${countryCode} fis shrink-0 rounded-xs`;
        }

        const labelEl = document.getElementById(`currency-${type}-label`);
        if (labelEl) {
            labelEl.textContent = label;
        }

        const menu = document.getElementById(`currency-${type}-menu`);
        if (menu) {
            menu.classList.add('hidden');
        }

        runConverter();
    }

    function updateDropdownUI(type, code) {
        const sel = document.getElementById(`currency-${type}`);
        if (!sel) return;
        const opt = sel.querySelector(`option[value="${code}"]`);
        if (!opt) return;

        const flagCode = opt.getAttribute('data-flag') || 'id';
        const text = opt.textContent.trim();

        const flagEl = document.getElementById(`currency-${type}-flag`);
        if (flagEl) {
            flagEl.className = `fi fi-${flagCode} fis shrink-0 rounded-xs`;
        }

        const labelEl = document.getElementById(`currency-${type}-label`);
        if (labelEl) {
            labelEl.textContent = text;
        }
    }

    // Close custom dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const fromContainer = document.getElementById('currency-from-container');
        const toContainer = document.getElementById('currency-to-container');
        if (fromContainer && !fromContainer.contains(e.target)) {
            document.getElementById('currency-from-menu')?.classList.add('hidden');
        }
        if (toContainer && !toContainer.contains(e.target)) {
            document.getElementById('currency-to-menu')?.classList.add('hidden');
        }
    });

    function runConverter() {
        const amount = parseFloat(document.getElementById('amount-input').value) || 0;
        const fromCode = document.getElementById('currency-from').value;
        const toCode = document.getElementById('currency-to').value;

        const fromRate = fromCode === 'IDR' ? 1.0 : (currencyRates[fromCode] || 1.0);
        const toRate = toCode === 'IDR' ? 1.0 : (currencyRates[toCode] || 1.0);

        // Convert via IDR base
        let inIdr = fromCode === 'IDR' ? amount : (fromRate > 0 ? (amount / fromRate) : amount);
        let converted = toCode === 'IDR' ? inIdr : (inIdr * toRate);

        const fromOption = document.getElementById('currency-from').querySelector(`option[value="${fromCode}"]`);
        const fromFlagCode = fromOption ? (fromOption.getAttribute('data-flag') || 'id') : 'id';

        const toOption = document.getElementById('currency-to').querySelector(`option[value="${toCode}"]`);
        const toSymbol = toOption ? toOption.getAttribute('data-symbol') : '';
        const toFlagCode = toOption ? (toOption.getAttribute('data-flag') || 'id') : 'id';

        // Display flag icon using flag-icons CSS
        const flagEl = document.getElementById('converted-flag');
        if (flagEl && toFlagCode) {
            flagEl.className = `fi fi-${toFlagCode} fis shrink-0 leading-none pl-1`;
            flagEl.style.fontSize = '24px';
            flagEl.style.borderRadius = '3px';
        }

        // Display
        const resEl = document.getElementById('converted-result');
        if (resEl) {
            resEl.textContent = toSymbol + ' ' + converted.toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' ' + toCode;
        }

        // Formula with Country Flag Icons
        const formulaEl = document.getElementById('conversion-formula');
        if (formulaEl) {
            const oneUnitTo = toRate > 0 ? (fromRate / toRate) : 0;
            const oneUnitFrom = fromRate > 0 ? (toRate / fromRate) : 0;
            formulaEl.innerHTML = `Kurs Acuan: <span class="fi fi-${fromFlagCode} fis" style="font-size:12px;border-radius:1px;"></span> 1 ${fromCode} = <span class="fi fi-${toFlagCode} fis" style="font-size:12px;border-radius:1px;"></span> ${oneUnitFrom.toFixed(4)} ${toCode} | <span class="fi fi-${toFlagCode} fis" style="font-size:12px;border-radius:1px;"></span> 1 ${toCode} = <span class="fi fi-${fromFlagCode} fis" style="font-size:12px;border-radius:1px;"></span> ${oneUnitTo.toFixed(4)} ${fromCode}`;
        }
    }

    function swapCurrencies() {
        const fromSel = document.getElementById('currency-from');
        const toSel = document.getElementById('currency-to');

        const temp = fromSel.value;
        fromSel.value = toSel.value;
        toSel.value = temp;

        updateDropdownUI('from', fromSel.value);
        updateDropdownUI('to', toSel.value);

        runConverter();
    }

    function setQuickCurrency(code) {
        const toSel = document.getElementById('currency-to');
        toSel.value = code;
        updateDropdownUI('to', code);
        runConverter();
    }

    // 3. WEATHER MAP & SELECTION FUNCTIONS
    function initWeatherMap() {
        weatherMap = L.map('weather-interactive-map', {
            zoomControl: true,
            attributionControl: false,
            dragging: true,
            touchZoom: true,
            scrollWheelZoom: true,
            doubleClickZoom: true,
            boxZoom: true,
            tap: false,
            keyboard: true,
            inertia: true,
            inertiaDeceleration: 3000
        }).setView([22.0, 15.0], 2);

        weatherMap.dragging.enable();
        if (weatherMap.keyboard) weatherMap.keyboard.enable();

        const esriSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: 'Tiles &copy; Esri'
        });
        const esriLabels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: 'Labels &copy; Esri'
        });
        L.layerGroup([esriSatellite, esriLabels]).addTo(weatherMap);

        setTimeout(() => { weatherMap.invalidateSize(); }, 300);
        window.addEventListener('resize', () => { weatherMap.invalidateSize(); });

        weatherHubs.forEach((hub, index) => {
            const markerColor = hub.risk_level === 'CRITICAL_HEAT' ? '#ef4444' : 
                               (hub.risk_level === 'ELEVATED_HEAT' ? '#f59e0b' : 
                               (hub.risk_level === 'FREEZE_RISK' ? '#06b6d4' : '#0d9488'));

            const emoji = hub.emoji || '⛅';

            // Custom HTML Weather Icon Marker directly visible on map
            const customIcon = L.divIcon({
                className: 'custom-weather-marker',
                html: `<div class="cursor-pointer flex items-center gap-1 px-2 py-0.5 rounded-full text-white font-mono font-bold text-[11px] shadow-md border-2 border-white hover:scale-115 transition-transform" style="background:${markerColor}; box-shadow:0 3px 6px rgba(0,0,0,0.3); white-space:nowrap;">
                        <span class="text-xs">${emoji}</span>
                        <span>${hub.flag}</span>
                        <span>${Math.round(hub.temp)}°C</span>
                       </div>`,
                iconSize: [68, 26],
                iconAnchor: [34, 13]
            });

            const marker = L.marker([hub.lat, hub.lon], { icon: customIcon }).addTo(weatherMap);

            marker.bindPopup(`
                <div style="font-family:'Inter',sans-serif; font-size:11px; padding:3px; min-width:210px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px; padding-bottom:4px; border-bottom:1px solid #e2e8f0;">
                        <strong style="font-size:13px; color:#0f172a;">${hub.flag} ${hub.country}</strong>
                        <span style="font-size:10px; font-weight:bold; padding:1px 5px; border-radius:4px; background:#f1f5f9; color:#475569;">${hub.hub_code}</span>
                    </div>
                    <div style="font-size:11px; color:#64748b; margin-bottom:4px;">${hub.city}</div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin:6px 0; background:#f8fafc; padding:6px; border-radius:8px;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span style="font-size:22px;">${emoji}</span>
                            <div>
                                <div style="font-size:17px; font-weight:800; color:#0f172a;">${Math.round(hub.temp * 10) / 10}°C</div>
                                <div style="font-size:10px; color:#0d9488; font-weight:600;">${hub.condition}</div>
                            </div>
                        </div>
                        <div style="text-align:right; font-size:10px; color:#64748b; line-height:1.4;">
                            <div>💧 <b>${hub.humidity}%</b></div>
                            <div>💨 <b>${Math.round(hub.wind_speed)} km/h</b></div>
                        </div>
                    </div>
                    <div style="margin-top:6px; padding:5px 7px; background:#f0fdfa; border:1px solid #ccfbf1; border-radius:6px; font-size:10px; color:#0f766e; line-height:1.35;">
                        <b>SOP Rantai Dingin:</b> ${hub.cold_chain_guidance}
                    </div>
                </div>
            `, { className: 'cargopharma-popup' });

            marker.on('click', function() {
                openWeatherPanel(index);
                const sel = document.getElementById('airport-weather-selector');
                if (sel) sel.value = index;
            });

            weatherMarkers.push(marker);
        });

        // Initially: detail panel remains hidden, world map is fully visible!
    }

    window.openWeatherPanel = function(index) {
        updateWeatherPanel(index);
        const panel = document.getElementById('weather-detail-panel');
        const hint = document.getElementById('weather-map-hint');
        if (panel) panel.classList.remove('hidden');
        if (hint) hint.classList.add('hidden');
        if (window.lucide) lucide.createIcons();
    };

    window.closeWeatherPanel = function() {
        const panel = document.getElementById('weather-detail-panel');
        const hint = document.getElementById('weather-map-hint');
        if (panel) panel.classList.add('hidden');
        if (hint) hint.classList.remove('hidden');
        const sel = document.getElementById('airport-weather-selector');
        if (sel) sel.value = '';
        if (weatherMap) weatherMap.closePopup();
    };

    window.panWeatherMap = function(dx, dy) {
        if (weatherMap) {
            weatherMap.panBy([dx, dy], { animate: true, duration: 0.35 });
        }
    };

    window.resetWeatherMapCenter = function() {
        if (weatherMap) {
            weatherMap.setView([22.0, 15.0], 2, { animate: true });
            closeWeatherPanel();
        }
    };

    window.focusAirportOnMap = function(index) {
        if (index === '' || index === null || typeof index === 'undefined') {
            closeWeatherPanel();
            return;
        }
        const hub = weatherHubs[index];
        if (!hub || !weatherMap) return;

        weatherMap.flyTo([hub.lat, hub.lon], 6, { duration: 1.2 });
        if (weatherMarkers[index]) {
            setTimeout(() => {
                weatherMarkers[index].openPopup();
            }, 300);
        }

        openWeatherPanel(index);
    };

    function updateWeatherPanel(index) {
        const hub = weatherHubs[index];
        if (!hub) return;

        document.getElementById('panel-flag').textContent = hub.flag;
        document.getElementById('panel-hub-badge').textContent = hub.hub_code;
        document.getElementById('panel-country').textContent = hub.country;
        document.getElementById('panel-city').textContent = hub.city;
        document.getElementById('panel-temp').textContent = `${Math.round(hub.temp * 10) / 10}°C`;
        document.getElementById('panel-condition').textContent = hub.condition;
        document.getElementById('panel-feels').textContent = `${Math.round(hub.feels_like * 10) / 10}°C`;
        document.getElementById('panel-wind').textContent = `${Math.round(hub.wind_speed * 10) / 10} km/h`;
        document.getElementById('panel-humidity').textContent = `${hub.humidity}%`;
        document.getElementById('panel-continent').textContent = hub.continent;
        document.getElementById('panel-weather-emoji').textContent = hub.emoji || '⛅';

        const riskBox = document.getElementById('panel-risk-box');
        riskBox.className = `p-3 rounded-xl border bg-white space-y-1.5 text-xs shadow-2xs ${hub.risk_badge}`;
        document.getElementById('panel-risk-desc').textContent = hub.cold_chain_guidance;

        if (window.lucide) lucide.createIcons();
    }

    window.filterWeatherContinent = function(continent) {
        document.querySelectorAll('.wx-tab-btn').forEach(btn => {
            btn.classList.remove('bg-teal-600', 'text-white', 'shadow-xs');
            btn.classList.add('text-slate-700');
        });
        const activeBtn = document.getElementById('wx-btn-' + (continent.replace(/\s+/g, '-')));
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-700');
            activeBtn.classList.add('bg-teal-600', 'text-white', 'shadow-xs');
        }

        const continentBounds = [];

        weatherHubs.forEach((hub, idx) => {
            const matches = (continent === 'ALL' || hub.continent === continent);
            const marker = weatherMarkers[idx];
            if (marker) {
                if (matches) {
                    marker.setOpacity(1.0);
                    continentBounds.push([hub.lat, hub.lon]);
                } else {
                    marker.setOpacity(0.12);
                }
            }
        });

        // Filter selector dropdown options & optgroups
        const sel = document.getElementById('airport-weather-selector');
        if (sel) {
            const optgroups = sel.querySelectorAll('optgroup');
            optgroups.forEach(og => {
                const options = og.querySelectorAll('option');
                let anyVisible = false;
                options.forEach(opt => {
                    const optContinent = opt.getAttribute('data-continent');
                    const show = (continent === 'ALL' || optContinent === continent);
                    opt.hidden = !show;
                    if (show) anyVisible = true;
                });
                og.hidden = !anyVisible;
            });
        }

        if (continentBounds.length > 0 && weatherMap) {
            weatherMap.fitBounds(L.latLngBounds(continentBounds), { padding: [50, 50], maxZoom: 5 });
        }
    };

    window.resetWeatherMap = function() {
        filterWeatherContinent('ALL');
        closeWeatherPanel();
        weatherMap.setView([22.0, 15.0], 2);
    };

    // 4. CONTINENTAL NEWS FILTER FUNCTION
    function filterNews(continent) {
        const cards = document.querySelectorAll('#radar-news-container .radar-news-card');
        cards.forEach(card => {
            const cardContinent = card.getAttribute('data-continent');
            if (continent === 'all' || cardContinent === continent) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // 5. TELEMETRY TAB SWITCHER (AVIATION / SEAPORTS)
    function switchTelemetryTab(tab) {
        const btnAv = document.getElementById('tab-btn-aviation');
        const btnSea = document.getElementById('tab-btn-seaports');
        const contentAv = document.getElementById('tab-content-aviation');
        const contentSea = document.getElementById('tab-content-seaports');

        if (tab === 'aviation') {
            btnAv.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-teal-600 text-white shadow-2xs border border-teal-700 transition-all flex items-center gap-1.5 cursor-pointer';
            btnSea.className = 'px-3 py-1.5 rounded-lg text-xs font-medium bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer';
            contentAv.classList.remove('hidden');
            contentSea.classList.add('hidden');
        } else {
            btnSea.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-teal-600 text-white shadow-2xs border border-teal-700 transition-all flex items-center gap-1.5 cursor-pointer';
            btnAv.className = 'px-3 py-1.5 rounded-lg text-xs font-medium bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer';
            contentSea.classList.remove('hidden');
            contentAv.classList.add('hidden');
        }
    }

    // 6. INTERACTIVE ROUTE CALCULATOR
    async function calculateInteractiveRoute() {
        const originVal = document.getElementById('route-calc-origin').value.split(',');
        const destVal = document.getElementById('route-calc-dest').value.split(',');
        const mode = document.getElementById('route-calc-mode').value;
        const btn = document.getElementById('btn-calc-route');
        const resBox = document.getElementById('route-calc-result');

        btn.disabled = true;
        btn.innerHTML = `<span class="animate-spin mr-1">🔄</span> Menghitung...`;

        try {
            const url = `{{ route('ajax.route') }}?lat1=${originVal[0]}&lng1=${originVal[1]}&lat2=${destVal[0]}&lng2=${destVal[1]}&mode=${mode}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.success && data.route) {
                const r = data.route;
                resBox.classList.remove('hidden');
                resBox.classList.add('flex');

                const originSel = document.getElementById('route-calc-origin');
                const destSel = document.getElementById('route-calc-dest');
                const originText = originSel.options[originSel.selectedIndex].text.split('(')[0];
                const destText = destSel.options[destSel.selectedIndex].text.split('(')[0];

                document.getElementById('route-calc-summary').textContent = `${originText} ➔ ${destText}`;
                
                let modeLabel = '';
                let durationText = '';
                if (mode === 'air') {
                    modeLabel = `✈️ Koridor Udara Global (Great-Circle Arc Navigation)`;
                    durationText = `~${r.flight_duration_hours || '--'} Jam`;
                } else if (mode === 'sea') {
                    modeLabel = `🚢 Koridor Pelayaran Maritim (Maritime Sea Lanes)`;
                    durationText = `~${r.transit_days || '--'} Hari`;
                } else {
                    modeLabel = `🚚 Rute Jalan Tol & Arteri (OSRM High-Speed Routing Engine)`;
                    const hours = Math.floor((r.duration_minutes || 0) / 60);
                    const mins = Math.round((r.duration_minutes || 0) % 60);
                    durationText = hours > 0 ? `${hours}j ${mins}m` : `${mins} menit`;
                }

                document.getElementById('route-calc-mode-label').textContent = modeLabel;
                document.getElementById('route-calc-distance').textContent = `${Number(r.distance_km || 0).toLocaleString('id-ID')} km`;
                document.getElementById('route-calc-duration').textContent = durationText;
            }
        } catch (err) {
            console.error('Route calculation failed:', err);
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i data-lucide="calculator" class="w-3.5 h-3.5 mr-1"></i> Hitung Rute & Navigasi`;
            lucide.createIcons();
        }
    }
</script>
@endpush
@endsection
