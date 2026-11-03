<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Invalid credentials. Please verify your email and password.',
        ])->onlyInput('email');
    }

    /**
     * Demo Fast Login (Allows lecturer/recruiter to switch roles seamlessly)
     */
    public function quickLogin(string $role)
    {
        $user = User::where('role', $role)->first();
        if ($user) {
            Auth::login($user);
            request()->session()->regenerate();

            return redirect()->route('dashboard')->with('success', "Logged in as {$user->name} ({$user->role})");
        }

        return redirect()->route('login')->withErrors(['error' => 'Demo role not found']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
