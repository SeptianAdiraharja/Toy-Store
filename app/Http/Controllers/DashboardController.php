<?php

namespace App\Http\Controllers;

use App\Models\AssociationRule;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPendapatan = Transaksi::sum('total_harga');
        $totalTransaksi = Transaksi::count();
        $totalProduk = Produk::count();
        $totalRules = AssociationRule::count();

        // Recent 5 transactions
        $recentTransaksis = Transaksi::with(['user', 'detailTransaksis.produk'])
            ->latest('tanggal_transaksi')
            ->take(5)
            ->get();

        // Top rules
        $topRules = AssociationRule::orderBy('nilai_confidence', 'desc')
            ->orderBy('nilai_support', 'desc')
            ->take(5)
            ->get();

        // Sales graph data by shift
        $shiftSales = Transaksi::select('shift', DB::raw('SUM(total_harga) as total'), DB::raw('COUNT(id) as count'))
            ->groupBy('shift')
            ->get()
            ->keyBy('shift');

        return view('dashboard.index', compact(
            'totalPendapatan',
            'totalTransaksi',
            'totalProduk',
            'totalRules',
            'recentTransaksis',
            'topRules',
            'shiftSales'
        ));
    }
}
