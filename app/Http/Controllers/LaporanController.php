<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(['user', 'detailTransaksis.produk']);

        $tanggalMulai = $request->get('tanggal_mulai', date('Y-m-01'));
        $tanggalSelesai = $request->get('tanggal_selesai', date('Y-m-d'));
        $shift = $request->get('shift');

        if ($tanggalMulai) {
            $query->whereDate('tanggal_transaksi', '>=', $tanggalMulai);
        }

        if ($tanggalSelesai) {
            $query->whereDate('tanggal_transaksi', '<=', $tanggalSelesai);
        }

        if ($shift) {
            $query->where('shift', $shift);
        }

        $transaksis = $query->latest('tanggal_transaksi')->get();

        $totalPendapatan = $transaksis->sum('total_harga');
        $totalTransaksiCount = $transaksis->count();
        $totalItemsSold = $transaksis->sum(function ($t) {
            return $t->detailTransaksis->sum('jumlah');
        });

        // Group by shift summary
        $shiftSummary = $transaksis->groupBy('shift')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('total_harga')
            ];
        });

        return view('laporan.index', compact(
            'transaksis',
            'tanggalMulai',
            'tanggalSelesai',
            'shift',
            'totalPendapatan',
            'totalTransaksiCount',
            'totalItemsSold',
            'shiftSummary'
        ));
    }

    public function cetak(Request $request)
    {
        $query = Transaksi::with(['user', 'detailTransaksis.produk']);

        $tanggalMulai = $request->get('tanggal_mulai');
        $tanggalSelesai = $request->get('tanggal_selesai');
        $shift = $request->get('shift');

        if ($tanggalMulai) {
            $query->whereDate('tanggal_transaksi', '>=', $tanggalMulai);
        }

        if ($tanggalSelesai) {
            $query->whereDate('tanggal_transaksi', '<=', $tanggalSelesai);
        }

        if ($shift) {
            $query->where('shift', $shift);
        }

        $transaksis = $query->latest('tanggal_transaksi')->get();
        $totalPendapatan = $transaksis->sum('total_harga');

        return view('laporan.cetak', compact('transaksis', 'tanggalMulai', 'tanggalSelesai', 'shift', 'totalPendapatan'));
    }
}
