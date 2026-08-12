@extends('layouts.app')

@section('title', 'Detail Struk Transaksi ' . $transaksi->id_transaksi)

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Back & Print Buttons -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('transaksis.index') }}" class="text-sm font-bold text-slate-600 hover:text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Transaksi
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm rounded-xl flex items-center gap-2 shadow-sm cursor-pointer">
            <i class="fa-solid fa-print"></i> Cetak Struk
        </button>
    </div>

    <!-- Receipt Card -->
    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-lg text-slate-800 font-mono text-sm space-y-6 print:shadow-none print:border-none">
        
        <!-- Header -->
        <div class="text-center space-y-1 pb-4 border-b border-slate-200">
            <h2 class="text-xl font-black font-sans text-slate-900">SERBA 123 TOY STORE</h2>
            <p class="text-xs text-slate-500 font-sans">Jl. Raya Jatinangor No. 123, Kabupaten Sumedang</p>
            <p class="text-xs text-slate-500 font-sans">Telp: (022) 779-1234 | WhatsApp: 0812-3456-7890</p>
        </div>

        <!-- Meta info -->
        <div class="grid grid-cols-2 text-xs space-y-1 font-sans">
            <div>
                <span class="text-slate-400 block">No. Struk:</span>
                <span class="font-mono font-bold text-slate-800 text-sm">{{ $transaksi->id_transaksi }}</span>
            </div>
            <div class="text-right">
                <span class="text-slate-400 block">Waktu Transaksi:</span>
                <span class="font-bold text-slate-800">{{ $transaksi->tanggal_transaksi->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Kasir:</span>
                <span class="font-bold text-slate-800">{{ $transaksi->user->nama ?? 'Kasir Store' }}</span>
            </div>
            <div class="text-right">
                <span class="text-slate-400 block">Shift Kerja:</span>
                <span class="font-bold uppercase text-slate-800">Shift {{ $transaksi->shift }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="border-t border-b border-dashed border-slate-300 py-3 space-y-2">
            @foreach($transaksi->detailTransaksis as $detail)
                <div class="flex justify-between items-start text-xs font-sans">
                    <div>
                        <span class="font-bold text-slate-800 block">{{ $detail->produk->nama_produk ?? 'Mainan' }}</span>
                        <span class="text-slate-500">{{ $detail->jumlah }} x Rp {{ number_format($detail->produk->harga ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <span class="font-bold text-slate-800">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="space-y-1 font-sans text-sm">
            <div class="flex justify-between text-base font-extrabold text-slate-900 pt-2">
                <span>TOTAL HARGA</span>
                <span>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="text-center text-xs text-slate-400 font-sans pt-4 border-t border-slate-200">
            <p class="font-semibold text-slate-600">Terima Kasih Atas Kunjungan Anda!</p>
            <p class="mt-1">Barang yang sudah dibeli dapat ditukar maksimal 1x24 jam dengan membawa struk ini.</p>
        </div>

    </div>

</div>

@push('styles')
<style>
    @media print {
        header, nav, footer, .no-print {
            display: none !important;
        }
        body {
            background: white !important;
        }
        main {
            padding: 0 !important;
        }
    }
</style>
@endpush
@endsection
