<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100 text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CargoTrace Enterprise Visibility</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6 bg-[#F4F6F9]">

    <div class="w-full max-w-md my-auto space-y-6">
        <!-- Logo & Header -->
        <div class="text-center">
            <div class="inline-flex w-12 h-12 rounded-xl bg-blue-600 items-center justify-center text-white shadow-sm mb-3">
                <i data-lucide="container" class="w-6 h-6"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">CargoTrace</h1>
            <p class="text-xs text-slate-500 mt-1">Enterprise Global Shipment Visibility & Tracking Platform</p>
        </div>

        <!-- 1-Click Fast Role Selection (For Academic Examiner Review) -->
        <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-700 flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-4 h-4 text-blue-600"></i> Fast Demo Accounts
                </span>
                <span class="text-[11px] text-slate-400">Examiner 1-Click</span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <a href="{{ route('login.quick', 'admin') }}" class="p-2.5 rounded-lg border border-slate-200 hover:border-blue-500 hover:bg-blue-50 transition-all text-center">
                    <div class="text-xs font-bold text-slate-800">Admin</div>
                    <div class="text-[10px] text-slate-500">Governance</div>
                </a>
                <a href="{{ route('login.quick', 'operator') }}" class="p-2.5 rounded-lg border border-slate-200 hover:border-blue-500 hover:bg-blue-50 transition-all text-center">
                    <div class="text-xs font-bold text-slate-800">Operator</div>
                    <div class="text-[10px] text-slate-500">Operations</div>
                </a>
                <a href="{{ route('login.quick', 'customer') }}" class="p-2.5 rounded-lg border border-slate-200 hover:border-blue-500 hover:bg-blue-50 transition-all text-center">
                    <div class="text-xs font-bold text-slate-800">Customer</div>
                    <div class="text-[10px] text-slate-500">UNICEF Consignee</div>
                </a>
            </div>
        </div>

        <!-- Standard Corporate Login Form -->
        <div class="p-6 rounded-xl bg-white border border-slate-200/90 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900">Sign in to your account</h2>

            @if($errors->any())
            <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', 'admin@cargotrace.com') }}" required 
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" value="password" required 
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                </div>

                <div class="flex items-center justify-between text-slate-500">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-0">
                        <span>Remember me</span>
                    </label>
                    <span class="font-mono text-slate-400">Default: password</span>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm transition-all flex items-center justify-center gap-2">
                    <span>Sign In</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

        <div class="text-center text-xs text-slate-400">
            Web Programming 2 • Portofolio Project
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
