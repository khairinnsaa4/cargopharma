<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100 text-slate-800">
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

    <!-- Leaflet.js CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        /* Clean corporate scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="h-full flex overflow-hidden bg-[#F4F7F6]">

    <!-- CLINICAL HEALTHCARE SIDEBAR (Deep Forest Teal #0A1E1D) -->
    <aside class="w-64 bg-[#0A1E1D] text-slate-300 flex flex-col shrink-0 z-20 shadow-xl border-r border-teal-950/60">
        <!-- Brand Header & Role Scope -->
        <div class="h-16 flex items-center px-6 gap-3 border-b border-teal-900/40">
            <div class="w-8 h-8 rounded-lg bg-teal-600 flex items-center justify-center text-white shadow-xs">
                <i data-lucide="shield-plus" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="font-bold text-white text-base tracking-tight">CargoPharma</span>
                <span class="block text-[10px] text-teal-300/80 font-mono">
                    @if(Auth::user()->isAdmin())
                        Executive Governance
                    @elseif(Auth::user()->isOperator())
                        Cold-Chain Operations
                    @else
                        Consignee Portal
                    @endif
                </span>
            </div>
        </div>

        <!-- Navigation Menus (Role-Tailored Healthcare Architecture) -->
        <div class="flex-1 overflow-y-auto px-4 py-5 space-y-1">
            <!-- 1. Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="activity" class="w-4 h-4"></i>
                <span>Dashboard</span>
            </a>

            <!-- 2. Shipments / My Consignments -->
            <a href="{{ route('shipments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('shipments.index') || request()->routeIs('shipments.show') || request()->routeIs('shipments.create') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span>{{ Auth::user()->isCustomer() ? 'My Consignments' : 'Shipments' }}</span>
            </a>

            <!-- 3. Tracking (Dedicated Live Map & Checkpoint View) -->
            <a href="{{ route('tracking.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('tracking.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="map-pin" class="w-4 h-4"></i>
                <span>{{ Auth::user()->isCustomer() ? 'Live Tracking' : 'Tracking' }}</span>
            </a>

            <!-- 4. Exceptions / Cold-Chain Alerts -->
            <a href="{{ route('exceptions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('exceptions.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                <span>{{ Auth::user()->isCustomer() ? 'Cold-Chain Alerts' : 'Exceptions' }}</span>
            </a>

            <!-- 5. Global Radar (Mata Uang, Berita Antar Benua, & Cuaca Dunia) -->
            <a href="{{ route('global-radar.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('global-radar.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="globe-2" class="w-4 h-4"></i>
                <span>Global Radar</span>
            </a>

            @if(!Auth::user()->isCustomer())
            <!-- 6. Carriers & Fleet Partners (Internal Bio Farma Logistics Only) -->
            <a href="{{ route('carriers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('carriers.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="truck" class="w-4 h-4"></i>
                <span>Carriers</span>
            </a>
            @endif

            @if(Auth::user()->isAdmin())
            <!-- 6. Reports & Audit Trail (Governance - Exclusive to Administrator) -->
            <div class="pt-4 px-3 text-[10px] uppercase font-mono tracking-wider text-teal-400/80 font-semibold">Governance</div>
            <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('audit-logs.index') ? 'bg-teal-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-white hover:bg-teal-900/40' }}">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Audit Logs</span>
            </a>
            @endif
        </div>

        <!-- User / Logout Footer -->
        <div class="p-4 border-t border-teal-900/40 bg-[#071615]">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-rose-400 hover:bg-teal-900/40 transition-all">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Log Out</span>
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
                    <input type="text" name="search" placeholder="Search consignment no, carrier, or origin..." 
                        class="w-full pl-9 pr-4 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-600 focus:bg-white transition-all">
                </form>
            </div>

            <!-- Right Controls: Telemetry Badge, Notifications & Profile -->
            <div class="flex items-center gap-4">
                <!-- Status Badge -->
                <div class="hidden lg:flex items-center gap-2 px-2.5 py-1 rounded-lg bg-teal-50 border border-teal-200/80 text-[11px] font-medium text-teal-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-600 animate-pulse"></span>
                    <span>Cold-Chain Telemetry Active</span>
                </div>

                <!-- Notifications Bell -->
                <div class="relative">
                    <button class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 relative">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                    </button>
                </div>

                <!-- User Profile -->
                <div class="flex items-center gap-3 pl-3 border-l border-slate-200">
                    <div class="w-9 h-9 rounded-full bg-teal-100 text-teal-800 font-bold flex items-center justify-center text-xs border border-teal-200 shadow-xs">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-semibold text-slate-900 leading-tight">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] text-teal-700 font-semibold uppercase tracking-wider">{{ Auth::user()->role }} &bull; {{ Auth::user()->organization ?? 'PT Bio Farma' }}</div>
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
    </script>
    @stack('scripts')
</body>
</html>
