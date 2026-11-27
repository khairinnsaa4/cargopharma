<!DOCTYPE html>
<html lang="en" class="h-full text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'CargoPharma' }} | Cold-Chain Vaccine Visibility</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        clinical: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                            950: '#042f2e',
                        },
                        brand: {
                            50: '#f0fdfa',
                            500: '#0d9488',
                            600: '#0f766e',
                            700: '#115e59',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Flag Icons CSS (SVG country flags for Windows compatibility) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />

    <!-- Leaflet.js CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        @font-face {
            font-family: "Twemoji Country Flags";
            src: url("https://cdn.jsdelivr.net/npm/country-flag-emoji-polyfill@0.1.8/dist/TwemojiCountryFlags.woff2") format("woff2");
            unicode-range: U+1F1E6-1F1FF, U+1F3F4, U+E0062-E007F;
            font-display: swap;
        }
        body { font-family: 'Inter', 'Twemoji Country Flags', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', 'Twemoji Country Flags', monospace; }

        /* ── Animated background: teal gradient sesuai tema sidebar ── */
        @keyframes bgShift {
            0%   { background-position: 0% 50%, 100% 50%, 0 0; }
            50%  { background-position: 100% 50%, 0% 50%, 0 0; }
            100% { background-position: 0% 50%, 100% 50%, 0 0; }
        }

        .main-bg {
            background-color: #f0fdf8;
            background-image:
                radial-gradient(ellipse 70% 60% at 15% 10%, rgba(13,148,136,0.18) 0%, transparent 65%),
                radial-gradient(ellipse 55% 50% at 85% 85%, rgba(20,184,166,0.13) 0%, transparent 65%),
                radial-gradient(ellipse 45% 40% at 50% 50%, rgba(5,150,105,0.07) 0%, transparent 70%);
            background-size: 200% 200%, 200% 200%, 100% 100%;
            animation: bgShift 12s ease-in-out infinite;
        }

        /* Clean corporate scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Leaflet Interactive Map Dragging & Pan */
        .leaflet-container {
            cursor: grab !important;
        }
        .leaflet-container:active,
        .leaflet-dragging,
        .leaflet-dragging .leaflet-grab,
        .leaflet-dragging .leaflet-interactive {
            cursor: grabbing !important;
        }
    </style>
</head>
<body class="h-full flex overflow-hidden main-bg">

    <!-- CLINICAL HEALTHCARE SIDEBAR (Deep Forest Teal #0A1E1D) -->
    <aside class="w-64 bg-[#0A1E1D] text-slate-300 flex flex-col shrink-0 z-20 shadow-xl border-r border-teal-950/60">
        <!-- Brand Header & Role Scope -->
        <div class="h-16 flex items-center px-6 gap-3 border-b border-teal-900/40">
            <div class="w-8 h-8 rounded-lg bg-teal-600 flex items-center justify-center text-white shadow-xs">
                <i data-lucide="shield-plus" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="font-bold text-white text-base tracking-tight">CargoPharma</span>
                <span class="block text-[10px] text-teal-400 font-mono tracking-wider uppercase font-semibold">{{ Auth::user()->role }}</span>
            </div>
        </div>

        <!-- Navigation Menus -->
        <div class="flex-1 overflow-y-auto px-4 py-5 space-y-1">
            <!-- 1. Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="activity" class="w-4 h-4"></i>
                <span>Dasbor</span>
            </a>

            <!-- 2. Shipments -->
            <a href="{{ route('shipments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('shipments.index') || request()->routeIs('shipments.show') || request()->routeIs('shipments.create') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span>Pengiriman</span>
            </a>

            <!-- 3. Tracking -->
            <a href="{{ route('tracking.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('tracking.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="map-pin" class="w-4 h-4"></i>
                <span>Pelacakan</span>
            </a>

            <!-- 4. Exceptions -->
            <a href="{{ route('exceptions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('exceptions.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                <span>Insiden</span>
            </a>

            <!-- 5. Global Radar -->
            <a href="{{ route('global-radar.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('global-radar.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <div class="flex items-center gap-3">
                    <i data-lucide="globe-2" class="w-4 h-4"></i>
                    <span>Radar Global</span>
                </div>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-teal-900/80 text-teal-200 border border-teal-700/50">Cuaca & Valas</span>
            </a>

            @if(!Auth::user()->isCustomer())
            <!-- Operations -->
            <div class="pt-4 px-3 text-[10px] uppercase font-mono tracking-wider text-teal-400/80 font-semibold">Operasional</div>
            
            <a href="{{ route('carriers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('carriers.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="truck" class="w-4 h-4"></i>
                <span>Armada</span>
            </a>

            <a href="{{ route('locations.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('locations.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="navigation" class="w-4 h-4"></i>
                <span>Lokasi</span>
            </a>

            <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('reports.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                <span>Laporan</span>
            </a>
            @endif

            @if(Auth::user()->isAdmin())
            <!-- Governance -->
            <div class="pt-4 px-3 text-[10px] uppercase font-mono tracking-wider text-teal-400/80 font-semibold">Sistem</div>

            <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('users.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Pengguna</span>
            </a>

            <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('audit-logs.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Audit Log</span>
            </a>
            @endif
        </div>

        <!-- User / Logout Footer -->
        <div class="p-4 border-t border-teal-900/40 bg-[#071615]">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-rose-400 hover:bg-teal-900/40 transition-all">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN BODY WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- TOPBAR (Clean Medical Enterprise Header) -->
        <header class="h-16 bg-white border-b border-slate-200/90 px-6 flex items-center justify-between shrink-0 z-10 shadow-xs">
            <!-- Search Input -->
            <div class="flex items-center gap-4 flex-1 max-w-md">
                <form action="{{ route('shipments.index') }}" method="GET" class="w-full relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <input type="text" name="search" placeholder="Cari resi, kurir, rute..." 
                        class="w-full pl-9 pr-4 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-600 focus:bg-white transition-all">
                </form>
            </div>

            <!-- Right Controls: Telemetry Badge, Notifications & Profile -->
            <div class="flex items-center gap-3">
                <!-- Status Badge -->
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-teal-50 border border-teal-200 text-xs font-semibold text-teal-800">
                    <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                    <span>Live Monitoring</span>
                </div>

                <!-- User Profile -->
                <div class="flex items-center gap-2.5 pl-3 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-800 font-bold flex items-center justify-center text-xs border border-teal-200 shadow-xs">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-semibold text-slate-900 leading-tight">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] text-teal-700 font-bold uppercase">{{ Auth::user()->role }}</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- FLASH NOTIFICATIONS -->
        @if(session('success'))
        <div class="mx-6 mt-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
        </div>
        @endif

        @if(session('warning'))
        <div class="mx-6 mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                <span>{{ session('warning') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
        </div>
        @endif

        @if($errors->any())
        <div class="mx-6 mt-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- MAIN SCROLLABLE CONTENT -->
        <main class="flex-1 overflow-y-auto p-6 space-y-6">
            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();

        // Universal Smooth Scroll Handler for Hash Navigation inside <main>
        document.addEventListener('click', function(e) {
            const anchor = e.target.closest('a[href*="#"]');
            if (!anchor) return;
            try {
                const url = new URL(anchor.href, window.location.href);
                if (url.pathname === window.location.pathname && url.hash) {
                    const targetId = url.hash.substring(1);
                    const target = document.getElementById(targetId);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        if (typeof weatherMap !== 'undefined' && weatherMap) {
                            setTimeout(() => weatherMap.invalidateSize(), 300);
                        }
                    }
                }
            } catch (err) {}
        });

        // Auto-scroll on initial load if hash is present
        if (window.location.hash) {
            setTimeout(function() {
                const target = document.getElementById(window.location.hash.substring(1));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 250);
        }
    </script>
    @stack('scripts')
</body>
</html>
