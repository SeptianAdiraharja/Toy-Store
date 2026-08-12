@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Page Header & Filter -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-file-invoice text-amber-600"></i> Laporan Penjualan & Revenue
            </h1>
            <p class="text-sm text-slate-500 mt-1">Laporan rekapitulasi transaksi penjualan dan analisis pendapatan per shift</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('laporan.cetak', ['tanggal_mulai' => $tanggalMulai, 'tanggal_selesai' => $tanggalSelesai, 'shift' => $shift]) }}" target="_blank" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak Laporan (PDF / Print)
            </a>
        </div>
    </div>

    <!-- Filter Form Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('laporan.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="tanggal_mulai" class="block text-xs font-bold text-slate-600 mb-1.5">Tanggal Mulai</label>
                <input 
                    type="date" 
                    name="tanggal_mulai" 
                    id="tanggal_mulai" 
                    value="{{ $tanggalMulai }}" 
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-amber-500">
            </div>

            <div>
                <label for="tanggal_selesai" class="block text-xs font-bold text-slate-600 mb-1.5">Tanggal Selesai</label>
                <input 
                    type="date" 
                    name="tanggal_selesai" 
                    id="tanggal_selesai" 
                    value="{{ $tanggalSelesai }}" 
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-amber-500">
            </div>

            <div>
                <label for="shift" class="block text-xs font-bold text-slate-600 mb-1.5">Filter Shift</label>
                <select name="shift" id="shift" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-amber-500">
                    <option value="">Semua Shift</option>
                    <option value="pagi" {{ $shift === 'pagi' ? 'selected' : '' }}>Shift Pagi</option>
                    <option value="siang" {{ $shift === 'siang' ? 'selected' : '' }}>Shift Siang</option>
                    <option value="sore" {{ $shift === 'sore' ? 'selected' : '' }}>Shift Sore</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all">
                    Terapkan Filter
                </button>
                <a href="{{ route('laporan.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary KPI Matrix -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pendapatan</span>
            <h3 class="text-2xl font-black text-emerald-700 mt-1">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
            <span class="text-xs text-slate-500 mt-1 block">Periode terpilih</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jumlah Transaksi</span>
            <h3 class="text-2xl font-black text-blue-700 mt-1">{{ number_format($totalTransaksiCount) }} Struk</h3>
            <span class="text-xs text-slate-500 mt-1 block">Total struk diterbitkan</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Mainan Terjual</span>
            <h3 class="text-2xl font-black text-indigo-700 mt-1">{{ number_format($totalItemsSold) }} Pcs</h3>
            <span class="text-xs text-slate-500 mt-1 block">Total item keluar</span>
        </div>
    </div>

    <!-- Shift Sales Breakdown Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
        <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-clock text-amber-600"></i> Ringkasan Penjualan Per Shift Kerja
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @php
                $shiftsDef = [
                    'pagi' => ['label' => 'Shift Pagi', 'color' => 'border-amber-200 bg-amber-50/40 text-amber-800'],
                    'siang' => ['label' => 'Shift Siang', 'color' => 'border-blue-200 bg-blue-50/40 text-blue-800'],
                    'sore' => ['label' => 'Shift Sore', 'color' => 'border-indigo-200 bg-indigo-50/40 text-indigo-800'],
                ];
            @endphp
            @foreach($shiftsDef as $key => $meta)
                @php
                    $sData = $shiftSummary[$key] ?? ['count' => 0, 'total' => 0];
                @endphp
                <div class="p-4 rounded-xl border {{ $meta['color'] }}">
                    <span class="text-xs font-bold uppercase block mb-1">{{ $meta['label'] }}</span>
                    <h4 class="text-lg font-black text-slate-800">Rp {{ number_format($sData['total'], 0, ',', '.') }}</h4>
                    <span class="text-xs font-medium text-slate-600">{{ $sData['count'] }} Transaksi</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-sm">Rincian Transaksi Penjualan</h3>
            <span class="text-xs text-slate-500 font-medium">Menampilkan {{ $transaksis->count() }} Data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-400 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">ID Transaksi</th>
                        <th class="px-5 py-3.5">Tanggal & Waktu</th>
                        <th class="px-5 py-3.5">Shift</th>
                        <th class="px-5 py-3.5">Kasir</th>
                        <th class="px-5 py-3.5">Item Dibeli</th>
                        <th class="px-5 py-3.5 text-right">Total (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksis as $t)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-800 text-xs">
                                <a href="{{ route('transaksis.show', $t->id) }}" class="text-brand-600 hover:underline">
                                    {{ $t->id_transaksi }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600 font-medium">
                                {{ \Carbon\Carbon::parse($t->tanggal_transaksi)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-3.5 text-xs font-bold">
                                <span class="px-2.5 py-1 rounded-full uppercase text-[10px] {{ $t->shift === 'pagi' ? 'bg-amber-100 text-amber-800' : ($t->shift === 'siang' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800') }}">
                                    {{ $t->shift }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs font-medium text-slate-700">
                                {{ $t->user ? $t->user->nama : 'System/Kasir' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">
                                @foreach($t->detailTransaksis as $d)
                                    <span class="inline-block bg-slate-100 px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 mr-1 mb-1">
                                        {{ $d->produk ? $d->produk->nama_produk : 'Produk' }} (x{{ $d->jumlah }})
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-5 py-3.5 text-right font-black text-slate-800 text-xs">
                                Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400 text-xs">
                                Tidak ada data transaksi untuk periode terpilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
