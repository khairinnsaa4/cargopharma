<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        if (! $currentUser->isAdmin()) {
            abort(403, 'Manajemen pengguna dibatasi khusus untuk administrator sistem.');
        }

        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();
        if (! $currentUser->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,operator,customer',
            'phone' => 'nullable|string|max:50',
            'organization' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'organization' => $validated['organization'] ?? null,
            'is_active' => true,
        ]);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'CREATE_USER',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'new_values' => ['email' => $user->email, 'role' => $user->role],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Akun pengguna {$user->name} ({$user->role}) berhasil dibuat.");
    }

    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();
        if (! $currentUser->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => 'required|string|in:admin,operator,customer',
            'phone' => 'nullable|string|max:50',
            'organization' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:8',
        ]);

        // Safety Guard: Cannot demote or deactivate the last remaining administrator
        if ($user->isAdmin() && ($validated['role'] !== 'admin' || ! $request->boolean('is_active'))) {
            $adminCount = User::where('role', 'admin')->where('is_active', true)->count();
            if ($adminCount <= 1) {
                return back()->withErrors(['role' => 'Pengaman sistem: Tidak dapat menonaktifkan atau menurunkan peran satu-satunya administrator aktif.']);
            }
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'organization' => $validated['organization'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return back()->with('success', "Pengguna {$user->email} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        $currentUser = Auth::user();
        if (! $currentUser->isAdmin()) {
            abort(403);
        }

        // Safety Guard: Cannot delete self
        if ($user->id === $currentUser->id) {
            return back()->withErrors(['user' => 'Pengaman sistem: Anda tidak dapat menghapus akun administrator Anda sendiri.']);
        }

        // Safety Guard: Cannot delete the last administrator
        if ($user->isAdmin()) {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->withErrors(['user' => 'Pengaman sistem: Tidak dapat menghapus satu-satunya akun administrator yang tersisa.']);
            }
        }

        $user->delete();

        return back()->with('success', "Pengguna {$user->email} berhasil dihapus.");
    }
}
