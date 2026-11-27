<!DOCTYPE html>
<html lang="en" class="h-full">
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

        /* ── Animated background: teal gradient + particle network ── */
        @keyframes bgBreath {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.82; }
        }
        @keyframes bgShift {
            0%   { background-position: 0% 50%, 100% 50%, 0 0; }
            50%  { background-position: 100% 50%, 0% 50%, 0 0; }
            100% { background-position: 0% 50%, 100% 50%, 0 0; }
        }

        .main-bg {
            position: relative;
            background-color: #edfaf6;
            background-image:
                radial-gradient(ellipse 80% 65% at 10% 5%,  rgba(13,148,136,0.20) 0%, transparent 65%),
                radial-gradient(ellipse 60% 55% at 88% 88%, rgba(20,184,166,0.15) 0%, transparent 65%),
                radial-gradient(ellipse 50% 45% at 55% 48%, rgba(5,150,105,0.09) 0%, transparent 70%);
            background-size: 200% 200%, 200% 200%, 100% 100%;
            animation: bgShift 14s ease-in-out infinite;
        }

        /* Canvas particle network — fixed behind everything */
        #bg-canvas {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        /* Semua konten di atas canvas */
        body > * { position: relative; z-index: 1; }

        /* ── Scrollbar: teal-tinted ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(13,148,136,0.06); }
        ::-webkit-scrollbar-thumb { background: rgba(20,184,166,0.30); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(20,184,166,0.55); }

        /* ── Leaflet map cursor ── */
        .leaflet-container { cursor: grab !important; }
        .leaflet-container:active,
        .leaflet-dragging,
        .leaflet-dragging .leaflet-grab,
        .leaflet-dragging .leaflet-interactive { cursor: grabbing !important; }

        /* ══════════════════════════════════════════
           GLOBAL DARK-TEAL THEME OVERRIDES
           Kartu, tabel, form, badge — semua selaras
        ══════════════════════════════════════════ */

        /* Base text */
        body { color: #0f2d2b; }

        /* ── Topbar ── */
        header.topbar {
            background: rgba(255,255,255,0.78);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(13,148,136,0.18);
            box-shadow: 0 1px 12px rgba(13,148,136,0.08);
        }

        /* ── White cards → glass teal ── */
        .bg-white {
            background: rgba(255,255,255,0.72) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* ── Stat cards border glow ── */
        .border-slate-200,
        .border-slate-200\/90 {
            border-color: rgba(13,148,136,0.18) !important;
        }

        /* ── Tables ── */
        table { border-collapse: separate; border-spacing: 0; }
        thead th {
            background: rgba(13,148,136,0.09) !important;
            color: #0f766e !important;
            font-size: 0.7rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(13,148,136,0.18) !important;
        }
        tbody tr {
            background: rgba(255,255,255,0.65) !important;
            transition: background 0.15s;
        }
        tbody tr:hover { background: rgba(20,184,166,0.08) !important; }
        tbody td {
            border-bottom: 1px solid rgba(13,148,136,0.09) !important;
            color: #134e4a;
        }

        /* ── Form inputs ── */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="search"],
        input[type="date"],
        select,
        textarea {
            background: rgba(255,255,255,0.75) !important;
            border-color: rgba(13,148,136,0.22) !important;
            color: #0f2d2b !important;
        }
        input:focus, select:focus, textarea:focus {
            border-color: rgba(13,148,136,0.55) !important;
            box-shadow: 0 0 0 3px rgba(20,184,166,0.12) !important;
            outline: none !important;
        }

        /* ── Badges & pills (slate) → teal ── */
        .bg-slate-100 { background: rgba(20,184,166,0.10) !important; }
        .text-slate-600 { color: #0f766e !important; }
        .text-slate-500 { color: #0d9488 !important; }
        .text-slate-700 { color: #0f2d2b !important; }
        .text-slate-800 { color: #0a1e1d !important; }
        .text-slate-900 { color: #071615 !important; }

        /* ── Flash notifications ── */
        .flash-success {
            background: rgba(16,185,129,0.10);
            border-color: rgba(16,185,129,0.28);
            color: #065f46;
        }
        .flash-warning {
            background: rgba(245,158,11,0.10);
            border-color: rgba(245,158,11,0.28);
            color: #78350f;
        }
        .flash-error {
            background: rgba(239,68,68,0.10);
            border-color: rgba(239,68,68,0.28);
            color: #7f1d1d;
        }

        /* ── Buttons: primary teal glow ── */
        .btn-teal,
        button[type="submit"]:not(.no-style),
        a.btn-primary {
            transition: box-shadow 0.2s, transform 0.15s;
        }
        .bg-teal-600:hover, .bg-teal-500:hover {
            box-shadow: 0 4px 18px rgba(20,184,166,0.35);
        }

        /* ── Page section headings ── */
        h1, h2, h3 { color: #0a1e1d; }

        /* ── Rounded panels / section boxes ── */
        .rounded-xl, .rounded-2xl {
            box-shadow: 0 2px 16px rgba(13,148,136,0.07);
        }

        /* ── Live Monitoring badge ── */
        .live-badge {
            background: rgba(20,184,166,0.12);
            border: 1px solid rgba(20,184,166,0.30);
            color: #0f766e;
        }

        /* ── Topbar search ── */
        .search-topbar {
            background: rgba(255,255,255,0.65) !important;
            border: 1px solid rgba(13,148,136,0.22) !important;
        }
        .search-topbar:focus {
            background: rgba(255,255,255,0.90) !important;
            border-color: rgba(13,148,136,0.50) !important;
        }

        /* ── Main scrollable area subtle tint ── */
        main { background: transparent; }
    </style>
</head>
<body class="h-full flex overflow-hidden main-bg">

    <!-- PARTICLE NETWORK CANVAS BACKGROUND -->
    <canvas id="bg-canvas"></canvas>
    <script>
    (function() {
        const canvas = document.getElementById('bg-canvas');
        const ctx    = canvas.getContext('2d');

        // Particle config — teal palette
        const CFG = {
            count:       52,
            connectDist: 155,
            speed:       0.38,
            dotR:        2.2,
            lineAlpha:   0.13,
            dotAlpha:    0.28,
            colors:      ['#0d9488','#14b8a6','#0f766e','#5eead4','#0891b2'],
        };

        let W, H, particles = [];

        function resize() {
            W = canvas.width  = window.innerWidth;
            H = canvas.height = window.innerHeight;
        }

        function rand(min, max) { return Math.random() * (max - min) + min; }

        function spawn() {
            return {
                x:  rand(0, W), y:  rand(0, H),
                vx: rand(-CFG.speed, CFG.speed) || 0.1,
                vy: rand(-CFG.speed, CFG.speed) || 0.1,
                r:  rand(1.4, CFG.dotR),
                color: CFG.colors[Math.floor(Math.random() * CFG.colors.length)],
                pulse: rand(0, Math.PI * 2),
            };
        }

        function init() {
            resize();
            particles = Array.from({ length: CFG.count }, spawn);
        }

        function draw() {
            ctx.clearRect(0, 0, W, H);

            // Update & draw particles
            for (let i = 0; i < particles.length; i++) {
                const p = particles[i];
                p.x += p.vx; p.y += p.vy;
                p.pulse += 0.025;

                // Bounce off edges
                if (p.x < 0 || p.x > W) p.vx *= -1;
                if (p.y < 0 || p.y > H) p.vy *= -1;

                // Draw node with pulse glow
                const glow = 1 + 0.3 * Math.sin(p.pulse);
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r * glow, 0, Math.PI * 2);
                ctx.fillStyle = p.color;
                ctx.globalAlpha = CFG.dotAlpha * glow;
                ctx.fill();

                // Draw connections
                for (let j = i + 1; j < particles.length; j++) {
                    const q   = particles[j];
                    const dx  = p.x - q.x, dy = p.y - q.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < CFG.connectDist) {
                        const alpha = CFG.lineAlpha * (1 - dist / CFG.connectDist);
                        ctx.beginPath();
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(q.x, q.y);
                        ctx.strokeStyle = p.color;
                        ctx.globalAlpha = alpha;
                        ctx.lineWidth   = 0.8;
                        ctx.stroke();
                    }
                }
            }

            ctx.globalAlpha = 1;
            requestAnimationFrame(draw);
        }

        window.addEventListener('resize', init);
        init();
        draw();
    })();
    </script>

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
        <header class="topbar h-16 px-6 flex items-center justify-between shrink-0 z-10">
            <!-- Search Input -->
            <div class="flex items-center gap-4 flex-1 max-w-md">
                <form action="{{ route('shipments.index') }}" method="GET" class="w-full relative">
                    <i data-lucide="search" class="w-4 h-4 text-teal-500/60 absolute left-3 top-2.5"></i>
                    <input type="text" name="search" placeholder="Cari resi, kurir, rute..."
                        class="search-topbar w-full pl-9 pr-4 py-1.5 rounded-lg text-xs text-slate-800 placeholder-slate-400 transition-all">
                </form>
            </div>

            <!-- Right Controls: Telemetry Badge, Notifications & Profile -->
            <div class="flex items-center gap-3">
                <!-- Status Badge -->
                <div class="live-badge flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                    <span>Live Monitoring</span>
                </div>

                <!-- User Profile -->
                <div class="flex items-center gap-2.5 pl-3 border-l border-teal-200/60">
                    <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-700 font-bold flex items-center justify-center text-xs border border-teal-200 shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-semibold text-slate-800 leading-tight">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] text-teal-600 font-bold uppercase">{{ Auth::user()->role }}</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- FLASH NOTIFICATIONS -->
        @if(session('success'))
        <div class="mx-6 mt-4 p-3 rounded-lg flash-success border text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
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
