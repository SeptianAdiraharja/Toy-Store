<?php

use App\Http\Controllers\AprioriController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureUserRole;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Dashboard (Owner & Admin)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(EnsureUserRole::class . ':owner,admin')
        ->name('dashboard');

    // Kasir POS & Real-Time Recommendations (Kasir, Admin, Owner)
    Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
    Route::post('/kasir/recommendations', [KasirController::class, 'getRecommendations'])->name('kasir.recommendations');
    Route::post('/kasir/store', [KasirController::class, 'store'])->name('kasir.store');

    // Kelola Data Produk (Admin CRUD, Owner & Kasir View)
    Route::resource('produks', ProdukController::class);

    // Kelola Data User (Admin only)
    Route::resource('users', UserController::class)->middleware(EnsureUserRole::class . ':admin');

    // Kelola Data Transaksi (Admin & Owner)
    Route::get('/transaksis/import', [TransaksiController::class, 'importForm'])->name('transaksis.import.form');
    Route::post('/transaksis/import', [TransaksiController::class, 'importProcess'])->name('transaksis.import.process');
    Route::resource('transaksis', TransaksiController::class)->only(['index', 'show', 'destroy']);

    // Proses Algoritma Apriori (Admin & Owner)
    Route::get('/apriori', [AprioriController::class, 'index'])->name('apriori.index');
    Route::post('/apriori/process', [AprioriController::class, 'process'])->name('apriori.process');
    Route::get('/apriori/hasil-rekomendasi', [AprioriController::class, 'hasilRekomendasi'])->name('apriori.hasil_rekomendasi');

    // Laporan Penjualan (Owner & Admin)
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');
});
