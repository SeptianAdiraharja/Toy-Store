@extends('layouts.app')

@section('title', 'Proses Algoritma Apriori')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-brain text-purple-600"></i> Proses Algoritma Apriori
            </h1>
            <p class="text-sm text-slate-500 mt-1">Pembentukan Frequent Itemset & Association Rules (Market Basket Analysis)</p>
        </div>
        <a href="{{ route('apriori.hasil_rekomendasi') }}" class="px-4 py-2.5 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-sm rounded-xl border border-purple-200 transition-all flex items-center gap-2">
            <i class="fa-solid fa-lightbulb"></i> Lihat Aturan Aktif
        </a>
    </div>

    <!-- Parameter Config Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-6">
        <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">Pengaturan Parameter Batas Ambang (Threshold)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tentukan nilai Minimum Support dan Minimum Confidence dalam persentase (%)</p>
            </div>
            <button type="button" onclick="setBab3Defaults()" class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold rounded-lg border border-purple-200 transition-all flex items-center gap-1.5">
                <i class="fa-solid fa-bookmark"></i> (Support 40%, Conf 70%)
            </button>
        </div>

        <form method="POST" action="{{ route('apriori.process') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Minimum Support -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <label for="min_support" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-percent text-blue-600"></i> Minimum Support (%)
                        </label>
                        <span id="support_val_badge" class="text-xs font-black px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg">40%</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Menentukan tingkat frekuensi kemunculan kombinasi produk dalam seluruh transaksi. Semakin tinggi nilainya, hanya kombinasi paling sering yang akan diproses.
                    </p>
                    <input
                        type="number"
                        step="0.1"
                        min="0.1"
                        max="100"
                        name="min_support"
                        id="min_support"
                        value="{{ old('min_support', 40.0) }}"
                        oninput="document.getElementById('support_val_badge').innerText = this.value + '%'"
                        class="w-full px-4 py-2.5 bg-white rounded-xl border border-slate-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-100 font-bold text-slate-800"
                        required>
                    @error('min_support')
                        <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Minimum Confidence -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <label for="min_confidence" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-emerald-600"></i> Minimum Confidence (%)
                        </label>
                        <span id="conf_val_badge" class="text-xs font-black px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg">70%</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Menentukan kepastian atau tingkat kekuatan hubungan asosiasi antara produk antecedent dan consequent (pemicu dan rekomendasi).
                    </p>
                    <input
                        type="number"
                        step="0.1"
                        min="0.1"
                        max="100"
                        name="min_confidence"
                        id="min_confidence"
                        value="{{ old('min_confidence', 70.0) }}"
                        oninput="document.getElementById('conf_val_badge').innerText = this.value + '%'"
                        class="w-full px-4 py-2.5 bg-white rounded-xl border border-slate-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-100 font-bold text-slate-800"
                        required>
                    @error('min_confidence')
                        <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-sm rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-play"></i> Jalankan Perhitungan Apriori
                </button>
            </div>
        </form>
    </div>

    <!-- Active Stored Rules Summary -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-purple-600"></i> Aturan Asosiasi Aktif Dalam Basis Data ({{ $rules->count() }})
            </h3>
            <span class="text-xs text-slate-500">Hasil perhitungan otomatis tersimpan untuk modul Kasir POS</span>
        </div>

        @if($rules->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-400 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3">ID Rule</th>
                            <th class="px-4 py-3">Produk Antecedent (Jika Beli)</th>
                            <th class="px-4 py-3">Produk Consequent (Maka Beli)</th>
                            <th class="px-4 py-3 text-center">Support (%)</th>
                            <th class="px-4 py-3 text-center">Confidence (%)</th>
                            <th class="px-4 py-3">Tanggal Diproses</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rules as $rule)
                            <tr class="hover:bg-purple-50/50 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-slate-800">{{ $rule->id_rule }}</td>
                                <td class="px-4 py-3 font-bold text-slate-800">
                                    <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-slate-800 inline-block">
                                        {{ $rule->produk_antecedent }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-bold text-purple-700">
                                    <span class="px-2.5 py-1 bg-purple-50 rounded-lg text-purple-800 inline-block border border-purple-100">
                                        <i class="fa-solid fa-angles-right text-xs mr-1 text-purple-500"></i> {{ $rule->produk_consequent }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-extrabold text-blue-700 bg-blue-50 px-2 py-0.5 rounded text-xs">
                                        {{ number_format($rule->nilai_support, 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-extrabold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-xs">
                                        {{ number_format($rule->nilai_confidence, 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">
                                    {{ $rule->tanggal_proses ? \Carbon\Carbon::parse($rule->tanggal_proses)->format('d/m/Y H:i') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-slate-400 text-sm">
                <i class="fa-solid fa-box-open text-3xl mb-2 block text-slate-300"></i>
                Belum ada aturan asosiasi tersimpan. Masukkan parameter di atas dan tekan <strong>Jalankan Perhitungan Apriori</strong>.
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function setBab3Defaults() {
    document.getElementById('min_support').value = 40;
    document.getElementById('min_confidence').value = 70;
    document.getElementById('support_val_badge').innerText = '40%';
    document.getElementById('conf_val_badge').innerText = '70%';
}
</script>
@endpush
@endsection
