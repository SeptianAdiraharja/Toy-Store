@extends('layouts.app')

@section('title', 'Kelola Pengguna Sistem')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Manajemen Pengguna Sistem</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola akun pengguna dan peranan hak akses (Owner, Admin, Kasir)</p>
        </div>
        <button type="button" onclick="openAddUserModal()" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm rounded-xl shadow-sm transition-all flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-user-plus"></i> Tambah User Baru
        </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-400 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Nama Lengkap</th>
                        <th class="px-6 py-3.5">Username</th>
                        <th class="px-6 py-3.5">Email</th>
                        <th class="px-6 py-3.5">Role / Peran</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800">
                                {{ $u->nama }}
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-600">
                                {{ $u->username }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $u->email ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold uppercase {{ $u->isOwner() ? 'bg-amber-100 text-amber-800' : ($u->isAdmin() ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    <i class="fa-solid {{ $u->isOwner() ? 'fa-crown' : ($u->isAdmin() ? 'fa-user-gear' : 'fa-cash-register') }} text-[10px] mr-1"></i>
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button type="button" onclick="openEditUserModal({{ json_encode($u) }})" class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Edit User">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                @if($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.destroy', $u->id) }}" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus User">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div id="addUserModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-extrabold text-slate-800">Tambah Akun Pengguna</h3>
            <button type="button" onclick="closeAddUserModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                <input type="text" name="nama" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Username Login</label>
                <input type="text" name="username" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email (Opsional)</label>
                <input type="email" name="email" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password</label>
                <input type="password" name="password" required minlength="6" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role / Peran</label>
                <select name="role" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="kasir">Kasir / Staf Toko</option>
                    <option value="admin">Admin System</option>
                    <option value="owner">Owner / Pemilik</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold text-sm rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 bg-brand-600 text-white font-bold text-sm rounded-xl">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User -->
<div id="editUserModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-extrabold text-slate-800">Edit Akun Pengguna</h3>
            <button type="button" onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="editUserForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                <input type="text" id="edit_nama" name="nama" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Username Login</label>
                <input type="text" id="edit_username" name="username" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email (Opsional)</label>
                <input type="email" id="edit_email" name="email" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password Baru (Biarkan kosong jika tidak diubah)</label>
                <input type="password" name="password" minlength="6" placeholder="••••••••" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role / Peran</label>
                <select id="edit_role" name="role" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="kasir">Kasir / Staf Toko</option>
                    <option value="admin">Admin System</option>
                    <option value="owner">Owner / Pemilik</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold text-sm rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 bg-brand-600 text-white font-bold text-sm rounded-xl">Update User</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddUserModal() {
        document.getElementById('addUserModal').classList.remove('hidden');
    }
    function closeAddUserModal() {
        document.getElementById('addUserModal').classList.add('hidden');
    }
    function openEditUserModal(user) {
        document.getElementById('editUserForm').action = '/users/' + user.id;
        document.getElementById('edit_nama').value = user.nama;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_role').value = user.role;
        document.getElementById('editUserModal').classList.remove('hidden');
    }
    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
