<!DOCTYPE html>
<html lang="en" class="h-full bg-[#0B0F19] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'CargoPharma' }} | Global Cold-Chain Visibility Platform</title>

    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#ecfeff',
                            400: '#22d3ee',
                            500: '#06b6d4',
                            600: '#0891b2',
                            700: '#0e7490',
                        },
                        cryo: '#38bdf8',
                        bio: '#10b981',
                        alert: '#f59e0b',
                        danger: '#ef4444',
                        dark: {
                            950: '#070a12',
                            900: '#0B0F19',
                            850: '#0f172a',
                            800: '#1e293b',
                            700: '#334155',
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

    <!-- Leaflet.js CSS & JS for Interactive Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0b0f19; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
        .glow-cyan { box-shadow: 0 0 20px -3px rgba(6, 182, 212, 0.35); }
        .glow-emerald { box-shadow: 0 0 20px -3px rgba(16, 185, 129, 0.35); }
        .glow-danger { box-shadow: 0 0 20px -3px rgba(239, 68, 68, 0.35); }
        .grid-pattern {
            background-image: radial-gradient(rgba(30, 41, 59, 0.5) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="h-full flex overflow-hidden grid-pattern">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#0F172A]/90 backdrop-blur-xl border-r border-slate-800/80 flex flex-col shrink-0 z-20">
        <!-- Logo Header -->
        <div class="h-16 flex items-center px-6 border-b border-slate-800/80 gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-600 to-cyan-400 flex items-center justify-center glow-cyan shadow-lg shadow-cyan-500/20">
                <i data-lucide="snowflake" class="w-5 h-5 text-slate-950 font-bold"></i>
            </div>
            <div>
                <span class="font-extrabold tracking-tight text-white text-lg">Cargo<span class="text-cyan-400">Pharma</span></span>
                <span class="block text-[10px] font-mono text-cyan-400 tracking-wider uppercase font-semibold">Cold-Chain OS</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 overflow-y-auto px-4 py-6 space-y-1.5">
            <div class="px-3 pb-2 text-[10px] font-mono uppercase tracking-wider text-slate-400 font-semibold">Operations</div>
            
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>Mission Control</span>
            </a>

            <a href="{{ route('shipments.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('shipments.index') || request()->routeIs('shipments.show') ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                <i data-lucide="plane-takeoff" class="w-4 h-4"></i>
                <span>Consignments</span>
            </a>

            @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
            <a href="{{ route('shipments.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('shipments.create') ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                <i data-lucide="package-plus" class="w-4 h-4"></i>
                <span>Dispatch Vaccine</span>
            </a>
            @endif

            @if(Auth::user()->isAdmin())
            <div class="pt-6 px-3 pb-2 text-[10px] font-mono uppercase tracking-wider text-slate-400 font-semibold">Governance</div>
            <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('audit-logs.index') ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>Audit Trail</span>
            </a>
            @endif
        </div>

        <!-- System Cold-Chain Widget -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            <div class="rounded-xl bg-slate-900/90 border border-slate-800 p-3 space-y-2">
                <div class="flex items-center justify-between text-xs font-mono">
                    <span class="text-slate-400">Hub Telemetry</span>
                    <span class="flex items-center gap-1.5 text-emerald-400 font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> ONLINE
                    </span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Cryo-Standard:</span>
                    <span class="font-mono text-cyan-300 font-semibold">-20°C ± 5°C</span>
                </div>
                <div class="text-[10px] text-slate-400 font-mono truncate">
                    Node: PT Bio Farma Bandung
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- TOPBAR -->
        <header class="h-16 bg-[#0F172A]/80 backdrop-blur-xl border-b border-slate-800/80 px-6 flex items-center justify-between shrink-0 z-10">
            <!-- Left: Ticker & Page Header -->
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-950/60 border border-cyan-800/40 text-xs text-cyan-300 font-mono">
                    <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-cyan-400"></i>
                    <span>BIO FARMA EXPORT PROTOCOL ACTIVE</span>
                </div>
            </div>

            <!-- Right: Role Switcher & User Profile -->
            <div class="flex items-center gap-3">
                <!-- FAST DEMO ROLE SWITCHER (For Lecturer/Examiner) -->
                <div class="hidden lg:flex items-center bg-slate-900/90 border border-slate-800 rounded-xl p-1 text-xs">
                    <span class="px-2 text-[10px] font-mono text-slate-400 uppercase font-bold">Demo Persona:</span>
                    <a href="{{ route('login.quick', 'admin') }}" title="Switch to Admin" class="px-2.5 py-1 rounded-lg transition-all {{ Auth::user()->role === 'admin' ? 'bg-cyan-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">Admin</a>
                    <a href="{{ route('login.quick', 'operator') }}" title="Switch to Operator" class="px-2.5 py-1 rounded-lg transition-all {{ Auth::user()->role === 'operator' ? 'bg-cyan-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">Operator</a>
                    <a href="{{ route('login.quick', 'customer') }}" title="Switch to Customer (UNICEF)" class="px-2.5 py-1 rounded-lg transition-all {{ Auth::user()->role === 'customer' ? 'bg-cyan-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">Customer</a>
                </div>

                <!-- Current User Badge -->
                <div class="flex items-center gap-3 pl-3 border-l border-slate-800">
                    <div class="text-right hidden sm:block">
                        <div class="text-xs font-semibold text-white leading-tight">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] font-mono uppercase text-cyan-400 font-medium">{{ Auth::user()->organization ?? Auth::user()->role }}</div>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-slate-800 to-slate-700 border border-slate-700 flex items-center justify-center text-xs font-bold font-mono text-cyan-300">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- FLASH ALERTS -->
        @if(session('success'))
        <div class="mx-6 mt-4 p-3.5 rounded-xl bg-emerald-950/60 border border-emerald-800/60 text-emerald-300 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        @endif

        @if(session('warning'))
        <div class="mx-6 mt-4 p-3.5 rounded-xl bg-amber-950/60 border border-amber-800/60 text-amber-300 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-400"></i>
                <span>{{ session('warning') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-amber-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        @endif

        @if($errors->any())
        <div class="mx-6 mt-4 p-3.5 rounded-xl bg-rose-950/60 border border-rose-800/60 text-rose-300 text-xs">
            <div class="flex items-center gap-2 mb-1 font-semibold">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-400"></i>
                <span>Validation Failure:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-[11px] pl-2 text-rose-300/90">
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
