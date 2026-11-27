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
        @keyframes bgShift {
            0%   { background-position: 0% 50%, 100% 50%, 50% 0%, 0 0, 0 0; }
            50%  { background-position: 100% 50%, 0% 50%, 50% 100%, 0 0, 0 0; }
            100% { background-position: 0% 50%, 100% 50%, 50% 0%, 0 0, 0 0; }
        }

        .main-bg {
            position: relative;
            background-color: #03100f;
            background-image:
                radial-gradient(ellipse 75% 60% at 12% 8%,  rgba(20,184,166,0.34) 0%, transparent 62%),
                radial-gradient(ellipse 65% 55% at 88% 88%, rgba(6,182,212,0.26) 0%, transparent 62%),
                radial-gradient(ellipse 50% 45% at 52% 48%, rgba(16,185,129,0.18) 0%, transparent 68%),
                linear-gradient(rgba(20,184,166,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(20,184,166,0.05) 1px, transparent 1px);
            background-size: 200% 200%, 200% 200%, 160% 160%, 44px 44px, 44px 44px;
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

        /* ── Scrollbar: teal-tinted dark ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #041312; }
        ::-webkit-scrollbar-thumb { background: rgba(20,184,166,0.45); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(45,212,191,0.80); }

        /* ── Leaflet map cursor & dark controls/popups ── */
        .leaflet-container { cursor: grab !important; background: #041312 !important; }
        .leaflet-container:active,
        .leaflet-dragging,
        .leaflet-dragging .leaflet-grab,
        .leaflet-dragging .leaflet-interactive { cursor: grabbing !important; }
        .leaflet-bar a {
            background-color: rgba(6, 26, 24, 0.92) !important;
            color: #5eead4 !important;
            border-bottom: 1px solid rgba(20, 184, 166, 0.28) !important;
        }
        .leaflet-bar a:hover {
            background-color: #0d9488 !important;
            color: #ffffff !important;
        }
        .leaflet-popup-content-wrapper,
        .leaflet-popup-tip {
            background: #071d1b !important;
            color: #e2e8f0 !important;
            border: 1px solid rgba(20, 184, 166, 0.30) !important;
        }
        .leaflet-tooltip {
            background: rgba(5, 22, 21, 0.94) !important;
            color: #ccfbf1 !important;
            border: 1px solid rgba(20, 184, 166, 0.40) !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5) !important;
        }

        /* ══════════════════════════════════════════════
           FULL STRIKING DARK-TEAL THEME — ZERO WHITE
           Semua elemen mengikuti palet obsidian + neon teal/cyan
        ══════════════════════════════════════════════ */

        html, body { color: #d1fae5; }

        /* ── Topbar: glowing dark glass ── */
        header.topbar {
            background: rgba(4, 18, 17, 0.86);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(45, 212, 191, 0.25);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.45), 0 1px 0 rgba(45, 212, 191, 0.12);
        }

        /* ── Override semua latar putih & terang → dark glass teal ── */
        .bg-white,
        .bg-white\/95,
        .bg-white\/90,
        .bg-white\/80 {
            background: rgba(8, 29, 27, 0.88) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-color: rgba(20, 184, 166, 0.24) !important;
        }
        .bg-slate-50,
        .bg-slate-50\/50,
        .bg-slate-50\/60,
        .bg-slate-50\/80,
        .bg-gray-50 {
            background: rgba(6, 22, 21, 0.82) !important;
        }
        .bg-slate-100,
        .bg-gray-100 {
            background: rgba(20, 184, 166, 0.13) !important;
        }
        .bg-slate-200 {
            background: rgba(20, 184, 166, 0.20) !important;
        }

        /* ── Light Gradient Boxes → Vibrant Dark Teal/Cyan Gradient ── */
        [class*="from-teal-50"],
        [class*="from-slate-50"],
        [class*="from-emerald-50"],
        [class*="from-cyan-50"] {
            background-image: linear-gradient(135deg, rgba(13, 148, 136, 0.24), rgba(6, 182, 212, 0.14)) !important;
            background-color: rgba(8, 29, 27, 0.90) !important;
            border-color: rgba(45, 212, 191, 0.30) !important;
        }

        /* ── Hover states that used to flash light/white ── */
        .hover\:bg-slate-50:hover,
        .hover\:bg-slate-50\/60:hover,
        .hover\:bg-slate-50\/80:hover,
        .hover\:bg-slate-100:hover,
        .hover\:bg-teal-50:hover,
        .hover\:bg-teal-100:hover {
            background: rgba(20, 184, 166, 0.22) !important;
        }
        .hover\:bg-rose-50:hover {
            background: rgba(244, 63, 94, 0.22) !important;
        }
        .hover\:bg-rose-100:hover {
            background: rgba(244, 63, 94, 0.28) !important;
        }
        .hover\:text-teal-700:hover,
        .hover\:text-teal-900:hover {
            color: #5eead4 !important;
        }

        /* ── Border & Divider overrides ── */
        .border-slate-100,
        .border-slate-200,
        .border-slate-200\/60,
        .border-slate-200\/80,
        .border-slate-200\/90,
        .border-slate-300,
        .border-gray-200,
        .border-gray-300 {
            border-color: rgba(20, 184, 166, 0.22) !important;
        }
        .divide-slate-100 > * + *,
        .divide-slate-200 > * + * {
            border-color: rgba(20, 184, 166, 0.14) !important;
        }

        /* ── Text overrides (membuat semua teks terbaca jelas & mencolok) ── */
        .text-slate-900, .text-gray-900 { color: #f0fdfa !important; }
        .text-slate-800, .text-gray-800 { color: #ccfbf1 !important; }
        .text-slate-700, .text-gray-700 { color: #99f6e4 !important; }
        .text-slate-600, .text-gray-600 { color: #5eead4 !important; }
        .text-slate-500, .text-gray-500 { color: #94a3b8 !important; }
        .text-slate-400 { color: #7dd3c8 !important; }
        .text-slate-300 { color: #cbd5e1 !important; }
        .text-teal-950, .text-teal-900, .text-teal-800, .text-teal-700 { color: #2dd4bf !important; }
        .text-indigo-900, .text-indigo-700 { color: #a5b4fc !important; }
        .text-blue-900, .text-blue-800, .text-blue-700 { color: #60a5fa !important; }
        .text-sky-800, .text-sky-700 { color: #38bdf8 !important; }
        .text-orange-600, .text-orange-700 { color: #fb923c !important; }

        /* ── Tables: full dark glowing ── */
        table { border-collapse: separate; border-spacing: 0; width: 100%; }
        thead tr { background: rgba(4, 18, 17, 0.85) !important; }
        thead th {
            background: transparent !important;
            color: #2dd4bf !important;
            font-size: 0.68rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 700;
            border-bottom: 1px solid rgba(45, 212, 191, 0.25) !important;
            padding: 10px 12px;
        }
        tbody tr { background: rgba(8, 29, 27, 0.60) !important; transition: background 0.15s; }
        tbody tr:hover { background: rgba(20, 184, 166, 0.14) !important; }
        tbody td {
            border-bottom: 1px solid rgba(20, 184, 166, 0.10) !important;
            color: #cbd5e1 !important;
            padding: 10px 12px;
        }

        /* ── Form inputs: dark glowing ── */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="search"],
        input[type="date"],
        input[type="datetime-local"],
        select,
        textarea {
            background: rgba(4, 18, 17, 0.88) !important;
            border-color: rgba(20, 184, 166, 0.28) !important;
            color: #f0fdfa !important;
            border-radius: 0.5rem;
        }
        input::placeholder, textarea::placeholder { color: rgba(148, 163, 184, 0.55) !important; }
        input:focus, select:focus, textarea:focus {
            border-color: rgba(45, 212, 191, 0.75) !important;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.22) !important;
            outline: none !important;
            background: rgba(5, 22, 21, 0.98) !important;
        }
        select option { background: #071b19; color: #f0fdfa; }
        label { color: #99f6e4 !important; }

        /* ── Cards & panels: glowing dark glass ── */
        .rounded-xl, .rounded-2xl {
            box-shadow: 0 6px 28px rgba(0, 0, 0, 0.42),
                        0 0 0 1px rgba(45, 212, 191, 0.16),
                        inset 0 1px 0 rgba(94, 234, 212, 0.08);
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .rounded-xl:hover, .rounded-2xl:hover {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.50),
                        0 0 0 1px rgba(45, 212, 191, 0.30),
                        0 0 22px rgba(20, 184, 166, 0.10),
                        inset 0 1px 0 rgba(94, 234, 212, 0.14);
        }

        /* ── Stat card & tinted icon containers (mencolok) ── */
        .bg-teal-50   { background: rgba(20, 184, 166, 0.20) !important; }
        .bg-emerald-50 { background: rgba(16, 185, 129, 0.20) !important; }
        .bg-cyan-50    { background: rgba(6, 182, 212, 0.20) !important; }
        .bg-sky-50     { background: rgba(14, 165, 233, 0.20) !important; }
        .bg-rose-50, .bg-rose-50\/50 { background: rgba(244, 63, 94, 0.18) !important; }
        .bg-amber-50   { background: rgba(245, 158, 11, 0.20) !important; }
        .bg-orange-50  { background: rgba(249, 115, 22, 0.20) !important; }
        .bg-indigo-50  { background: rgba(99, 102, 241, 0.20) !important; }
        .bg-violet-50  { background: rgba(139, 92, 246, 0.20) !important; }
        .bg-blue-50    { background: rgba(59, 130, 246, 0.20) !important; }

        /* ── Badges ── */
        .bg-emerald-100 { background: rgba(16, 185, 129, 0.22) !important; }
        .text-emerald-700, .text-emerald-800 { color: #34d399 !important; }
        .bg-rose-100   { background: rgba(244, 63, 94, 0.22) !important; }
        .text-rose-600, .text-rose-700, .text-rose-800 { color: #fb7185 !important; }
        .bg-amber-100  { background: rgba(245, 158, 11, 0.22) !important; }
        .text-amber-700, .text-amber-800 { color: #fbbf24 !important; }
        .bg-cyan-100   { background: rgba(6, 182, 212, 0.22) !important; }
        .text-cyan-700, .text-cyan-800 { color: #22d3ee !important; }
        .bg-blue-100   { background: rgba(59, 130, 246, 0.22) !important; }
        .bg-indigo-100 { background: rgba(99, 102, 241, 0.22) !important; }
        .bg-teal-100, .bg-teal-100\/70 { background: rgba(20, 184, 166, 0.22) !important; }

        .border-emerald-100, .border-emerald-200, .border-emerald-300 { border-color: rgba(16, 185, 129, 0.35) !important; }
        .border-rose-100, .border-rose-200, .border-rose-200\/60 { border-color: rgba(244, 63, 94, 0.35) !important; }
        .border-amber-100, .border-amber-200 { border-color: rgba(245, 158, 11, 0.35) !important; }
        .border-orange-100, .border-orange-200 { border-color: rgba(249, 115, 22, 0.35) !important; }
        .border-teal-100, .border-teal-200, .border-teal-200\/80, .border-teal-200\/90, .border-teal-300 { border-color: rgba(45, 212, 191, 0.35) !important; }
        .border-cyan-100, .border-cyan-200 { border-color: rgba(6, 182, 212, 0.35) !important; }
        .border-sky-100, .border-sky-200 { border-color: rgba(14, 165, 233, 0.35) !important; }
        .border-indigo-100, .border-indigo-200, .border-indigo-300 { border-color: rgba(99, 102, 241, 0.35) !important; }
        .border-blue-100, .border-blue-200, .border-blue-300 { border-color: rgba(59, 130, 246, 0.35) !important; }

        /* ── Buttons: vibrant neon teal glow on hover ── */
        .bg-teal-600 {
            background: linear-gradient(135deg, #0d9488, #14b8a6) !important;
            box-shadow: 0 2px 14px rgba(20, 184, 166, 0.30);
        }
        .bg-teal-600:hover, .bg-teal-500:hover {
            box-shadow: 0 0 24px rgba(45, 212, 191, 0.55);
            transform: translateY(-1px);
        }
        .bg-rose-500:hover, .bg-red-500:hover {
            box-shadow: 0 0 18px rgba(244, 63, 94, 0.48);
        }

        /* ── Page headings ── */
        h1, h2, h3, h4 { color: #f0fdfa !important; }
        p { color: #94a3b8; }

        /* ── Flash notifications ── */
        .flash-success {
            background: rgba(16, 185, 129, 0.16) !important;
            border-color: rgba(16, 185, 129, 0.40) !important;
            color: #6ee7b7 !important;
        }
        .flash-warning {
            background: rgba(245, 158, 11, 0.16) !important;
            border-color: rgba(245, 158, 11, 0.40) !important;
            color: #fcd34d !important;
        }
        .flash-error {
            background: rgba(244, 63, 94, 0.16) !important;
            border-color: rgba(244, 63, 94, 0.40) !important;
            color: #fda4af !important;
        }

        /* ── Live badge ── */
        .live-badge {
            background: rgba(20, 184, 166, 0.18);
            border: 1px solid rgba(45, 212, 191, 0.45);
            color: #5eead4;
            box-shadow: 0 0 14px rgba(20, 184, 166, 0.18);
        }

        /* ── Topbar search ── */
        .search-topbar {
            background: rgba(4, 18, 17, 0.80) !important;
            border: 1px solid rgba(45, 212, 191, 0.28) !important;
            color: #f0fdfa !important;
        }
        .search-topbar::placeholder { color: rgba(148, 163, 184, 0.60) !important; }
        .search-topbar:focus {
            background: rgba(5, 22, 21, 0.98) !important;
            border-color: rgba(45, 212, 191, 0.65) !important;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.18) !important;
        }

        /* ── Smooth card entry animation ── */
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .rounded-xl, .rounded-2xl { animation: cardIn 0.4s ease both; }
    </style>
</head>
<body class="h-full flex overflow-hidden main-bg">

    <!-- PARTICLE NETWORK CANVAS BACKGROUND -->
    <canvas id="bg-canvas"></canvas>
    <script>
    (function() {
        const canvas = document.getElementById('bg-canvas');
        const ctx    = canvas.getContext('2d');

        const CFG = {
            count:       60,
            connectDist: 160,
            speed:       0.42,
            dotR:        2.6,
            lineAlpha:   0.22,
            dotAlpha:    0.55,
            colors:      ['#14b8a6','#2dd4bf','#0d9488','#5eead4','#06b6d4','#34d399'],
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
                    <i data-lucide="search" class="w-4 h-4 text-teal-400/80 absolute left-3 top-2.5"></i>
                    <input type="text" name="search" placeholder="Cari resi, kurir, rute..."
                        class="search-topbar w-full pl-9 pr-4 py-1.5 rounded-lg text-xs transition-all">
                </form>
            </div>

            <!-- Right Controls: Telemetry Badge, Notifications & Profile -->
            <div class="flex items-center gap-3">
                <!-- Status Badge -->
                <div class="live-badge flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                    <span>Live Monitoring</span>
                </div>

                <!-- User Profile -->
                <div class="flex items-center gap-2.5 pl-3 border-l border-teal-700/40">
                    <div class="w-8 h-8 rounded-full bg-teal-500/20 text-teal-300 font-bold flex items-center justify-center text-xs border border-teal-500/40 shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-semibold text-slate-200 leading-tight">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] text-teal-400 font-bold uppercase">{{ Auth::user()->role }}</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- FLASH NOTIFICATIONS -->
        @if(session('success'))
        <div class="mx-6 mt-4 p-3 rounded-lg flash-success border text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
        </div>
        @endif

        @if(session('warning'))
        <div class="mx-6 mt-4 p-3 rounded-lg flash-warning border text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                <span>{{ session('warning') }}</span>
            </div>
            <button onclick="this.parentElement.remove()"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
        </div>
        @endif

        @if($errors->any())
        <div class="mx-6 mt-4 p-3 rounded-lg flash-error border text-xs">
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
