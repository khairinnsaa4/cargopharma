<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F4F7F6] text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication | CargoPharma Biopharma Visibility</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 sm:p-6 bg-[#F4F7F6] relative overflow-x-hidden">

    <!-- Subtle Medical Grid Background Pattern -->
    <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#0f766e 1px, transparent 1px); background-size: 24px 24px;"></div>

    <div class="w-full max-w-md my-auto space-y-5 relative z-10">
        
        <!-- Brand Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex w-12 h-12 rounded-2xl bg-teal-600 text-white items-center justify-center shadow-lg shadow-teal-900/10 ring-4 ring-teal-50 mb-1">
                <i data-lucide="shield-plus" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">CargoPharma</h1>
                <p class="text-xs text-teal-800 font-medium">Biopharmaceutical Cold-Chain Telemetry System</p>
            </div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Bio Farma &bull; WHO &amp; UNICEF Mandate</span>
            </div>
        </div>

        <!-- Standard Enterprise Login Form -->
        <div class="rounded-xl bg-white border border-slate-200/90 shadow-sm overflow-hidden">
            <div class="h-1 bg-gradient-to-r from-teal-500 via-emerald-500 to-teal-700"></div>
            
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Sign in to Operations Portal</h2>
                        <p class="text-[11px] text-slate-500">Enter your official enterprise credentials</p>
                    </div>
                    <i data-lucide="lock" class="w-4 h-4 text-slate-400"></i>
                </div>

                @if($errors->any())
                <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-rose-600 mt-0.5"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Official Email Address</label>
                        <div class="relative">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="name@cargotrace.com" required 
                                class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-medium text-slate-700">Account Password</label>
                        </div>
                        <div class="relative">
                            <i data-lucide="key" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                            <input type="password" id="password-field" name="password" placeholder="Enter account password" required 
                                class="w-full pl-9 pr-10 py-2 rounded-lg border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-2 text-slate-400 hover:text-slate-600 focus:outline-none" title="Lihat/Sembunyikan Password">
                                <i id="eye-icon" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[11px] text-slate-500">
                            <span>Password default: <code class="font-mono font-semibold text-teal-700 bg-teal-50 px-1.5 py-0.5 rounded border border-teal-200">password</code></span>
                            <span class="text-slate-400 text-[10px]">Tersedia juga: <code>admin</code></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-slate-600 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-teal-600 focus:ring-0">
                            <span>Keep me signed in</span>
                        </label>
                        <span class="text-slate-400 text-[11px] font-mono">TLS 256-bit Encrypted</span>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-xs transition-all flex items-center justify-center gap-2">
                        <span>Authenticate &amp; Access Dashboard</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
            
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span class="flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i> ISO/IEC 27001 Security Standard
                </span>
                <span class="font-mono text-slate-400">v1.4.0</span>
            </div>
        </div>

        <!-- Authorized Enterprise Accounts (Manual Typing Reference) -->
        <div class="rounded-xl bg-white border border-slate-200/90 p-4 shadow-xs space-y-3">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-700">
                <span class="flex items-center gap-1.5 text-teal-800">
                    <i data-lucide="key-round" class="w-4 h-4 text-teal-600"></i>
                    <span>Akun Sistem (Ketik Manual)</span>
                </span>
                <span class="text-[11px] font-mono text-slate-500">Password: <strong class="text-teal-700">password</strong></span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px]">
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200/60 space-y-0.5">
                    <div class="font-semibold text-slate-800 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span> Admin
                    </div>
                    <div class="font-mono text-slate-600 text-[10px] select-all font-medium">admin@cargotrace.com</div>
                    <div class="text-[9px] text-slate-400">Bio Farma HQ</div>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200/60 space-y-0.5">
                    <div class="font-semibold text-slate-800 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Operator
                    </div>
                    <div class="font-mono text-slate-600 text-[10px] select-all font-medium">operator@cargotrace.com</div>
                    <div class="text-[9px] text-slate-400">Cold-Chain Logistics</div>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200/60 space-y-0.5">
                    <div class="font-semibold text-slate-800 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> Consignee
                    </div>
                    <div class="font-mono text-slate-600 text-[10px] select-all font-medium">customer@unicef.org</div>
                    <div class="text-[9px] text-slate-400">UNICEF Supply Div.</div>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 text-center">Silakan ketik email &amp; password di atas ke dalam form (tidak ada tombol 1-klik).</p>
        </div>

        <!-- Footer / Security Disclosure -->
        <div class="text-center space-y-1 text-slate-400 text-[11px]">
            <p>Protected biopharmaceutical network. Unauthorized access is strictly logged.</p>
            <p class="text-[10px]">PT Bio Farma (Persero) &bull; Global Cold-Chain Visibility Division</p>
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
