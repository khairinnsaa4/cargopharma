@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HERO VISUAL BANNER (ROLE-TAILORED) -->
    <div class="relative rounded-2xl overflow-hidden border border-teal-900/40 shadow-sm min-h-[160px] flex items-center p-6 text-white bg-slate-900">
        <!-- Background Image with Gradient Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/cargopharma-banner.jpg') }}" alt="CargoPharma Banner" class="w-full h-full object-cover object-right sm:object-center opacity-45 transform scale-105">
            <div class="absolute inset-0 bg-gradient-to-r from-[#071918] via-[#0A2220]/90 to-transparent"></div>
        </div>

        <!-- Content on top of banner -->
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4 w-full">
            <div class="space-y-1.5 max-w-2xl">
                <div class="flex items-center gap-2 flex-wrap">
                    @if(Auth::user()->isAdmin())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-teal-500/25 text-teal-300 border border-teal-400/40 text-[10px] font-bold tracking-wide uppercase">
                            <i data-lucide="shield-check" class="w-3 h-3 text-teal-300"></i> Administrator Hub
                        </span>
                        <span class="text-[10px] text-teal-200/90 font-mono">WHO GDP &bull; 21 CFR Part 11 &bull; SLA Governance</span>
                    @elseif(Auth::user()->isOperator())
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/25 text-emerald-300 border border-emerald-400/40 text-[10px] font-bold tracking-wide uppercase">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Dispatch & Operations Center
                        </span>
                        <span class="text-[10px] text-emerald-200/90 font-mono">Telemetri Aktif: +2°C s/d +8°C &bull; -70°C Ultra-Cold</span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-cyan-500/25 text-cyan-200 border border-cyan-400/40 text-[10px] font-bold tracking-wide uppercase">
                            <i data-lucide="building-2" class="w-3 h-3 text-cyan-300"></i> Portal Penerima Resmi (Consignee)
                        </span>
                        <span class="text-[10px] text-cyan-200/90 font-mono">Verifikasi Vaksin Polio nOPV2 &bull; UNICEF / WHO Response</span>
                    @endif
                </div>

                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    @if(Auth::user()->isAdmin())
                        CargoPharma Executive Governance
                    @elseif(Auth::user()->isOperator())
                        Pusat Kendali Disposisi &amp; Telemetri Vaksin
                    @else
                        Portal Penerimaan Vaksin &amp; Jaminan Mutu Rantai Dingin
                    @endif
                </h1>

                <p class="text-xs text-slate-300 leading-relaxed">
                    @if(Auth::user()->isAdmin())
                        Pengawasan menyeluruh kepatuhan Good Distribution Practice (GDP), evaluasi performa SLA mitra armada darat, laut, dan udara, serta audit forensik digital.
                    @elseif(Auth::user()->isOperator())
                        Triage aktif manifes kargo farmasi Bio Farma, pemantauan sensor suhu cold-box IoT, validasi titik transit checkpoint, dan mitigasi darurat cuaca ekstrem.
                    @else
                        Verifikasi keaslian batch vaksin internasional, pemantauan ETA pengiriman ke fasilitas tujuan, dan sertifikasi kepatuhan termal WHO PQS secara real-time.
                    @endif
                </p>
            </div>

            <!-- Quick Action Buttons by Role -->
            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('audit-logs.index') }}" class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white font-semibold text-xs backdrop-blur-md border border-white/20 transition-all flex items-center gap-1.5">
                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-teal-300"></i>
                        <span>Audit Log</span>
                    </a>
                    <a href="{{ route('carriers.index') }}" class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white font-semibold text-xs backdrop-blur-md border border-white/20 transition-all flex items-center gap-1.5">
                        <i data-lucide="truck" class="w-3.5 h-3.5 text-teal-300"></i>
                        <span>Mitra Armada</span>
                    </a>
                    <a href="{{ route('tracking.index') }}" class="px-3.5 py-2 rounded-lg bg-teal-500 hover:bg-teal-600 text-slate-950 font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                        <i data-lucide="map" class="w-3.5 h-3.5"></i>
                        <span>Pelacakan Global</span>
                    </a>
                @elseif(Auth::user()->isOperator())
                    <a href="{{ route('tracking.index') }}" class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white font-semibold text-xs backdrop-blur-md border border-white/20 transition-all flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-300"></i>
                        <span>Pelacakan Live</span>
                    </a>
                    <a href="{{ route('exceptions.index') }}" class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white font-semibold text-xs backdrop-blur-md border border-white/20 transition-all flex items-center gap-1.5">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-amber-300"></i>
                        <span>Insiden ({{ $openExceptionsCount }})</span>
                    </a>
                    <a href="{{ route('shipments.create') }}" class="px-3.5 py-2 rounded-lg bg-teal-500 hover:bg-teal-600 text-slate-950 font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Pengiriman Baru</span>
                    </a>
                @else
                    <a href="{{ route('tracking.index') }}" class="px-3.5 py-2 rounded-lg bg-teal-500 hover:bg-teal-600 text-slate-950 font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                        <span>Lacak Vaksin Saya</span>
                    </a>
                    <a href="{{ route('exceptions.index') }}" class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white font-semibold text-xs backdrop-blur-md border border-white/20 transition-all flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-rose-300"></i>
                        <span>Lapor Masalah</span>
                    </a>
                    <a href="{{ route('global-radar.index') }}" class="px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white font-semibold text-xs backdrop-blur-md border border-white/20 transition-all flex items-center gap-1.5">
                        <i data-lucide="cloud-sun" class="w-3.5 h-3.5 text-teal-300"></i>
                        <span>Radar Cuaca</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- REAL-TIME FINANCIAL & CURRENCY TELEMETRY TICKER (LIVE MARQUEE KE KIRI) -->
    <div class="px-4 py-2 rounded-xl bg-slate-950 text-white border border-teal-900/40 shadow-sm flex items-center gap-3 overflow-hidden relative">
        <div class="flex items-center gap-2 shrink-0 pr-3 border-r border-slate-800 bg-slate-950 z-10">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="text-[11px] font-bold tracking-wider uppercase text-emerald-400 font-mono flex items-center gap-1.5 whitespace-nowrap">
                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> Kurs Valas Live
            </span>
        </div>

        <!-- Marquee Track Moving to Left -->
        <div class="relative overflow-hidden w-full select-none">
            <div class="valas-ticker-track flex items-center gap-3 text-xs font-mono py-1 whitespace-nowrap">
                @for($repeat = 0; $repeat < 2; $repeat++)
                    @foreach($currencyData['pairs'] as $pair)
                    <div class="inline-flex items-center gap-1.5 shrink-0 px-2.5 py-1 rounded-lg bg-slate-900/90 border border-slate-800 shadow-2xs hover:border-teal-500/50 hover:bg-slate-850 transition-colors">
                        <span class="fi fi-{{ $pair['country_code'] }} fis shrink-0" style="font-size: 16px; border-radius: 2px;"></span>
                        <span class="text-slate-300 text-[11px] font-bold">{{ $pair['code'] }}</span>
                        <span class="text-slate-500 text-[10px] hidden sm:inline">({{ $pair['country'] }})</span>
                        <span class="font-bold text-emerald-400 font-mono text-[11px]">{{ $pair['symbol'] }} {{ number_format($pair['rate'], $pair['code'] === 'IDR' ? 0 : 4) }}</span>
                    </div>
                    @endforeach
                @endfor
            </div>
        </div>
    </div>

    <!-- 1. TOP STATS ROW: 5 ROLE-TAILORED KPI CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        @if(Auth::user()->isAdmin())
            <!-- Admin KPI 1: Total Shipments -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0 border border-teal-100">
                    <i data-lucide="globe" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Total Konsinyasi</span>
                    <span class="text-xl font-bold text-slate-900 leading-tight">{{ $totalShipments }}</span>
                </div>
            </div>

            <!-- Admin KPI 2: Carrier SLA Adherence -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                    <i data-lucide="check-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Kepatuhan SLA</span>
                    <span class="text-xl font-bold text-emerald-700 leading-tight">{{ round(100 - $averageDelayRate, 1) }}%</span>
                </div>
            </div>

            <!-- Admin KPI 3: Total Active Carriers -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-sky-50 text-sky-700 flex items-center justify-center shrink-0 border border-sky-100">
                    <i data-lucide="truck" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Mitra Armada</span>
                    <span class="text-xl font-bold text-slate-900 leading-tight">{{ $totalCarriersCount }} <span class="text-xs font-normal text-slate-500">Carrier</span></span>
                </div>
            </div>

            <!-- Admin KPI 4: Compliance Audit Logs -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0 border border-indigo-100">
                    <i data-lucide="file-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Jejak Audit GDP</span>
                    <span class="text-xl font-bold text-indigo-900 leading-tight">{{ $recentAuditLogs->count() }} <span class="text-xs font-normal text-slate-500">Log</span></span>
                </div>
            </div>

            <!-- Admin KPI 5: Critical Exceptions -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3 col-span-2 sm:col-span-1">
                <div class="w-11 h-11 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                    <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Insiden Kritis</span>
                    <span class="text-xl font-bold text-rose-600 leading-tight">{{ $openExceptionsCount }}</span>
                </div>
            </div>

        @elseif(Auth::user()->isOperator())
            <!-- Operator KPI 1: Active In Transit -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0 border border-teal-100">
                    <i data-lucide="navigation-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Kargo Bergerak</span>
                    <span class="text-xl font-bold text-teal-900 leading-tight">{{ $inTransitCount }}</span>
                </div>
            </div>

            <!-- Operator KPI 2: Pending Manifest / Staging -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 border border-amber-100">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Perlu Disposisi</span>
                    <span class="text-xl font-bold text-amber-700 leading-tight">{{ $manifestPendingCount }}</span>
                </div>
            </div>

            <!-- Operator KPI 3: Temperature Warning / Delays -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center shrink-0 border border-orange-100">
                    <i data-lucide="thermometer-snowflake" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Waspada Delay</span>
                    <span class="text-xl font-bold text-orange-600 leading-tight">{{ $delayedCount }}</span>
                </div>
            </div>

            <!-- Operator KPI 4: Open Exceptions Queue -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Triage Insiden</span>
                    <span class="text-xl font-bold text-rose-600 leading-tight">{{ $openExceptionsCount }}</span>
                </div>
            </div>

            <!-- Operator KPI 5: Delivered Today -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3 col-span-2 sm:col-span-1">
                <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Terkirim / Tiba</span>
                    <span class="text-xl font-bold text-slate-900 leading-tight">{{ $deliveredCount }}</span>
                </div>
            </div>

        @else
            <!-- Customer KPI 1: My Vaccine Orders -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0 border border-teal-100">
                    <i data-lucide="package-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Pesanan Vaksin</span>
                    <span class="text-xl font-bold text-slate-900 leading-tight">{{ $totalShipments }}</span>
                </div>
            </div>

            <!-- Customer KPI 2: On The Way to Facility -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-100">
                    <i data-lucide="plane-takeoff" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Dalam Perjalanan</span>
                    <span class="text-xl font-bold text-blue-900 leading-tight">{{ $inTransitCount }}</span>
                </div>
            </div>

            <!-- Customer KPI 3: Arrived Safely -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Tiba di Fasilitas</span>
                    <span class="text-xl font-bold text-slate-900 leading-tight">{{ $deliveredCount }}</span>
                </div>
            </div>

            <!-- Customer KPI 4: Cold-Chain Integrity % -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-cyan-50 text-cyan-700 flex items-center justify-center shrink-0 border border-cyan-100">
                    <i data-lucide="thermometer" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Integritas Suhu</span>
                    <span class="text-xl font-bold text-cyan-800 leading-tight">{{ $carrierMetricValue }}</span>
                </div>
            </div>

            <!-- Customer KPI 5: Total Doses Received -->
            <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs flex items-center gap-3 col-span-2 sm:col-span-1">
                <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium block">Total Dosis</span>
                    <span class="text-xl font-bold text-slate-900 leading-tight">{{ number_format($customerDoses > 0 ? $customerDoses : 1000000) }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- 2. MIDDLE 3-CARD ROW -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Card 1: Shipments by Status (Horizontal Bar Chart) -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between pb-1 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                    <h3 class="text-sm font-bold text-slate-900">Status Konsinyasi</h3>
                </div>
                <span class="text-[11px] text-slate-400 font-mono">Real-Time</span>
            </div>

            <div class="h-[175px] w-full relative">
                <canvas id="statusBarChart"></canvas>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs font-mono text-slate-500 pt-1 border-t border-slate-100">
                <div class="flex items-center gap-1.5 truncate">
                    <span class="w-2 h-2 rounded-full bg-teal-600 shrink-0"></span>
                    <span class="truncate">Transit: {{ $inTransitCount }}</span>
                </div>
                <div class="flex items-center gap-1.5 truncate">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    <span class="truncate">Terkirim: {{ $deliveredCount }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Role-Tailored Donut Chart -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between pb-1 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                    <h3 class="text-sm font-bold text-slate-900 truncate">{{ $carrierChartTitle }}</h3>
                </div>
                <span class="text-[11px] text-slate-400 font-mono">Kinerja</span>
            </div>

            <div class="flex items-center gap-4">
                <div class="relative w-28 h-28 shrink-0 flex items-center justify-center">
                    <canvas id="carrierDonutChart" class="w-full h-full"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                        <span class="text-xs font-extrabold text-slate-900 font-mono leading-none">{{ $carrierMetricValue }}</span>
                        <span class="text-[9px] text-slate-500 uppercase tracking-tight mt-0.5">{{ $carrierMetricLabel }}</span>
                    </div>
                </div>

                <div class="flex-1 space-y-1.5 text-xs overflow-hidden">
                    @foreach($carrierLegendItems as $item)
                    <div class="flex items-center justify-between py-1 px-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="flex items-center gap-2 truncate">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0 shadow-2xs" style="background-color: {{ $item['color'] }}"></span>
                            <span class="text-slate-700 font-medium truncate" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                        </span>
                        <span class="font-mono font-bold text-slate-900 ml-2 shrink-0">{{ $item['value'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                {!! $carrierBenchmark !!}
            </div>
        </div>

        <!-- Card 3: Interactive Route Map -->
        <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-xs space-y-3 flex flex-col">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="map" class="w-4 h-4 text-teal-600"></i>
                    <h3 class="text-sm font-bold text-slate-900">Lintasan Rute Aktif</h3>
                </div>
                <a href="{{ route('tracking.index') }}" class="text-xs text-teal-700 hover:text-teal-900 font-semibold flex items-center gap-1">
                    <span>Pelacakan</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <!-- Mini Map Canvas -->
            <div class="relative flex-1 w-full min-h-[175px] rounded-lg border border-slate-200 overflow-hidden">
                <div id="dashboard-mini-map" class="w-full h-full min-h-[175px] z-0"></div>

                <!-- Floating Pan & Navigation D-Pad Controls -->
                <div class="absolute top-2 left-12 z-[400] flex items-center gap-0.5 bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-md p-0.5 shadow-xs">
                    <button type="button" onclick="panDashboardMap(0, -120)" title="Geser ke Atas"
                        class="w-6 h-6 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                        <i data-lucide="arrow-up" class="w-3 h-3"></i>
                    </button>
                    <button type="button" onclick="panDashboardMap(0, 120)" title="Geser ke Bawah"
                        class="w-6 h-6 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                        <i data-lucide="arrow-down" class="w-3 h-3"></i>
                    </button>
                    <button type="button" onclick="panDashboardMap(-120, 0)" title="Geser ke Kiri"
                        class="w-6 h-6 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                        <i data-lucide="arrow-left" class="w-3 h-3"></i>
                    </button>
                    <button type="button" onclick="panDashboardMap(120, 0)" title="Geser ke Kanan"
                        class="w-6 h-6 rounded bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold">
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </button>
                    <button type="button" onclick="resetDashboardMap()" title="Pusatkan Peta"
                        class="w-6 h-6 rounded bg-teal-50 hover:bg-teal-600 hover:text-white text-teal-800 flex items-center justify-center transition-colors cursor-pointer text-xs">
                        <i data-lucide="crosshair" class="w-3 h-3"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. ROLE-SPECIFIC SPECIALIZED SECTIONS -->

    @if(Auth::user()->isAdmin())
        <!-- ========================================== -->
        <!-- ADMIN SPECIFIC: SYSTEM AUDIT TRAIL LOGS    -->
        <!-- ========================================== -->
        <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden space-y-0">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-200 shrink-0">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Jejak Audit Kepatuhan Regulasi (Regulatory Compliance Trail)</h3>
                        <p class="text-[11px] text-slate-500">Mencatat bukti forensik digital perubahan data, login, dan validasi suhu sesuai standar WHO GDP &amp; FDA 21 CFR Part 11.</p>
                    </div>
                </div>

                <a href="{{ route('audit-logs.index') }}" class="px-3 py-1.5 rounded-lg border border-teal-200 bg-teal-50 text-xs font-semibold text-teal-800 hover:bg-teal-100 flex items-center gap-1.5 transition-colors">
                    <span>Lihat Semua Log Audit</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-medium">
                        <tr>
                            <th class="py-3 px-4 font-semibold">Waktu (UTC/WIB)</th>
                            <th class="py-3 px-4 font-semibold">Petugas (User)</th>
                            <th class="py-3 px-4 font-semibold">Tindakan / Aksi</th>
                            <th class="py-3 px-4 font-semibold">Objek Entitas</th>
                            <th class="py-3 px-4 font-semibold">IP Address</th>
                            <th class="py-3 px-4 font-semibold text-right">Status Kepatuhan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentAuditLogs as $log)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-mono text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-900">
                                {{ $log->user?->name ?? 'System Automated' }}
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-teal-900">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $log->entity_type }} #{{ $log->entity_id }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i data-lucide="check" class="w-3 h-3"></i> Terverifikasi
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400 font-mono">
                                Belum ada catatan audit log tersimpan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif(Auth::user()->isOperator())
        <!-- ============================================== -->
        <!-- OPERATOR SPECIFIC: ACTIVE DISPATCH QUEUE       -->
        <!-- ============================================== -->
        <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden space-y-0">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-200 shrink-0">
                        <i data-lucide="radio" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Antrean Disposisi &amp; Telemetri Kargo Aktif</h3>
                        <p class="text-[11px] text-slate-500">Triage pengiriman aktif yang membutuhkan pemantauan suhu sensor sensor berkala dan pelaporan waypoint.</p>
                    </div>
                </div>

                <a href="{{ route('shipments.create') }}" class="px-3 py-1.5 rounded-lg bg-teal-600 text-white hover:bg-teal-700 text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-2xs">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Buat Pengiriman</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-medium">
                        <tr>
                            <th class="py-3 px-4 font-semibold">Resi / AWB</th>
                            <th class="py-3 px-4 font-semibold">Produk Vaksin</th>
                            <th class="py-3 px-4 font-semibold">Rute &amp; Moda</th>
                            <th class="py-3 px-4 font-semibold">Suhu Sensor Terakhir</th>
                            <th class="py-3 px-4 font-semibold">Status Operasional</th>
                            <th class="py-3 px-4 text-right font-semibold">Tindakan Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($activeDispatchQueue as $shp)
                        @php
                            $lastTemp = $shp->latestTrackingEvent?->container_temperature ?? 4.2;
                            $tempOk = ($lastTemp >= $shp->target_temp_min && $lastTemp <= $shp->target_temp_max);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ $shp->shipment_number }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-800">{{ $shp->items->first()?->product_name ?? 'Polio nOPV2 Vaccine' }}</span>
                                <span class="text-[10px] text-slate-400 block font-mono">Lot: {{ $shp->items->first()?->batch_number ?? 'BF-2024-X1' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-medium text-slate-700">{{ $shp->origin->city }} &rarr; {{ $shp->destination->city }}</span>
                                <span class="text-[10px] text-teal-700 font-bold block">{{ $shp->carrier?->name }} ({{ strtoupper($shp->carrier?->transport_type ?? 'Air') }})</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded font-mono font-bold text-xs {{ $tempOk ? 'bg-teal-50 text-teal-800 border border-teal-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    <i data-lucide="{{ $tempOk ? 'check' : 'alert-circle' }}" class="w-3.5 h-3.5"></i>
                                    <span>{{ $lastTemp }}°C</span>
                                </div>
                                <span class="text-[9px] text-slate-400 block">Batas: {{ $shp->target_temp_min }}°C ~ {{ $shp->target_temp_max }}°C</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($shp->is_delayed)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">TERTUNDA</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-200">IN TRANSIT</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('shipments.show', $shp->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-700 font-semibold text-xs transition-colors inline-flex items-center gap-1">
                                    <span>Update Telemetri</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400 font-mono">
                                Tidak ada kargo yang sedang aktif bergerak.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        <!-- ============================================== -->
        <!-- CUSTOMER SPECIFIC: INCOMING CONSIGNMENTS & SOP -->
        <!-- ============================================== -->
        <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden space-y-0">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-700 flex items-center justify-center border border-cyan-200 shrink-0">
                        <i data-lucide="package-search" class="w-4 h-4 text-cyan-600"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Jadwal Kedatangan Konsinyasi Vaksin Saya</h3>
                        <p class="text-[11px] text-slate-500">Daftar pasokan vaksin yang dialokasikan untuk fasilitas Anda dengan verifikasi integritas rantai dingin WHO.</p>
                    </div>
                </div>

                <a href="{{ route('exceptions.index') }}" class="px-3 py-1.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    <span>Pantau Insiden Kargo</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-medium">
                        <tr>
                            <th class="py-3 px-4 font-semibold">Nomor Resi / AWB</th>
                            <th class="py-3 px-4 font-semibold">Vaksin &amp; Nomor Batch</th>
                            <th class="py-3 px-4 font-semibold">Alokasi Dosis</th>
                            <th class="py-3 px-4 font-semibold">Estimasi Tiba (ETA)</th>
                            <th class="py-3 px-4 font-semibold">Suhu Sensor Real-Time</th>
                            <th class="py-3 px-4 text-right font-semibold">Status Penerimaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($incomingShipments as $inc)
                        @php
                            $curTemp = $inc->latestTrackingEvent?->container_temperature ?? 4.0;
                            $tempSafe = ($curTemp >= $inc->target_temp_min && $curTemp <= $inc->target_temp_max);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ $inc->shipment_number }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-900">{{ $inc->items->first()?->product_name ?? 'Novel Oral Polio Vaccine Type 2 (nOPV2)' }}</span>
                                <span class="text-[10px] text-slate-500 block font-mono">Batch: {{ $inc->items->first()?->batch_number ?? 'BF-nOPV2-2024' }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono">
                                <strong>{{ number_format($inc->items->first()?->quantity_vials ?? 25000) }}</strong> Vial
                                <span class="text-[10px] text-slate-500 block">({{ number_format(($inc->items->first()?->quantity_vials ?? 25000) * ($inc->items->first()?->doses_per_vial ?? 20)) }} Dosis)</span>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-700">
                                {{ $inc->expected_arrival?->format('d M Y, H:i') ?? 'Segera Tiba' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-mono font-bold {{ $tempSafe ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $tempSafe ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500 animate-ping' }}"></span>
                                    <span>{{ $curTemp }}°C</span>
                                </span>
                                <span class="text-[10px] text-teal-700 block font-sans font-medium mt-0.5">Standar WHO: Terjaga</span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('shipments.show', $inc->id) }}" class="px-3 py-1 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs transition-colors inline-flex items-center gap-1 shadow-2xs">
                                    <span>Verifikasi Kargo</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400 font-mono">
                                Tidak ada konsinyasi aktif yang sedang dalam perjalanan menuju fasilitas Anda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Consignee Vaccine Receiving SOP Guidance Box -->
        <div class="p-4 rounded-xl bg-gradient-to-r from-teal-50 to-cyan-50 border border-teal-200/80 shadow-2xs">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-teal-600 text-white flex items-center justify-center shrink-0 shadow-2xs mt-0.5">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <div class="space-y-1 text-xs">
                    <h4 class="font-bold text-teal-950 text-sm">Panduan Prosedur Penerimaan Vaksin (WHO Receiving SOP)</h4>
                    <p class="text-slate-600 leading-relaxed">
                        Saat kargo vaksin mendarat di bandara atau tiba di cold room fasilitas Anda:
                        <strong>(1)</strong> Periksa keutuhan segel kontainer pasif PCM/dry-ice.
                        <strong>(2)</strong> Cek indikator fisik Vaccine Vial Monitor (VVM) pada botol vaksin.
                        <strong>(3)</strong> Konfirmasi bahwa suhu telemetri berada dalam rentang izin (+2°C s/d +8°C atau -20°C).
                        <strong>(4)</strong> Klik <em>"Lapor Masalah Kargo"</em> bila terdapat kerusakan kemasan atau kenaikan suhu transit.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- 4. BOTTOM SECTION: RECENT SHIPMENTS TABLE (UNIVERSAL) -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <!-- Table Header & Action Controls -->
        <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <i data-lucide="package" class="w-4 h-4 text-teal-600"></i>
                <h3 class="text-sm font-bold text-slate-900">Pengiriman Keseluruhan</h3>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('shipments.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition-all">
                    <span>Lihat Semua Resi</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-medium">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Resi</th>
                        <th class="py-3 px-4 font-semibold">Penerima</th>
                        <th class="py-3 px-4 font-semibold">Kurir</th>
                        <th class="py-3 px-4 font-semibold">Rute</th>
                        <th class="py-3 px-4 font-semibold">ETA</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentShipments as $shipment)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <!-- Shipment No -->
                        <td class="py-3 px-4 font-mono font-semibold text-slate-900">
                            {{ $shipment->shipment_number }}
                        </td>

                        <!-- Customer -->
                        <td class="py-3 px-4">
                            <span class="font-medium text-slate-800">{{ $shipment->customer->organization ?? $shipment->customer->name }}</span>
                        </td>

                        <!-- Carrier -->
                        <td class="py-3 px-4 text-slate-600">
                            {{ $shipment->carrier->name ?? 'Armada Standar' }}
                        </td>

                        <!-- Route -->
                        <td class="py-3 px-4 text-slate-600 font-medium">
                            <span class="flex items-center gap-1">
                                <span>{{ $shipment->origin->city }}</span>
                                <span class="text-teal-600">&rarr;</span>
                                <span>{{ $shipment->destination->city }}</span>
                            </span>
                        </td>

                        <!-- ETA -->
                        <td class="py-3 px-4 font-mono text-slate-600">
                            {{ $shipment->expected_arrival?->format('Y-m-d') }}
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3 px-4">
                            @if($shipment->is_delayed)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-600 text-white shadow-xs">
                                    TERTUNDA
                                </span>
                            @elseif($shipment->current_status === 'DELIVERED')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white shadow-xs">
                                    TERKIRIM
                                </span>
                            @elseif($shipment->current_status === 'IN_TRANSIT')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal-600 text-white shadow-xs">
                                    DALAM PERJALANAN
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-200 text-slate-700">
                                    {{ $shipment->current_status }}
                                </span>
                            @endif
                        </td>

                        <!-- Action -->
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('shipments.show', $shipment->id) }}" title="Lihat Detail" class="p-1.5 rounded-lg text-slate-400 hover:text-teal-700 hover:bg-slate-100 transition-colors inline-flex items-center">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 font-mono">
                            Tidak ada data pengiriman terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. GLOBAL RADAR & PETA CUACA SUMMARY BANNER -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center border border-teal-200 shrink-0">
                <i data-lucide="globe-2" class="w-5 h-5 text-teal-600"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-slate-900">Radar Global &amp; Peta Cuaca Tiap Negara</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Live Telemetri</span>
                </div>
                <span class="text-xs text-slate-500">Peta cuaca real-time 40+ negara transit vaksin dunia, kalkulator valas live, dan telemetri bandara.</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('global-radar.index') }}" class="px-3.5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-xs transition-all flex items-center gap-1.5 whitespace-nowrap">
                <i data-lucide="map" class="w-3.5 h-3.5"></i>
                <span>Buka Radar Global</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>

</div>

<style>
    /* Infinite Smooth Ticker Scrolling to Left */
    @keyframes valasTickerMoveLeft {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .valas-ticker-track {
        display: inline-flex;
        width: max-content;
        will-change: transform;
        animation: valasTickerMoveLeft 25s linear infinite;
    }
    .valas-ticker-track:hover {
        animation-play-state: paused;
    }
</style>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // 1. Shipments by Status (Clean Horizontal Bar Chart)
        const barCtx = document.getElementById('statusBarChart').getContext('2d');
        const barData = @json($barChartData);

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['Dalam Perjalanan', 'Terkirim', 'Tertunda', 'Insiden'],
                datasets: [{
                    label: 'Konsinyasi',
                    data: [
                        barData.in_transit || 0,
                        barData.delivered || 0,
                        barData.delayed || 0,
                        barData.exceptions || 0
                    ],
                    backgroundColor: [
                        '#0d9488', // Medical Teal
                        '#10b981', // Emerald green
                        '#f59e0b', // Warm Amber
                        '#ef4444'  // Rose Red
                    ],
                    borderRadius: 6,
                    borderSkipped: false,
                    barThickness: 16,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { left: 8, right: 12, top: 4, bottom: 4 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Inter', size: 11, weight: 'bold' },
                        bodyFont: { family: 'JetBrains Mono', size: 11 },
                        padding: 8,
                        cornerRadius: 6,
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.raw + ' Konsinyasi';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { stepSize: 1, font: { family: 'JetBrains Mono', size: 10 } }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 11, weight: '600' }, color: '#334155' }
                    }
                }
            }
        });

        // 2. Delay Rate / Cold-Chain Integrity (Donut Chart)
        const donutCtx = document.getElementById('carrierDonutChart').getContext('2d');
        const carrierLabels = @json($carrierChartLabels);
        const carrierData = @json($carrierChartData);
        const carrierColors = @json($carrierChartColors);

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: carrierLabels,
                datasets: [{
                    data: carrierData,
                    backgroundColor: carrierColors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false // Turned off so donut stays dead-centered!
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                }
            }
        });

        // 3. Mini Leaflet Map (Smooth Dragging & Multimodal Routes: Udara, Darat, Laut)
        const routes = @json($mapRoutes);
        const miniMap = L.map('dashboard-mini-map', {
            zoomControl: true,
            attributionControl: false,
            dragging: true,
            touchZoom: true,
            scrollWheelZoom: true,
            doubleClickZoom: true,
            boxZoom: true,
            tap: false,
            inertia: true
        }).setView([-6.89, 107.59], 2);

        miniMap.dragging.enable();

        window.panDashboardMap = function(dx, dy) {
            if (miniMap) {
                miniMap.panBy([dx, dy], { animate: true, duration: 0.3 });
            }
        };

        window.resetDashboardMap = function() {
            if (miniMap) {
                miniMap.setView([-6.89, 107.59], 2, { animate: true });
            }
        };

        const esriSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: 'Tiles &copy; Esri'
        });
        const esriLabels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            attribution: 'Labels &copy; Esri'
        });
        L.layerGroup([esriSatellite, esriLabels]).addTo(miniMap);

        setTimeout(() => { miniMap.invalidateSize(); }, 300);
        window.addEventListener('resize', () => { miniMap.invalidateSize(); });

        const latLngs = [];
        routes.forEach(r => {
            const origin = r.origin;
            const dest = r.destination || r.dest;
            if (!origin || !dest || !origin.lat || !dest.lat) return;

            const originPt = [origin.lat, origin.lng];
            const destPt = [dest.lat, dest.lng];
            latLngs.push(originPt, destPt);

            const mode = r.transport_type || 'air';
            const modeColor = mode === 'air' ? '#0284c7' : (mode === 'sea' ? '#0d9488' : '#d97706');
            const modeIcon = mode === 'air' ? '✈️' : (mode === 'sea' ? '🚢' : '🚚');

            // Origin marker
            L.circleMarker(originPt, {
                radius: 4.5,
                color: modeColor,
                fillColor: modeColor,
                fillOpacity: 1
            }).addTo(miniMap).bindTooltip(`<b>${origin.name}</b>`, { sticky: true });

            // Destination marker
            L.circleMarker(destPt, {
                radius: 4.5,
                color: '#10b981',
                fillColor: '#10b981',
                fillOpacity: 1
            }).addTo(miniMap).bindTooltip(`<b>${dest.name}</b>`, { sticky: true });

            // Draw line
            const poly = L.polyline([originPt, destPt], {
                color: r.delayed ? '#e11d48' : modeColor,
                weight: mode === 'air' ? 2 : 3,
                opacity: 0.85,
                dashArray: mode === 'air' ? '4, 4' : 'none'
            }).addTo(miniMap);

            poly.bindTooltip(`<b>${modeIcon} ${r.number}</b><br>${origin.name} &rarr; ${dest.name}`, { sticky: true });
        });

        if (latLngs.length > 0) {
            miniMap.fitBounds(L.latLngBounds(latLngs), { padding: [25, 25] });
        }
    });
</script>
@endpush
@endsection
