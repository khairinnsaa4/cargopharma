@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pengguna</h1>

        <button onclick="document.getElementById('modal-add-user').classList.remove('hidden')" class="px-3.5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-xs">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah
        </button>
    </div>

    <!-- FILTER BAR -->
    <div class="p-4 rounded-xl bg-white border border-slate-200/90 shadow-xs">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pengguna..." 
                    class="w-full p-2 rounded-lg border border-slate-200 text-slate-800 focus:outline-none focus:border-teal-600">
            </div>
            <div>
                <select name="role" class="p-2 rounded-lg border border-slate-200 text-slate-800 focus:outline-none focus:border-teal-600">
                    <option value="">Semua Peran</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="operator" {{ request('role') === 'operator' ? 'selected' : '' }}>Operator</option>
                    <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Penerima</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-semibold shadow-xs flex items-center gap-1.5">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['search', 'role']))
            <a href="{{ route('users.index') }}" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">
                Reset
            </a>
            @endif
        </form>
    </div>

    <!-- USERS TABLE -->
    <div class="rounded-xl bg-white border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Peran</th>
                        <th class="py-3 px-4">Organisasi</th>
                        <th class="py-3 px-4">No. Telepon</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Tanggal Dibuat</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-slate-900">{{ $user->name }}</div>
                            <div class="text-[11px] font-mono text-slate-400">{{ $user->email }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $roleBadge = [
                                    'admin' => ['Administrator', 'bg-rose-50 text-rose-700 border-rose-200'],
                                    'operator' => ['Operator', 'bg-blue-50 text-blue-700 border-blue-200'],
                                    'customer' => ['Penerima', 'bg-teal-50 text-teal-700 border-teal-200'],
                                ];
                                $currentRole = $roleBadge[$user->role] ?? [$user->role, 'bg-slate-50 text-slate-700 border-slate-200'];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono border {{ $currentRole[1] }}">
                                {{ $currentRole[0] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            {{ $user->organization ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-500">
                            {{ $user->phone ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($user->is_active ?? true)
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Nonaktif
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-400 text-[11px]">
                            {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button onclick="openEditUserModal({{ json_encode($user) }})" title="Edit" class="p-1.5 rounded-lg text-slate-400 hover:text-teal-700 hover:bg-slate-100 transition-colors inline-flex items-center">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>

                                @if($user->id !== Auth::id())
                                <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="inline" onsubmit="return confirm('Hapus pengguna {{ $user->email }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-slate-100 transition-colors inline-flex items-center">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400 italic">
                            Tidak ada pengguna yang cocok dengan kriteria pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>

<!-- MODAL ADD USER -->
<div id="modal-add-user" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md bg-white rounded-xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Tambah Akun Pengguna Baru</h3>
            <button onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('users.store') }}" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-medium text-slate-700 mb-1">Nama Lengkap *</label>
                <input type="text" name="name" required placeholder="Contoh: Dr. Budi Santoso" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Alamat Email *</label>
                <input type="email" name="email" required placeholder="user@biopharma.co.id" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Kata Sandi *</label>
                <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Peran Pengguna *</label>
                <select name="role" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                    <option value="operator">Operator (Logistik Rantai Dingin)</option>
                    <option value="customer">Penerima (Klien / Otoritas Kesehatan)</option>
                    <option value="admin">Administrator (Tata Kelola Sistem)</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Organisasi / Departemen</label>
                <input type="text" name="organization" placeholder="Contoh: Bio Farma Logistik / UNICEF Indonesia" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Nomor Telepon</label>
                <input type="text" name="phone" placeholder="+62 811 2345 6789" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-add-user').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold shadow-xs">Buat Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT USER -->
<div id="modal-edit-user" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md bg-white rounded-xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Edit Akun Pengguna</h3>
            <button onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="edit-user-form" method="POST" action="" class="space-y-3 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-medium text-slate-700 mb-1">Nama Lengkap *</label>
                <input type="text" name="name" id="edit-user-name" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Alamat Email *</label>
                <input type="email" name="email" id="edit-user-email" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Peran Pengguna *</label>
                <select name="role" id="edit-user-role" required class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
                    <option value="operator">Operator (Logistik Rantai Dingin)</option>
                    <option value="customer">Penerima (Klien / Otoritas Kesehatan)</option>
                    <option value="admin">Administrator (Tata Kelola Sistem)</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Organisasi / Departemen</label>
                <input type="text" name="organization" id="edit-user-org" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Nomor Telepon</label>
                <input type="text" name="phone" id="edit-user-phone" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div>
                <label class="block font-medium text-slate-700 mb-1">Ganti Kata Sandi (kosongkan jika tidak diubah)</label>
                <input type="password" name="password" minlength="8" placeholder="Biarkan kosong jika tidak diubah" class="w-full p-2 rounded-lg border border-slate-200 text-slate-800">
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="edit-user-active" value="1" class="rounded border-slate-300 text-teal-600">
                <label for="edit-user-active" class="font-medium text-slate-700">Akun Aktif (Diberi Izin Masuk Sistem)</label>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold shadow-xs">Perbarui Akun</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditUserModal(user) {
    const form = document.getElementById('edit-user-form');
    form.action = `/users/${user.id}`;
    document.getElementById('edit-user-name').value = user.name;
    document.getElementById('edit-user-email').value = user.email;
    document.getElementById('edit-user-role').value = user.role;
    document.getElementById('edit-user-org').value = user.organization || '';
    document.getElementById('edit-user-phone').value = user.phone || '';
    document.getElementById('edit-user-active').checked = user.is_active === undefined ? true : Boolean(user.is_active);
    document.getElementById('modal-edit-user').classList.remove('hidden');
}
</script>
@endsection
