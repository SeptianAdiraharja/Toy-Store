@extends('layouts.app')

@section('title', 'Hasil Perhitungan Apriori Step-by-Step')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 font-extrabold text-xs rounded-full">
                    <i class="fa-solid fa-circle-check text-[10px] mr-1"></i> Perhitungan Selesai
                </span>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Hasil Analisis Algoritma Apriori</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">Rincian Perhitungan Lolos-Pruned Frequent Itemset & Formation Association Rules</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('apriori.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition-all flex items-center gap-2">
                <i class="fa-solid fa-rotate-left"></i> Hitung Ulang
            </a>
            <a href="{{ route('apriori.hasil_rekomendasi') }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-xl shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-lightbulb"></i> Lihat Hasil Rekomendasi
            </a>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Transaksi (N)</span>
            <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $result['total_transactions'] }}</h3>
            <span class="text-xs text-slate-500 mt-1 block">Dataset transaksi diproses</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Minimum Support</span>
            <h3 class="text-2xl font-black text-blue-600 mt-1">{{ number_format($result['min_support'], 1) }}%</h3>
            <span class="text-xs text-slate-500 mt-1 block">Threshold frekuensi minimum</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Minimum Confidence</span>
            <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($result['min_confidence'], 1) }}%</h3>
            <span class="text-xs text-slate-500 mt-1 block">Threshold kepastian minimum</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-purple-200 bg-purple-50/50 shadow-xs">
            <span class="text-xs font-bold text-purple-600 uppercase tracking-wider">Aturan Valid Tersimpan</span>
            <h3 class="text-2xl font-black text-purple-700 mt-1">{{ $result['saved_rules'] }} Rules</h3>
            <span class="text-xs text-purple-600 mt-1 block font-semibold">Siap digunakan di Kasir POS</span>
        </div>
    </div>

    <!-- STEP 1: Candidate 1-Itemset & Frequent 1-Itemset -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                Tahap 1: Pembentukan Kandidat 1-Itemset (C1) & Frequent 1-Itemset (L1)
            </h3>
            <span class="text-xs font-bold px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg">
                Min. Support = {{ $result['min_support'] }}%
            </span>
        </div>

        <div class="p-5">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100 text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Nama Produk Mainan</th>
                            <th class="px-4 py-3 text-center">Jumlah Transaksi (Support Count)</th>
                            <th class="px-4 py-3 text-center">Nilai Support (%)</th>
                            <th class="px-4 py-3 text-center">Status Penyaringan (L1)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($result['candidate_1'] as $c1)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-bold text-slate-800">{{ $c1['item'] }}</td>
                                <td class="px-4 py-3 text-center font-mono font-bold">{{ $c1['count'] }} / {{ $result['total_transactions'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-extrabold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg text-xs">
                                        {{ number_format($c1['support'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($c1['is_frequent'])
                                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 font-extrabold text-xs rounded-full inline-flex items-center gap-1">
                                            <i class="fa-solid fa-check text-[10px]"></i> Lolos (Frequent)
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-rose-100 text-rose-800 font-extrabold text-xs rounded-full inline-flex items-center gap-1">
                                            <i class="fa-solid fa-xmark text-[10px]"></i> Tidak Lolos (Pruned)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-slate-400">Tidak ada item transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- STEP 2: Candidate 2-Itemset & Frequent 2-Itemset -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                Tahap 2: Pembentukan Kandidat 2-Itemset (C2) & Frequent 2-Itemset (L2)
            </h3>
            <span class="text-xs font-bold px-2.5 py-1 bg-indigo-100 text-indigo-800 rounded-lg">
                Kombinasi Pasangan Produk
            </span>
        </div>

        <div class="p-5">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100 text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Kombinasi Pasangan (2-Itemset)</th>
                            <th class="px-4 py-3 text-center">Kemunculan Bersama (Count)</th>
                            <th class="px-4 py-3 text-center">Nilai Support Pasangan (%)</th>
                            <th class="px-4 py-3 text-center">Status Penyaringan (L2)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($result['candidate_2'] as $c2)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-bold text-slate-800">
                                    <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-slate-800 inline-block text-xs">
                                        { {{ $c2['item1'] }}, {{ $c2['item2'] }} }
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-bold">{{ $c2['count'] }} / {{ $result['total_transactions'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-extrabold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg text-xs">
                                        {{ number_format($c2['support'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($c2['is_frequent'])
                                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 font-extrabold text-xs rounded-full inline-flex items-center gap-1">
                                            <i class="fa-solid fa-check text-[10px]"></i> Lolos (Frequent)
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-rose-100 text-rose-800 font-extrabold text-xs rounded-full inline-flex items-center gap-1">
                                            <i class="fa-solid fa-xmark text-[10px]"></i> Tidak Lolos (Pruned)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-slate-400">Tidak ada kombinasi 2-itemset yang terbentuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- STEP 3: Association Rules & Confidence Calculation -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-purple-600 text-white flex items-center justify-center text-xs font-bold">3</span>
                Tahap 3: Pembentukan Aturan Asosiasi & Perhitungan Confidence
            </h3>
            <span class="text-xs font-bold px-2.5 py-1 bg-purple-100 text-purple-800 rounded-lg">
                Min. Confidence = {{ $result['min_confidence'] }}%
            </span>
        </div>

        <div class="p-5">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-purple-50 text-xs font-bold uppercase text-purple-900">
                        <tr>
                            <th class="px-4 py-3">Aturan Asosiasi (If A &rarr; Then B)</th>
                            <th class="px-4 py-3 text-center">Nilai Support</th>
                            <th class="px-4 py-3 text-center">Nilai Confidence</th>
                            <th class="px-4 py-3 text-center">Status Aturan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($result['association_rules'] as $rule)
                            <tr class="{{ $rule['is_valid'] ? 'bg-emerald-50/30' : 'bg-white' }} hover:bg-purple-50/50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="font-bold text-slate-800">Jika beli "{{ $rule['antecedent'] }}"</span>
                                        <i class="fa-solid fa-arrow-right text-purple-500 text-xs"></i>
                                        <span class="font-bold text-purple-700">Maka beli "{{ $rule['consequent'] }}"</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-extrabold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg text-xs">
                                        {{ number_format($rule['support'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg text-xs">
                                        {{ number_format($rule['confidence'], 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($rule['is_valid'])
                                        <span class="px-3 py-1 bg-emerald-500 text-white font-extrabold text-xs rounded-full inline-flex items-center gap-1 shadow-xs">
                                            <i class="fa-solid fa-star text-[10px]"></i> Valid & Strong Rule
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-slate-200 text-slate-600 font-bold text-xs rounded-full inline-flex items-center gap-1">
                                            <i class="fa-solid fa-minus text-[10px]"></i> Tidak Memenuhi Threshold
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-6 text-slate-400">Tidak ada aturan asosiasi yang terbentuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
