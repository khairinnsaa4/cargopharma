<!DOCTYPE html>
<html lang="en" class="h-full bg-[#0B0F19] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CargoPharma Mission Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .grid-pattern {
            background-image: radial-gradient(rgba(34, 211, 238, 0.15) 1px, transparent 1px);
            background-size: 28px 28px;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6 grid-pattern relative overflow-y-auto">

    <!-- Glowing Background Aura -->
    <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10 my-auto">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 to-cyan-400 items-center justify-center shadow-lg shadow-cyan-500/20 mb-4 border border-cyan-300/30">
                <i data-lucide="snowflake" class="w-8 h-8 text-slate-950"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Cargo<span class="text-cyan-400">Pharma</span></h1>
            <p class="text-xs text-slate-400 mt-1 font-mono">Global Cold-Chain Visibility Platform • Bio Farma Export Node</p>
        </div>

        <!-- 1-CLICK DEMO ACCOUNT SELECTOR (Exam Ready for Lecturer) -->
        <div class="mb-6 p-4 rounded-2xl bg-slate-900/90 border border-cyan-500/30 backdrop-blur-xl shadow-xl shadow-cyan-950/40">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-mono text-cyan-400 uppercase font-bold tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span> 1-Click Examiner Demo Login
                </span>
                <span class="text-[10px] text-slate-400">Instant Access</span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <a href="{{ route('login.quick', 'admin') }}" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-cyan-500/20 border border-slate-700/80 hover:border-cyan-400 transition-all text-center group">
                    <i data-lucide="shield" class="w-4 h-4 mx-auto text-cyan-400 mb-1"></i>
                    <div class="text-xs font-bold text-white group-hover:text-cyan-300">Admin</div>
                    <div class="text-[9px] text-slate-400 truncate">Governance</div>
                </a>
                <a href="{{ route('login.quick', 'operator') }}" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-cyan-500/20 border border-slate-700/80 hover:border-cyan-400 transition-all text-center group">
                    <i data-lucide="cpu" class="w-4 h-4 mx-auto text-emerald-400 mb-1"></i>
                    <div class="text-xs font-bold text-white group-hover:text-emerald-300">Operator</div>
                    <div class="text-[9px] text-slate-400 truncate">Bio Farma</div>
                </a>
                <a href="{{ route('login.quick', 'customer') }}" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-cyan-500/20 border border-slate-700/80 hover:border-cyan-400 transition-all text-center group">
                    <i data-lucide="user-check" class="w-4 h-4 mx-auto text-sky-400 mb-1"></i>
                    <div class="text-xs font-bold text-white group-hover:text-sky-300">Customer</div>
                    <div class="text-[9px] text-slate-400 truncate">UNICEF</div>
                </a>
            </div>
        </div>

        <!-- Standard Login Card -->
        <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-800 backdrop-blur-xl shadow-2xl">
            <h2 class="text-sm font-semibold text-white mb-4 flex items-center gap-2">
                <i data-lucide="lock" class="w-4 h-4 text-cyan-400"></i> Standard Authentication
            </h2>

            @if($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-rose-950/60 border border-rose-800 text-rose-300 text-xs">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1.5 font-mono">Email Address</label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3"></i>
                        <input type="email" name="email" value="{{ old('email', 'admin@cargotrace.com') }}" required 
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-sm text-white focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1.5 font-mono">Access Key / Password</label>
                    <div class="relative">
                        <i data-lucide="key" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3"></i>
                        <input type="password" name="password" value="password" required 
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-sm text-white focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-700 text-cyan-500 focus:ring-0">
                        <span>Remember token</span>
                    </label>
                    <span class="font-mono text-cyan-400 text-[11px]">Default: password</span>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-lg shadow-cyan-500/20">
                    <span>Enter Mission Control</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

        <div class="text-center mt-6 text-[11px] text-slate-400 font-mono">
            UAS Web Programming 2 • Portofolio Engineering Project
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
