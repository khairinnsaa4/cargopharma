<!DOCTYPE html>
<html lang="id" class="h-full bg-[#061514] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | CargoPharma Cold-Chain Logistics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="h-full flex min-h-screen bg-[#040e0d] relative overflow-x-hidden selection:bg-teal-500 selection:text-slate-950">

    <!-- Ambient Cold-Chain Background Glow across entire screen -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <img src="{{ asset('images/cargopharma-login.jpg') }}" alt="" class="w-full h-full object-cover object-center scale-105 filter blur-3xl opacity-20">
        <div class="absolute inset-0 bg-[#040e0d]/85"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#040e0d] via-[#040e0d]/90 to-transparent"></div>
    </div>

    <div class="relative z-10 flex-1 flex flex-col lg:flex-row min-h-screen">
        
        <!-- LEFT COLUMN: AUTH FORM (Glassmorphic Cold-Chain Theme - No White Background) -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-12 lg:p-16 z-10 bg-[#051615]/95 lg:bg-[#051615]/85 backdrop-blur-2xl border-r border-teal-900/30">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-500 text-slate-950 flex items-center justify-center shadow-lg shadow-teal-500/20 font-bold">
                    <i data-lucide="shield-plus" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="font-extrabold text-white text-lg tracking-tight">CargoPharma</span>
                    <span class="block text-[10px] text-teal-400 font-mono font-semibold uppercase tracking-wider">Cold-Chain Visibility</span>
                </div>
            </div>

            <!-- Form Content Box -->
            <div class="w-full max-w-sm mx-auto my-8 space-y-6">
                <div>
                    <h2 class="text-2xl font-bold text-white tracking-tight">Masuk ke Portal</h2>
                    <p class="text-xs text-teal-200/70 mt-1">Masuk dengan kredensial akun CargoPharma Anda.</p>
                </div>

                @if($errors->any())
                <div class="p-3 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-300 text-xs flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-rose-400"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-medium text-slate-300 mb-1.5">Email</label>
                        <div class="relative">
                            <i data-lucide="mail" class="w-4 h-4 text-teal-400/80 absolute left-3 top-2.5"></i>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@cargopharma.com" required autofocus
                                class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-teal-800/70 bg-[#0A2624]/80 text-white text-xs placeholder-teal-600/70 focus:outline-none focus:border-teal-400 focus:bg-[#0C2D2B] focus:ring-1 focus:ring-teal-400/50 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-300 mb-1.5">Kata Sandi</label>
                        <div class="relative">
                            <i data-lucide="lock" class="w-4 h-4 text-teal-400/80 absolute left-3 top-2.5"></i>
                            <input type="password" id="password-field" name="password" placeholder="••••••••" required 
                                class="w-full pl-9 pr-9 py-2.5 rounded-xl border border-teal-800/70 bg-[#0A2624]/80 text-white text-xs placeholder-teal-600/70 focus:outline-none focus:border-teal-400 focus:bg-[#0C2D2B] focus:ring-1 focus:ring-teal-400/50 transition-all">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-3 text-teal-400/80 hover:text-teal-200 focus:outline-none" title="Lihat/Sembunyikan">
                                <i id="eye-icon" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-slate-300 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="rounded border-teal-700 bg-[#0A2624] text-teal-500 focus:ring-0">
                            <span>Ingat saya</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs shadow-lg shadow-teal-950/60 transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span>Masuk</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </button>
                </form>

                @if(app()->environment('local'))
                <!-- Quick Demo Login -->
                <div class="pt-4 border-t border-teal-900/60 text-center space-y-2">
                    <span class="text-[10px] text-teal-400/80 block font-semibold uppercase tracking-wider font-mono">Akses Cepat (Demo):</span>
                    <div class="flex items-center justify-center gap-2">
                        <a href="{{ route('login.quick', 'admin') }}" class="px-3 py-1.5 rounded-lg bg-[#0A2624] hover:bg-teal-900/70 text-teal-100 hover:text-white font-semibold text-xs transition-colors border border-teal-800/60 flex items-center gap-1.5">
                            <i data-lucide="shield" class="w-3.5 h-3.5 text-teal-400"></i>
                            <span>Admin</span>
                        </a>
                        <a href="{{ route('login.quick', 'operator') }}" class="px-3 py-1.5 rounded-lg bg-[#0A2624] hover:bg-teal-900/70 text-teal-100 hover:text-white font-semibold text-xs transition-colors border border-teal-800/60 flex items-center gap-1.5">
                            <i data-lucide="activity" class="w-3.5 h-3.5 text-teal-400"></i>
                            <span>Operator</span>
                        </a>
                        <a href="{{ route('login.quick', 'customer') }}" class="px-3 py-1.5 rounded-lg bg-[#0A2624] hover:bg-teal-900/70 text-teal-100 hover:text-white font-semibold text-xs transition-colors border border-teal-800/60 flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-teal-400"></i>
                            <span>Customer</span>
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="text-xs text-teal-400/50 text-center sm:text-left">
                &copy; {{ date('Y') }} CargoPharma. Sistem Distribusi Rantai Dingin Farmasi.
            </div>
        </div>

        <!-- RIGHT COLUMN: VISUAL HERO SHOWCASE -->
        <div class="w-full lg:w-1/2 relative bg-[#040e0d] flex flex-col justify-between p-8 sm:p-12 overflow-hidden min-h-[420px] lg:min-h-screen">
            <!-- Background Image with Seamless Vignette & Gradients -->
            <div class="absolute inset-0 z-0">
                <img src="{{ asset('images/cargopharma-login.jpg') }}" alt="CargoPharma Cold-Chain" 
                    class="w-full h-full object-cover object-center transform hover:scale-105 transition-transform duration-1000 ease-out">
                <div class="absolute inset-0 bg-gradient-to-t from-[#040e0d] via-transparent to-[#040e0d]/50"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-[#051615] via-transparent to-black/40"></div>
            </div>

            <!-- Top Pill Indicator -->
            <div class="relative z-10 flex justify-between items-center">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/80 backdrop-blur-md border border-teal-500/30 text-teal-300 text-xs font-semibold shadow-lg">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Telemetri Rantai Dingin Aktif (-70°C ~ 8°C)</span>
                </div>

                <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-teal-900/70 backdrop-blur-md border border-teal-400/30 text-teal-200 text-xs font-mono">
                    <i data-lucide="plane" class="w-3.5 h-3.5 text-teal-300"></i>
                    <span>Bio Farma Global Network</span>
                </div>
            </div>

            <!-- Bottom Caption & Metrics Showcase -->
            <div class="relative z-10 space-y-4 pt-16 lg:pt-0">
                <div class="space-y-2 max-w-lg">
                    <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase font-mono bg-teal-500/20 text-teal-300 border border-teal-400/30">
                        Presisi Distribusi Vaksin
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">
                        Visibilitas Real-time Logistik Rantai Dingin Global
                    </h2>
                    <p class="text-xs sm:text-sm text-teal-100/80 leading-relaxed">
                        Integritas termal presisi tinggi untuk kargo vaksin, obat-obatan esensial, dan produk biologi lintas benua.
                    </p>
                </div>

                <!-- Feature Highlights Badges -->
                <div class="grid grid-cols-3 gap-2.5 pt-2 max-w-md">
                    <div class="p-2.5 rounded-xl bg-slate-900/70 backdrop-blur-md border border-teal-500/30 text-center">
                        <span class="block text-xs font-bold font-mono text-emerald-400">-70°C &bull; 2°-8°C</span>
                        <span class="text-[10px] text-slate-300 font-medium">Ultra-Cold Storage</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-900/70 backdrop-blur-md border border-teal-500/30 text-center">
                        <span class="block text-xs font-bold font-mono text-teal-300">100% Validated</span>
                        <span class="text-[10px] text-slate-300 font-medium">WHO GDP Compliant</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-900/70 backdrop-blur-md border border-teal-500/30 text-center">
                        <span class="block text-xs font-bold font-mono text-cyan-300">Multimodal</span>
                        <span class="text-[10px] text-slate-300 font-medium">Udara, Darat &amp; Laut</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
        lucide.createIcons();

        function togglePasswordVisibility() {
            const field = document.getElementById('password-field');
            const icon = document.getElementById('eye-icon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                field.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>
