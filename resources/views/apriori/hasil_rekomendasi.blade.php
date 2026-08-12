@extends('layouts.app')

@section('title', 'Hasil Rekomendasi & Aturan Asosiasi')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header & Search Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-lightbulb text-purple-600"></i> Hasil Rekomendasi Aturan Asosiasi
            </h1>
            <p class="text-sm text-slate-500 mt-1">Aturan rekomendasi produk terkuat berdasarkan analisis pola transaksi penjualan</p>
        </div>

        <form method="GET" action="{{ route('apriori.hasil_rekomendasi') }}" class="flex items-center gap-2">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nama produk..." 
                    class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-100 w-56">
            </div>
            <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl transition-all">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('apriori.hasil_rekomendasi') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Rules Grid/Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
        @if($rules->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($rules as $rule)
                    <div class="p-5 rounded-2xl border border-slate-200/90 bg-gradient-to-br from-white via-purple-50/20 to-slate-50 hover:border-purple-300 hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                                <span class="px-2.5 py-1 bg-purple-100 text-purple-800 font-mono font-extrabold text-xs rounded-lg">
                                    {{ $rule->id_rule }}
                                </span>
                                <span class="text-[11px] text-slate-400 font-medium">
                                    Diproses: {{ $rule->tanggal_proses ? \Carbon\Carbon::parse($rule->tanggal_proses)->format('d M Y, H:i') : '-' }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-start gap-2">
                                    <span class="text-xs font-bold text-slate-400 w-24 shrink-0 uppercase tracking-wider">Jika Beli:</span>
                                    <span class="font-extrabold text-slate-800 text-sm bg-slate-100 px-2.5 py-1 rounded-lg">
                                        {{ $rule->produk_antecedent }}
                                    </span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="text-xs font-bold text-purple-600 w-24 shrink-0 uppercase tracking-wider">Maka Beli:</span>
                                    <span class="font-extrabold text-purple-800 text-sm bg-purple-100/70 px-2.5 py-1 rounded-lg border border-purple-200/60 flex items-center gap-1.5">
                                        <i class="fa-solid fa-gift text-purple-600 text-xs"></i> {{ $rule->produk_consequent }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 font-extrabold text-xs rounded-lg border border-blue-200/60" title="Nilai Support">
                                    Support: {{ number_format($rule->nilai_support, 1) }}%
                                </span>
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-extrabold text-xs rounded-lg border border-emerald-200/60" title="Nilai Confidence">
                                    Confidence: {{ number_format($rule->nilai_confidence, 1) }}%
                                </span>
                            </div>
                            <span class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                <i class="fa-solid fa-circle-check text-[10px]"></i> Rekomendasi Kuat
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100">
                {{ $rules->links() }}
            </div>
        @else
            <div class="text-center py-12 text-slate-400">
                <i class="fa-solid fa-lightbulb-slash text-4xl mb-3 text-slate-300 block"></i>
                <p class="font-bold text-slate-600 text-base">Tidak ada aturan asosiasi ditemukan</p>
                <p class="text-xs text-slate-400 mt-1">Silakan jalankan proses Apriori atau ubah kata kunci pencarian.</p>
                <a href="{{ route('apriori.index') }}" class="mt-4 inline-block px-5 py-2.5 bg-purple-600 text-white font-bold text-xs rounded-xl shadow-sm hover:bg-purple-700">
                    Ke Halaman Proses Apriori
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
