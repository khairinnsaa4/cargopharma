<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        try {
            if (! Schema::hasTable('users')) {
                Artisan::call('migrate', ['--force' => true]);
            }
            if (User::count() === 0) {
                Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            Log::warning('Auto-migration/seed check: '.$e->getMessage());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            if (! Schema::hasTable('users')) {
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('db:seed', ['--force' => true]);
            } elseif (User::where('email', $credentials['email'])->doesntExist()) {
                Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            Log::warning('User seed check: '.$e->getMessage());
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (! Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi tidak valid. Silakan periksa kredensial Anda.',
        ])->onlyInput('email');
    }

    /**
     * Fast Login for Local Development / Evaluation Only.
     * Strictly disabled in production or staging environments.
     */
    public function quickLogin(string $role)
    {
        if (! app()->environment('local')) {
            abort(403, 'Quick login is strictly disabled in non-local environments.');
        }

        if (! in_array($role, ['admin', 'operator', 'customer'], true)) {
            abort(400, 'Invalid demonstration role requested.');
        }

        $user = User::where('role', $role)->first();

        if (! $user) {
            try {
                Artisan::call('db:seed', ['--force' => true]);
                $user = User::where('role', $role)->first();
            } catch (\Throwable) {
                // fallback to explicit creation
            }
        }

        if (! $user) {
            $user = match ($role) {
                'admin' => User::firstOrCreate(
                    ['email' => 'admin@cargopharma.com'],
                    [
                        'name' => 'Administrator Bio Farma',
                        'password' => Hash::make('password'),
                        'role' => 'admin',
                        'phone' => '+62 811-2233-4455',
                        'organization' => 'PT Bio Farma (Persero) - Headquarters',
                        'is_active' => true,
                    ]
                ),
                'operator' => User::firstOrCreate(
                    ['email' => 'operator@cargopharma.com'],
                    [
                        'name' => 'Rian Pratama',
                        'password' => Hash::make('password'),
                        'role' => 'operator',
                        'phone' => '+62 812-9988-7766',
                        'organization' => 'PT Bio Farma - Global Export & Cold Chain Logistics',
                        'is_active' => true,
                    ]
                ),
                'customer' => User::firstOrCreate(
                    ['email' => 'customer@unicef.org'],
                    [
                        'name' => 'Dr. Adebayo Johnson',
                        'password' => Hash::make('password'),
                        'role' => 'customer',
                        'phone' => '+45 35 27 35 27',
                        'organization' => 'UNICEF Global Supply Division (Polio Outbreak Response)',
                        'is_active' => true,
                    ]
                ),
                default => null,
            };
        }

        if ($user) {
            Auth::login($user);
            request()->session()->regenerate();

            return redirect()->route('dashboard')->with('success', "Berhasil masuk sebagai {$user->name} ({$user->role})");
        }

        return redirect()->route('login')->withErrors(['error' => 'Peran demonstrasi tidak ditemukan di dalam sistem.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
