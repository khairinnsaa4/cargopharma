@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Global Logistics Radar</h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live Telemetry Feeds
                </span>
            </div>
            <p class="text-xs text-slate-500">Konversi mata uang dua arah, peta cuaca satelit bandara dunia, dan intelijen logistik antar benua.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 font-mono">Basis Data: <strong class="text-slate-800">Open-Meteo &amp; Live ER-API</strong></span>
        </div>
    </div>

    <!-- 1. SIMPLIFIED TWO-WAY CURRENCY CONVERTER -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-100 shrink-0">
                    <i data-lucide="coins" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Konverter Mata Uang Bebas Pilih (Dua Arah)</h3>
                    <p class="text-xs text-slate-500">Pilih negara asal dan negara tujuan untuk konversi instan</p>
                </div>
            </div>
            <span class="text-[11px] font-mono text-slate-400">Sinkronisasi: {{ $currencyData['last_updated'] ?? date('d M Y') }}</span>
        </div>

        <!-- Converter Controls Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
            <!-- From Currency & Amount (5 cols) -->
            <div class="lg:col-span-5 p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-semibold text-slate-700">Mata Uang Asal</label>
                    <span class="text-[10px] font-mono text-slate-400">Dari</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <select id="currency-from" onchange="runConverter()" class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-white font-mono text-xs font-semibold text-slate-900 focus:outline-none focus:border-teal-600">
                        <option value="IDR" data-symbol="Rp" data-rate="1" selected>🇮🇩 IDR - Rupiah Indonesia</option>
                        @foreach($currencyData['all_currencies'] as $c)
                            <option value="{{ $c['code'] }}" data-symbol="{{ $c['symbol'] }}" data-rate="{{ $c['rate'] }}">
                                {{ $c['flag'] }} {{ $c['code'] }} - {{ $c['name'] }}
                            </option>
                        @endforeach
                    </select>

                    <div class="relative">
                        <input type="number" id="amount-input" value="10000000" min="0" step="any" oninput="runConverter()"
                            class="w-full pl-3 pr-3 py-2 rounded-lg border border-slate-200 bg-white font-mono text-xs font-bold text-slate-900 focus:outline-none focus:border-teal-600">
                    </div>
                </div>
            </div>

            <!-- Swap Button (2 cols) -->
            <div class="lg:col-span-2 flex justify-center">
                <button type="button" onclick="swapCurrencies()" class="p-3 rounded-full bg-teal-50 hover:bg-teal-100 border border-teal-200 text-teal-700 transition-all shadow-2xs hover:rotate-180 duration-300" title="Tukar Mata Uang">
                    <i data-lucide="arrow-left-right" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- To Currency & Result (5 cols) -->
            <div class="lg:col-span-5 p-4 rounded-xl bg-teal-50/60 border border-teal-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-semibold text-teal-900">Mata Uang Tujuan</label>
                    <span class="text-[10px] font-mono text-teal-600">Hasil Konversi</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 items-center">
                    <select id="currency-to" onchange="runConverter()" class="w-full px-3 py-2 rounded-lg border border-teal-200 bg-white font-mono text-xs font-semibold text-slate-900 focus:outline-none focus:border-teal-600">
                        @foreach($currencyData['all_currencies'] as $c)
                            <option value="{{ $c['code'] }}" data-symbol="{{ $c['symbol'] }}" data-rate="{{ $c['rate'] }}" {{ $c['code'] === 'NGN' ? 'selected' : '' }}>
                                {{ $c['flag'] }} {{ $c['code'] }} - {{ $c['name'] }}
                            </option>
                        @endforeach
                        <option value="IDR" data-symbol="Rp" data-rate="1">🇮🇩 IDR - Rupiah Indonesia</option>
                    </select>

                    <div class="p-2 rounded-lg bg-white border border-teal-200 text-right overflow-hidden">
                        <div id="converted-result" class="text-sm font-black font-mono text-teal-950 truncate">
                            ₦ 743.700 NGN
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchange Rate Info Banner & Quick Hub Chips -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
            <div id="conversion-formula" class="font-mono text-slate-600 text-[11px]">
                Kurs Acuan: 1 IDR = 0.07437 NGN | 1 NGN = Rp 13,45
            </div>

            <!-- Quick Currency Selection Chips -->
            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                <span class="text-slate-400 mr-1">Tujuan Cepat:</span>
                <button type="button" onclick="setQuickCurrency('NGN')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono">🇳🇬 NGN</button>
                <button type="button" onclick="setQuickCurrency('USD')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono">🇺🇸 USD</button>
                <button type="button" onclick="setQuickCurrency('AED')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono">🇦🇪 AED</button>
                <button type="button" onclick="setQuickCurrency('EUR')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono">🇪🇺 EUR</button>
                <button type="button" onclick="setQuickCurrency('SAR')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono">🇸🇦 SAR</button>
                <button type="button" onclick="setQuickCurrency('KES')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono">🇰🇪 KES</button>
            </div>
        </div>
    </div>

    <!-- 2. INTERACTIVE GLOBAL AIRPORT WEATHER MAP -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <!-- Weather Map Header & Airport Selector -->
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-100 shrink-0">
                    <i data-lucide="cloud-sun" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Peta Radar Cuaca Real-Time Bandara Dunia</h3>
                    <p class="text-xs text-slate-500">Pilih bandara tujuan atau klik marker pada peta untuk melihat suhu dan mitigasi risiko rantai dingin</p>
                </div>
            </div>

            <!-- Airport Dropdown Selector -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-600 shrink-0">Pilih Bandara:</label>
                <select id="airport-weather-selector" onchange="focusAirportOnMap(this.value)" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-800 focus:outline-none focus:border-teal-600">
                    @foreach($globalWeatherHubs as $index => $wh)
                        <option value="{{ $index }}" {{ $wh['hub_code'] === 'DXB' ? 'selected' : '' }}>
                            {{ $wh['flag'] }} {{ $wh['hub_code'] }} - {{ $wh['city'] }} ({{ round($wh['temp'], 1) }}°C)
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Weather Map Container & Live Card -->
        <div class="grid grid-cols-1 lg:grid-cols-4 divide-y lg:divide-y-0 lg:divide-x divide-slate-100">
            <!-- Leaflet Map (3 cols) -->
            <div class="lg:col-span-3 h-[420px] relative">
                <div id="weather-interactive-map" class="w-full h-full z-0"></div>
            </div>

            <!-- Selected Airport Weather Card (1 col) -->
            <div class="p-5 flex flex-col justify-between space-y-4 bg-slate-50/50" id="weather-detail-panel">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span id="panel-flag" class="text-2xl">🇦🇪</span>
                        <span id="panel-hub-badge" class="px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-teal-100 text-teal-800 border border-teal-200">DXB</span>
                    </div>

                    <div>
                        <h4 id="panel-city" class="text-sm font-bold text-slate-900 leading-snug">Dubai (SkyPharma Hub)</h4>
                        <p id="panel-country" class="text-xs text-slate-500">Uni Emirat Arab &bull; Timur Tengah</p>
                    </div>

                    <!-- Temperature -->
                    <div class="p-3 rounded-xl bg-white border border-slate-200 shadow-2xs">
                        <div class="flex items-baseline justify-between">
                            <span class="text-3xl font-black text-slate-900 font-mono" id="panel-temp">30.7°C</span>
                            <span class="text-xs font-bold text-slate-700" id="panel-condition">Cerah</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-1 font-mono flex items-center justify-between">
                            <span>Sensasi: <strong class="text-slate-700" id="panel-feels">32°C</strong></span>
                            <span>Angin: <strong class="text-slate-700" id="panel-wind">14 km/h</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Cold-Chain Threat Guidance -->
                <div class="p-3 rounded-xl border bg-white space-y-1 text-xs" id="panel-risk-box">
                    <div class="flex items-center gap-1.5 font-bold text-slate-800 text-[11px]">
                        <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span id="panel-risk-title">Kondisi Termal Vaksin</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed" id="panel-risk-desc">
                        WASPADA PANAS: Paparan sinar matahari langsung di bandara dapat menaikkan suhu isolasi.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. SIMPLIFIED CONTINENTAL INTELLIGENCE -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-100 shrink-0">
                    <i data-lucide="newspaper" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Berita &amp; Regulasi Logistik Antar Benua</h3>
                    <p class="text-xs text-slate-500">Pemberitahuan resmi bea cukai, izin jalur kargo vaksin, dan respon wabah per benua</p>
                </div>
            </div>

            <!-- Continent Filter Dropdown -->
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-slate-600">Pilih Benua:</label>
                <select id="continent-filter" onchange="filterNews(this.value)" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-800 focus:outline-none focus:border-teal-600">
                    <option value="all">Semua Benua (Global)</option>
                    <option value="Asia">Asia 🇮🇩🇸🇬</option>
                    <option value="Middle East">Timur Tengah 🇦🇪🇶🇦</option>
                    <option value="Africa">Afrika 🇳🇬🇰🇪</option>
                    <option value="Europe">Eropa 🇩🇰🇨🇭</option>
                    <option value="Americas">Amerika 🇺🇸🇧🇷</option>
                </select>
            </div>
        </div>

        <!-- News Cards Grid (Simple & Digestible) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="radar-news-container">
            @foreach($continentalNews as $continentKey => $newsList)
                @foreach($newsList as $news)
                <div class="radar-news-card p-4 rounded-xl bg-slate-50 border border-slate-200 hover:border-teal-400 hover:shadow-xs transition-all space-y-2 flex flex-col justify-between" data-continent="{{ $continentKey }}">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-slate-700 flex items-center gap-1">
                                <span>{{ $news['flag'] }}</span>
                                <span>{{ $news['country'] }}</span>
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $news['badge_color'] }}">
                                {{ $news['category'] }}
                            </span>
                        </div>

                        <h4 class="text-xs font-bold text-slate-900 leading-snug line-clamp-2">
                            {{ $news['title'] }}
                        </h4>

                        <p class="text-[11px] text-slate-600 line-clamp-3 leading-relaxed">
                            {{ $news['summary'] }}
                        </p>
                    </div>

                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-[10px] text-slate-400">
                        <span>{{ $news['source'] }}</span>
                        <a href="{{ $news['url'] }}" target="_blank" rel="noopener noreferrer" class="text-teal-700 hover:text-teal-900 font-bold inline-flex items-center gap-1">
                            <span>Buka Rilis</span>
                            <i data-lucide="external-link" class="w-3 h-3"></i>
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
    });

    // 2. CONVERTER FUNCTIONS
    function runConverter() {
        const amount = parseFloat(document.getElementById('amount-input').value) || 0;
        const fromCode = document.getElementById('currency-from').value;
        const toCode = document.getElementById('currency-to').value;

        const fromRate = fromCode === 'IDR' ? 1.0 : (currencyRates[fromCode] || 1.0);
        const toRate = toCode === 'IDR' ? 1.0 : (currencyRates[toCode] || 1.0);

        // Convert via IDR base
        let inIdr = fromCode === 'IDR' ? amount : (fromRate > 0 ? (amount / fromRate) : amount);
        let converted = toCode === 'IDR' ? inIdr : (inIdr * toRate);

        const toOption = document.getElementById('currency-to').querySelector(`option[value="${toCode}"]`);
        const toSymbol = toOption ? toOption.getAttribute('data-symbol') : '';

        // Display
        const resEl = document.getElementById('converted-result');
        if (resEl) {
            resEl.textContent = toSymbol + ' ' + converted.toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' ' + toCode;
        }

        // Formula
        const formulaEl = document.getElementById('conversion-formula');
        if (formulaEl) {
            const oneUnitTo = toRate > 0 ? (fromRate / toRate) : 0;
            const oneUnitFrom = fromRate > 0 ? (toRate / fromRate) : 0;
            formulaEl.textContent = `Kurs Acuan: 1 ${fromCode} = ${oneUnitFrom.toFixed(4)} ${toCode} | 1 ${toCode} = ${oneUnitTo.toFixed(4)} ${fromCode}`;
        }
    }

    function swapCurrencies() {
        const fromSel = document.getElementById('currency-from');
        const toSel = document.getElementById('currency-to');

        const temp = fromSel.value;
        fromSel.value = toSel.value;
        toSel.value = temp;

        runConverter();
    }

    function setQuickCurrency(code) {
        const toSel = document.getElementById('currency-to');
        toSel.value = code;
        runConverter();
    }

    // 3. WEATHER MAP & SELECTION FUNCTIONS
    function initWeatherMap() {
        weatherMap = L.map('weather-interactive-map', {
            zoomControl: true,
            attributionControl: false
        }).setView([20.0, 45.0], 2);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18
        }).addTo(weatherMap);

        weatherHubs.forEach((hub, index) => {
            const markerColor = hub.risk_level === 'CRITICAL_HEAT' ? '#ef4444' : 
                               (hub.risk_level === 'ELEVATED_HEAT' ? '#f59e0b' : 
                               (hub.risk_level === 'FREEZE_RISK' ? '#06b6d4' : '#0d9488'));

            // Custom HTML Label Marker on map
            const customIcon = L.divIcon({
                className: 'custom-weather-marker',
                html: `<div style="background:${markerColor}; color:#fff; font-family:'Inter',sans-serif; font-size:10px; font-weight:bold; padding:2px 6px; border-radius:12px; border:2px solid #fff; box-shadow:0 2px 4px rgba(0,0,0,0.2); white-space:nowrap; cursor:pointer;">
                        ${hub.flag} ${hub.hub_code} ${Math.round(hub.temp)}°C
                       </div>`,
                iconSize: [60, 24],
                iconAnchor: [30, 12]
            });

            const marker = L.marker([hub.lat, hub.lon], { icon: customIcon }).addTo(weatherMap);

            marker.bindPopup(`
                <div style="font-family:'Inter',sans-serif; font-size:11px; padding:2px;">
                    <strong style="font-size:13px; color:#0f172a;">${hub.flag} ${hub.city} (${hub.hub_code})</strong><br>
                    <div style="font-size:15px; font-weight:bold; color:#0d9488; margin:4px 0;">${hub.temp}°C &bull; ${hub.condition}</div>
                    <div>💧 Kelembapan: <b>${hub.humidity}%</b> | 💨 Angin: <b>${hub.wind_speed} km/h</b></div>
                    <div style="margin-top:6px; padding:4px; background:#f8fafc; border-radius:4px; font-size:10px; color:#475569;">
                        <b>Kondisi:</b> ${hub.cold_chain_guidance}
                    </div>
                </div>
            `);

            marker.on('click', function() {
                updateWeatherPanel(index);
                document.getElementById('airport-weather-selector').value = index;
            });

            weatherMarkers.push(marker);
        });

        // Set default to Dubai DXB
        focusAirportOnMap(4);
    }

    function focusAirportOnMap(index) {
        const hub = weatherHubs[index];
        if (!hub || !weatherMap) return;

        weatherMap.flyTo([hub.lat, hub.lon], 5, { duration: 1.2 });
        if (weatherMarkers[index]) {
            weatherMarkers[index].openPopup();
        }

        updateWeatherPanel(index);
    }

    function updateWeatherPanel(index) {
        const hub = weatherHubs[index];
        if (!hub) return;

        document.getElementById('panel-flag').textContent = hub.flag;
        document.getElementById('panel-hub-badge').textContent = hub.hub_code;
        document.getElementById('panel-city').textContent = hub.city;
        document.getElementById('panel-country').textContent = `${hub.country} • ${hub.continent}`;
        document.getElementById('panel-temp').textContent = `${Math.round(hub.temp * 10) / 10}°C`;
        document.getElementById('panel-condition').textContent = hub.condition;
        document.getElementById('panel-feels').textContent = `${Math.round(hub.feels_like * 10) / 10}°C`;
        document.getElementById('panel-wind').textContent = `${Math.round(hub.wind_speed * 10) / 10} km/h`;

        const riskBox = document.getElementById('panel-risk-box');
        riskBox.className = `p-3 rounded-xl border bg-white space-y-1 text-xs ${hub.risk_badge}`;
        document.getElementById('panel-risk-desc').textContent = hub.cold_chain_guidance;

        lucide.createIcons();
    }

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
</script>
@endpush
@endsection
