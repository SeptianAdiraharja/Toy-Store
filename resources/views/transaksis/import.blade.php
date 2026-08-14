@extends('layouts.app')

@section('title', 'Impor Data Transaksi')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Page Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Impor Data Transaksi Penjualan</h1>
            <p class="text-sm text-slate-500 mt-1">Unggah file Excel (.xlsx, .xls) atau CSV (.csv) untuk impor otomatis ke database</p>
        </div>
        <a href="{{ route('transaksis.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition-all flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Import Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-6">
        
        <!-- Format Info Banner -->
        <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 border border-blue-200/80 p-4 rounded-xl flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold shrink-0 mt-0.5 shadow-sm">
                <i class="fa-solid fa-file-excel text-lg"></i>
            </div>
            <div class="text-xs text-slate-700 leading-relaxed space-y-1.5 flex-grow">
                <div class="flex items-center justify-between">
                    <p class="font-bold text-sm text-slate-900">Format File Terdukung (.xlsx, .xls, .csv):</p>
                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 rounded-full font-bold text-[10px]">
                        <i class="fa-solid fa-sync mr-1"></i> Sinkronisasi Produk Otomatis
                    </span>
                </div>
                <p>Sistem secara otomatis mendeteksi kolom: <span class="font-semibold text-blue-900">TANGGAL, NAMA BARANG, JUMLAH, TOTAL HARGA, SHIFT</span>.</p>
                <p class="text-slate-600 bg-white/70 p-2 rounded-lg border border-blue-100">
                    <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> <strong>Pemberitahuan:</strong> Setiap data produk yang ada di file import akan otomatis disimpan ke database (<span class="font-semibold text-slate-800">Katalog Produk</span>) dengan ID produk unik, kategori, harga satuan, dan stok sehingga langsung dapat ditampilkan di katalog, kasir, dan analisis Apriori.
                </p>
                <div class="flex flex-wrap gap-2 pt-1">
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-md font-bold text-[11px]"><i class="fa-solid fa-file-excel mr-1"></i> Excel (.xlsx)</span>
                    <span class="px-2.5 py-1 bg-green-100 text-green-800 rounded-md font-bold text-[11px]"><i class="fa-solid fa-file-csv mr-1"></i> CSV (.csv)</span>
                    <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-md font-bold text-[11px]"><i class="fa-solid fa-file-code mr-1"></i> Excel Legacy (.xls)</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('transaksis.import.process') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Upload Methods Tabs -->
            <div class="border-b border-slate-200 flex gap-4">
                <button type="button" id="tabFileUploadBtn" onclick="switchTab('file')" class="pb-3 text-sm font-bold text-brand-600 border-b-2 border-brand-600 flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Unggah File Excel / CSV
                </button>
                <button type="button" id="tabTextPasteBtn" onclick="switchTab('text')" class="pb-3 text-sm font-bold text-slate-400 border-b-2 border-transparent hover:text-slate-600 flex items-center gap-2">
                    <i class="fa-solid fa-paste"></i> Input Teks Manual
                </button>
            </div>

            <!-- Tab 1: File Upload Box -->
            <div id="fileUploadSection" class="space-y-4">
                <label for="file" class="block text-sm font-bold text-slate-700">
                    Pilih File Excel / CSV <span class="text-rose-500">*</span>
                </label>
                
                <div id="dropZone" class="border-2 border-dashed border-slate-300 hover:border-brand-500 bg-slate-50/50 hover:bg-brand-50/30 rounded-2xl p-8 text-center transition-all cursor-pointer relative">
                    <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv,.txt" onchange="handleFileSelect(this)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    
                    <div id="uploadPrompt" class="space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto text-2xl font-bold shadow-xs">
                            <i class="fa-solid fa-file-arrow-up"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-700">Klik atau seret file Excel/CSV ke sini</p>
                            <p class="text-xs text-slate-400 mt-1">Mendukung format .xlsx, .xls, .csv (Maksimal 10MB)</p>
                        </div>
                    </div>

                    <div id="selectedFileInfo" class="hidden items-center justify-center gap-3 p-4 bg-white rounded-xl border border-brand-200 shadow-xs max-w-md mx-auto">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg">
                            <i class="fa-solid fa-file-circle-check"></i>
                        </div>
                        <div class="text-left overflow-hidden">
                            <p id="fileName" class="text-sm font-bold text-slate-800 truncate">filename.xlsx</p>
                            <p id="fileSize" class="text-xs text-slate-500">0 KB</p>
                        </div>
                    </div>
                </div>
                @error('file')
                    <p class="text-xs font-bold text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tab 2: Manual Text Input -->
            <div id="textPasteSection" class="space-y-4 hidden">
                <div class="flex items-center justify-between">
                    <label for="transaction_data" class="block text-sm font-bold text-slate-700">
                        Input Teks Data Transaksi
                    </label>
                    <div class="flex gap-2">
                        <button type="button" onclick="loadBab3Sample()" class="px-2.5 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold rounded-lg transition-all border border-purple-200">
                            Contoh Bab 3
                        </button>
                        <button type="button" onclick="loadExtendedSample()" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg transition-all border border-blue-200">
                            Contoh 10 TRX
                        </button>
                    </div>
                </div>

                <textarea 
                    name="transaction_data" 
                    id="transaction_data" 
                    rows="10" 
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 font-mono text-xs leading-relaxed text-slate-800"
                    placeholder="Contoh:&#10;T1, pagi, Mainan 1, Mainan 2, Mainan 5&#10;T2, siang, Mainan 2, Mainan 3">{{ old('transaction_data') }}</textarea>
            </div>

            <!-- Form Action Footer -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <span class="text-xs text-slate-400">Pastikan format kolom file sesuai sebelum mengunggah.</span>
                <div class="flex items-center gap-3">
                    <a href="{{ route('transaksis.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition-all">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-xl shadow-md shadow-brand-900/20 transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-file-import"></i> Proses Impor Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function switchTab(type) {
    const fileSec = document.getElementById('fileUploadSection');
    const textSec = document.getElementById('textPasteSection');
    const fileBtn = document.getElementById('tabFileUploadBtn');
    const textBtn = document.getElementById('tabTextPasteBtn');

    if (type === 'file') {
        fileSec.classList.remove('hidden');
        textSec.classList.add('hidden');
        fileBtn.className = 'pb-3 text-sm font-bold text-brand-600 border-b-2 border-brand-600 flex items-center gap-2';
        textBtn.className = 'pb-3 text-sm font-bold text-slate-400 border-b-2 border-transparent hover:text-slate-600 flex items-center gap-2';
    } else {
        fileSec.classList.add('hidden');
        textSec.classList.remove('hidden');
        textBtn.className = 'pb-3 text-sm font-bold text-brand-600 border-b-2 border-brand-600 flex items-center gap-2';
        fileBtn.className = 'pb-3 text-sm font-bold text-slate-400 border-b-2 border-transparent hover:text-slate-600 flex items-center gap-2';
    }
}

