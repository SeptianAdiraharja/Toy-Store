@extends('layouts.app')

@section('title', 'Data Transaksi Penjualan')

@section('content')
<div class="space-y-6">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Riwayat Data Transaksi</h1>
            <p class="text-sm text-slate-500 mt-1">Seluruh arsip transaksi penjualan kasir di Serba 123 Toy Store</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->isAdmin())
            <a href="{{ route('transaksis.import.form') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-file-import"></i> Impor Data Transaksi
            </a>
            @endif
            <a href="{{ route('kasir.index') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-cart-plus"></i> Transaksi Baru
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('transaksis.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                    placeholder="No Struk / ID Transaksi...">
            </div>
            <div>
                <select name="shift" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Semua Shift</option>
                    <option value="pagi" {{ request('shift') == 'pagi' ? 'selected' : '' }}>Shift Pagi</option>
                    <option value="siang" {{ request('shift') == 'siang' ? 'selected' : '' }}>Shift Siang</option>
                    <option value="sore" {{ request('shift') == 'sore' ? 'selected' : '' }}>Shift Sore</option>
                </select>
            </div>
            <div>
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-grow py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm rounded-xl transition-colors">
                    Filter
                </button>
                <a href="{{ route('transaksis.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-400 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">ID Transaksi</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Shift</th>
                        <th class="px-6 py-3.5">Detail Mainan</th>
                        <th class="px-6 py-3.5">Kasir</th>
                        <th class="px-6 py-3.5 text-right">Total Struk</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksis as $t)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-brand-600">
                                {{ $t->id_transaksi }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $t->tanggal_transaksi->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase {{ $t->shift == 'pagi' ? 'bg-amber-100 text-amber-800' : ($t->shift == 'siang' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800') }}">
                                    {{ $t->shift }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-700 max-w-xs truncate">
                                {{ implode(', ', $t->detailTransaksis->map(fn($d) => $d->produk->nama_produk ?? 'Produk')->toArray()) }}
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-700">
                                {{ $t->user->nama ?? 'Kasir' }}
                            </td>
                            <td class="px-6 py-4 font-black text-slate-800 text-right">
                                Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('transaksis.show', $t->id) }}" class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors inline-block" title="Lihat Struk Detail">
                                    <i class="fa-solid fa-receipt"></i>
                                </a>
                                @if(auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('transaksis.destroy', $t->id) }}" class="inline-block" onsubmit="return confirm('Hapus transaksi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Transaksi">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-4xl mb-3 block"></i>
                                Belum ada transaksi penjualan yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transaksis->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $transaksis->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
