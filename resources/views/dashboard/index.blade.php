@extends('layouts.app')

@section('title', 'Dashboard Utama')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Dashboard Ringkasan Operasional</h1>
            <p class="text-sm text-slate-500 mt-1">Sistem Rekomendasi Produk Pola Pembelian (Market Basket Analysis) - Toko Serba 123 Toy Store</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('kasir.index') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-cash-register"></i> Buka Kasir POS
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('apriori.index') }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm rounded-xl shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-brain"></i> Jalankan Apriori
            </a>
            @endif
        </div>
    </div>

    <!-- Stat Cards Matrix -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Pendapatan -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pendapatan</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center text-xs text-slate-500">
                <i class="fa-solid fa-chart-line text-emerald-500 mr-1.5"></i> Total akumulasi transaksi
            </div>
        </div>

        <!-- Total Transaksi -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Transaksi</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalTransaksi) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center text-xs text-slate-500">
                <i class="fa-solid fa-clock-history text-blue-500 mr-1.5"></i> Struk transaksi tercatat
            </div>
        </div>

        <!-- Total Produk -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Katalog Produk</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalProduk) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center text-xs text-slate-500">
                <i class="fa-solid fa-tag text-indigo-500 mr-1.5"></i> Varian mainan aktif
            </div>
        </div>

        <!-- Aturan Asosiasi -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aturan Asosiasi (Apriori)</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalRules) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                    <i class="fa-solid fa-lightbulb"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center text-xs text-slate-500">
                <i class="fa-solid fa-wand-magic-sparkles text-purple-500 mr-1.5"></i> Pattern rekomendasi kuat
            </div>
        </div>
    </div>

    <!-- Main Content Section: Shift Distribution & Top Rules -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Shift Sales Breakdown -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-clock text-amber-500"></i> Distribusi Penjualan per Shift
            </h3>

            <div class="space-y-4">
                @php
                    $shifts = ['pagi' => 'Shift Pagi', 'siang' => 'Shift Siang', 'sore' => 'Shift Sore'];
                    $colors = ['pagi' => 'bg-amber-500 text-amber-600', 'siang' => 'bg-blue-500 text-blue-600', 'sore' => 'bg-indigo-500 text-indigo-600'];
                @endphp

                @foreach($shifts as $key => $label)
                    @php
                        $data = $shiftSales[$key] ?? (object)['total' => 0, 'count' => 0];
                        $percent = $totalPendapatan > 0 ? round(($data->total / $totalPendapatan) * 100, 1) : 0;
                    @endphp
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700 uppercase flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ explode(' ', $colors[$key])[0] }}"></span>
                                {{ $label }}
                            </span>
                            <span class="text-xs font-extrabold text-slate-800">Rp {{ number_format($data->total, 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden mb-1.5">
                            <div class="{{ explode(' ', $colors[$key])[0] }} h-2 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-500">
                            <span>{{ $data->count }} Transaksi</span>
                            <span class="font-bold text-slate-700">{{ $percent }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Top Association Rules Preview -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-sparkles text-purple-600"></i> Top Aturan Asosiasi (Rekomendasi Utama)
                    </h3>
                    <a href="{{ route('apriori.hasil_rekomendasi') }}" class="text-xs font-bold text-purple-600 hover:text-purple-800">
                        Lihat Semua <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($topRules as $rule)
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-purple-200 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs">
                                    <i class="fa-solid fa-link"></i>
                                </div>
                                <div class="text-sm">
                                    <span class="font-bold text-slate-800">Jika membeli "{{ $rule->produk_antecedent }}"</span>
                                    <span class="text-slate-400 mx-1"><i class="fa-solid fa-arrow-right"></i></span>
                                    <span class="font-bold text-purple-700">Maka membeli "{{ $rule->produk_consequent }}"</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 self-end sm:self-auto">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 font-extrabold text-xs rounded-lg border border-blue-200/50">
                                    Support: {{ number_format($rule->nilai_support, 1) }}%
                                </span>
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-extrabold text-xs rounded-lg border border-emerald-200/50">
                                    Confidence: {{ number_format($rule->nilai_confidence, 1) }}%
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-400 text-sm">
                            <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                            Belum ada aturan asosiasi. Silakan jalankan proses Algoritma Apriori.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Aturan asosiasi digunakan secara otomatis pada antarmuka Kasir POS.</span>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-history text-blue-600"></i> Transaksi Terakhir
            </h3>
            <a href="{{ route('transaksis.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                Lihat Riwayat Transaksi <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-400 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">ID Transaksi</th>
                        <th class="px-6 py-3.5">Tanggal & Waktu</th>
                        <th class="px-6 py-3.5">Shift</th>
                        <th class="px-6 py-3.5">Item Dibeli</th>
                        <th class="px-6 py-3.5">Kasir</th>
                        <th class="px-6 py-3.5 text-right">Total Harga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentTransaksis as $t)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-bold text-blue-600">
                                <a href="{{ route('transaksis.show', $t->id) }}" class="hover:underline">
                                    {{ $t->id_transaksi }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $t->tanggal_transaksi->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase {{ $t->shift == 'pagi' ? 'bg-amber-100 text-amber-800' : ($t->shift == 'siang' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800') }}">
                                    Shift {{ $t->shift }}
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate text-xs text-slate-700">
                                {{ implode(', ', $t->detailTransaksis->map(fn($d) => $d->produk->nama_produk ?? 'Item')->toArray()) }}
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-700">
                                {{ $t->user->nama ?? 'Kasir' }}
                            </td>
                            <td class="px-6 py-4 font-black text-slate-800 text-right">
                                Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400 text-sm">
                                Belum ada transaksi tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