function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('fileName').innerText = file.name;
        document.getElementById('fileSize').innerText = (file.size / 1024).toFixed(1) + ' KB';
        
        document.getElementById('uploadPrompt').classList.add('hidden');
        document.getElementById('selectedFileInfo').classList.remove('hidden');
        document.getElementById('selectedFileInfo').classList.add('flex');
    }
}

function loadBab3Sample() {
    const bab3Text = `T1, pagi, Mainan 1, Mainan 2, Mainan 5
T2, siang, Mainan 2, Mainan 3
T3, pagi, Mainan 1, Mainan 4
T4, sore, Mainan 1, Mainan 2, Mainan 3
T5, siang, Mainan 2, Mainan 5`;
    document.getElementById('transaction_data').value = bab3Text;
}

function loadExtendedSample() {
    const extendedText = `TRX-101, pagi, Mainan 1, Mainan 2, Mainan 5
TRX-102, siang, Mainan 2, Mainan 3
TRX-103, pagi, Mainan 1, Mainan 4
TRX-104, sore, Mainan 1, Mainan 2, Mainan 3
TRX-105, siang, Mainan 2, Mainan 5
TRX-106, pagi, Mainan 1, Mainan 2, Robot Transformers Prime
TRX-107, siang, Mobil Remote Control Offroad, Robot Transformers Prime
TRX-108, sore, Lego Classic Building Bricks, Pasir Kinetik Ajaib Set
TRX-109, pagi, Mainan 3, Mainan 2
TRX-110, siang, Mainan 5, Mainan 2, Boneka Teddy Bear Jumbo`;
    document.getElementById('transaction_data').value = extendedText;
}
</script>
@endpush
@endsection
