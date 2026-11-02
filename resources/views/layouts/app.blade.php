<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100 text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'CargoTrace' }} | Enterprise Shipment Visibility</title>

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
                        navy: {
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#0a0f1d',
                        },
                        brand: {
                            50: '#f0f9ff',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
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
<body class="h-full flex overflow-hidden bg-[#F4F6F9]">

    <!-- CORPORATE SIDEBAR (Deep Navy #0E1B2E) -->
    <aside class="w-64 bg-[#0E1B2E] text-slate-300 flex flex-col shrink-0 z-20 shadow-xl">
        <!-- Brand Header -->
        <div class="h-16 flex items-center px-6 gap-3 border-b border-slate-800/80">
            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-sm">
                <i data-lucide="container" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="font-bold text-white text-base tracking-tight">CargoTrace</span>
                <span class="block text-[10px] text-slate-400 font-mono">Visibility Platform</span>
            </div>
        </div>

        <!-- Navigation Menus (Matching Photo's Essential Architecture) -->
        <div class="flex-1 overflow-y-auto px-4 py-5 space-y-1">
            <!-- 1. Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>Dashboard</span>
            </a>

            <!-- 2. Shipments -->
            <a href="{{ route('shipments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('shipments.index') || request()->routeIs('shipments.show') || request()->routeIs('shipments.create') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span>Shipments</span>
            </a>

            <!-- 3. Tracking (Dedicated Live Map & Checkpoint View) -->
            <a href="{{ route('tracking.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('tracking.index') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="map-pin" class="w-4 h-4"></i>
                <span>Tracking</span>
            </a>

            <!-- 4. Exceptions (Incident & Delay Management) -->
            <a href="{{ route('exceptions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('exceptions.index') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                <span>Exceptions</span>
            </a>

            <!-- 5. Carriers & Fleet Partners -->
            <a href="{{ route('carriers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('carriers.index') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="truck" class="w-4 h-4"></i>
                <span>Carriers</span>
            </a>

            @if(Auth::user()->isAdmin())
            <!-- 6. Reports & Audit Trail (Governance) -->
            <div class="pt-4 px-3 text-[10px] uppercase font-mono tracking-wider text-slate-400 font-semibold">Governance</div>
            <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('audit-logs.index') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Audit Logs</span>
            </a>
            @endif
        </div>

        <!-- User / Logout Footer -->
        <div class="p-4 border-t border-slate-800/80 bg-[#091322]">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-rose-400 hover:bg-slate-800/60 transition-all">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN BODY WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- TOPBAR (White Clean Enterprise Header) -->
        <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0 z-10 shadow-xs">
            <!-- Search Input (as in photo) -->
            <div class="flex items-center gap-4 flex-1 max-w-md">
                <form action="{{ route('shipments.index') }}" method="GET" class="w-full relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <input type="text" name="search" placeholder="Search consignment no, carrier, or origin..." 
                        class="w-full pl-9 pr-4 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
                </form>
            </div>

            <!-- Right Controls: Fast Persona Switcher & Profile -->
            <div class="flex items-center gap-4">
                <!-- Role Switcher for Lecturer Review -->
                <div class="hidden md:flex items-center bg-slate-100 rounded-lg p-1 text-xs border border-slate-200">
                    <span class="px-2 text-[10px] font-mono text-slate-500 uppercase font-semibold">Role:</span>
                    <a href="{{ route('login.quick', 'admin') }}" class="px-2 py-0.5 rounded transition-all {{ Auth::user()->role === 'admin' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">Admin</a>
                    <a href="{{ route('login.quick', 'operator') }}" class="px-2 py-0.5 rounded transition-all {{ Auth::user()->role === 'operator' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">Operator</a>
                    <a href="{{ route('login.quick', 'customer') }}" class="px-2 py-0.5 rounded transition-all {{ Auth::user()->role === 'customer' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">Customer</a>
                </div>

                <!-- Notifications Bell -->
                <div class="relative">
                    <button class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 relative">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                    </button>
                </div>

                <!-- User Profile (as in photo: Initials avatar + Sarah Johnson style) -->
                <div class="flex items-center gap-3 pl-3 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs border border-blue-200">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-semibold text-slate-800 leading-tight">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] text-slate-500 capitalize">{{ Auth::user()->organization ?? Auth::user()->role }}</div>
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
