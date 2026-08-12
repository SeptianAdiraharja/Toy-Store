@extends('layouts.app')

@section('title', 'Kelola Data Produk')

@section('content')
<div class="space-y-6">

    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Katalog Data Produk Mainan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola daftar item, harga, kategori, dan stok barang di Serba 123 Toy Store</p>
        </div>
        @if(auth()->user()->isAdmin())
        <button type="button" onclick="openAddModal()" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm rounded-xl shadow-sm transition-all flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-plus"></i> Tambah Produk Baru
        </button>
        @endif
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('produks.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-grow">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </span>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                    placeholder="Cari ID produk, nama mainan, atau kategori...">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm rounded-xl transition-colors">
                Filter
            </button>
            @if(!empty($search))
                <a href="{{ route('produks.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm rounded-xl transition-colors flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Product Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-400 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">ID Produk</th>
                        <th class="px-6 py-3.5">Nama Mainan</th>
                        <th class="px-6 py-3.5">Kategori</th>
                        <th class="px-6 py-3.5">Harga</th>
                        <th class="px-6 py-3.5">Stok</th>
                        @if(auth()->user()->isAdmin())
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($produks as $p)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-brand-600">
                                {{ $p->id_produk }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-800">
                                {{ $p->nama_produk }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $p->kategori }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-black text-slate-800">
                                Rp {{ number_format($p->harga, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($p->stok <= 5)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ $p->stok }} (Hampir Habis)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        {{ $p->stok }} Tersedia
                                    </span>
                                @endif
                            </td>
                            @if(auth()->user()->isAdmin())
                            <td class="px-6 py-4 text-right space-x-2">
                                <button type="button" onclick="openEditModal({{ json_encode($p) }})" class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Edit Produk">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form method="POST" action="{{ route('produks.destroy', $p->id) }}" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Produk">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-4xl mb-3 block"></i>
                                Tidak ada data produk yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($produks->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $produks->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Produk -->
@if(auth()->user()->isAdmin())
<div id="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-extrabold text-slate-800">Tambah Produk Mainan</h3>
            <button type="button" onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('produks.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">ID Produk (Opsional)</label>
                <input type="text" name="id_produk" placeholder="Contoh: PRD011 (Otomatis jika kosong)" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Produk</label>
                <input type="text" name="nama_produk" required placeholder="Contoh: Robot Transformers" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori</label>
                <input type="text" name="kategori" required placeholder="Contoh: Robot & Figur, Edukasi, Boneka" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Harga (Rp)</label>
                    <input type="number" name="harga" required min="0" placeholder="50000" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Stok Awal</label>
                    <input type="number" name="stok" required min="0" value="50" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold text-sm rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 bg-brand-600 text-white font-bold text-sm rounded-xl">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Produk -->
<div id="editModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-extrabold text-slate-800">Edit Data Produk</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="editForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">ID Produk</label>
                <input type="text" id="edit_id_produk" name="id_produk" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Produk</label>
                <input type="text" id="edit_nama_produk" name="nama_produk" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori</label>
                <input type="text" id="edit_kategori" name="kategori" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Harga (Rp)</label>
                    <input type="number" id="edit_harga" name="harga" required min="0" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Stok</label>
                    <input type="number" id="edit_stok" name="stok" required min="0" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold text-sm rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 bg-brand-600 text-white font-bold text-sm rounded-xl">Update Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
    }
    function openEditModal(produk) {
        document.getElementById('editForm').action = '/produks/' + produk.id;
        document.getElementById('edit_id_produk').value = produk.id_produk;
        document.getElementById('edit_nama_produk').value = produk.nama_produk;
        document.getElementById('edit_kategori').value = produk.kategori;
        document.getElementById('edit_harga').value = produk.harga;
        document.getElementById('edit_stok').value = produk.stok;
        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
