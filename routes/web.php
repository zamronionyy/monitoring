<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StokBarangController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BarangController; 
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\KMeansController;
use App\Http\Controllers\AprioriController;
use App\Http\Controllers\AkunRoleController; 
use App\Http\Controllers\LaporanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Grup untuk SEMUA PENGGUNA YANG SUDAH LOGIN (Admin & Gudang)
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard & Profile
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    
    // --- GRUP KHUSUS ADMIN (TERMASUK LAPORAN) ---
    Route::middleware(['role:admin'])->group(function () {

        // Pengelolaan Akun
        Route::resource('akunrole', AkunRoleController::class)->only(['index', 'create','edit', 'store', 'update']);
        Route::delete('akunrole/{akunrole}', [AkunRoleController::class, 'destroy'])->name('akunrole.destroy');
        
        // Manajemen Kategori (CRUD)
        Route::resource('kategori', KategoriController::class);
        
        // MANAJEMEN BARANG (MASTER DATA)
        Route::delete('/barang/delete-multiple', [BarangController::class, 'deleteMultiple'])->name('barang.deleteMultiple');
        Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
        Route::get('/barang/create', [BarangController::class, 'create'])->name('barang.create');
        Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
        Route::get('/barang/{barang}/edit', [BarangController::class, 'edit'])->name('barang.edit');
        Route::put('/barang/{barang}', [BarangController::class, 'update'])->name('barang.update');
        Route::delete('/barang/{barang}', [BarangController::class, 'destroy'])->name('barang.destroy');
        
        // Rute Import/Export Barang
        Route::get('/barang-import', [BarangController::class, 'showImportForm'])->name('barang.showImportForm');
        Route::post('/barang-import', [BarangController::class, 'importExcel'])->name('barang.importExcel');
        Route::get('/barang/import/template', [BarangController::class, 'downloadTemplate'])->name('barang.downloadTemplate');
        Route::get('/barang/export/excel', [BarangController::class, 'exportExcel'])->name('barang.exportExcel');
        
        // Manajemen Pelanggan (CRUD)
        Route::resource('pelanggan', PelangganController::class);

        // ANALISIS DATA MINING (KHUSUS ADMIN)
        Route::prefix('analisis-kmeans')->name('k_means.')->group(function () {
            Route::get('/', [KMeansController::class, 'index'])->name('index'); 
            Route::post('/run', [KMeansController::class, 'run'])->name('run');
        });

        Route::prefix('analisis-apriori')->name('apriori.')->group(function () {
             Route::get('/', [AprioriController::class, 'index'])->name('index'); 
             Route::get('/run', [AprioriController::class, 'run'])->name('run'); 
        });
        
        // =====================================================================
        // LAPORAN RANGKUMAN (PINDAH: KHUSUS ADMIN)
        // =====================================================================
        Route::prefix('laporan')->name('laporan.')->group(function () {
             Route::get('/', [LaporanController::class, 'index'])->name('index'); 
             Route::get('/filter', [LaporanController::class, 'filter'])->name('filter');
             // 1. Route Export Laporan Lengkap
             Route::get('/export/excel', [LaporanController::class, 'exportExcel'])->name('exportExcel');
             // 2. Route Export Top Pelanggan
             Route::get('/export/top-pelanggan', [LaporanController::class, 'exportTopPelangganExcel'])->name('exportTopPelanggan');
        });
        
    }); // AKHIR DARI GRUP ROLE:ADMIN


    // --- GRUP UNTUK GUDANG & ADMIN (HANYA OPERASIONAL) ---
    Route::middleware(['role:admin,gudang'])->group(function () {
        
        // STOK & PENJUALAN (OPERASIONAL)
        
        // Stok Barang
        Route::delete('/stokbarang/delete-multiple', [StokBarangController::class, 'deleteMultiple'])->name('stokbarang.deleteMultiple');
        Route::get('/stokbarang/template', [StokBarangController::class, 'downloadTemplate'])->name('stokbarang.template'); 
        Route::get('/stokbarang/import/form', [StokBarangController::class, 'showImportForm'])->name('stokbarang.import'); 
        Route::post('/stokbarang/import/excel', [StokBarangController::class, 'importExcel'])->name('stokbarang.import.excel'); 
        Route::get('/stokbarang/export/excel', [StokBarangController::class, 'exportExcel'])->name('stokbarang.export.excel'); 
        Route::resource('stokbarang', StokBarangController::class); 

        // Barang Keluar (Penjualan)
        Route::resource('barangkeluar', BarangKeluarController::class); 
        Route::get('/barangkeluar/{id}/detail', [BarangKeluarController::class, 'detail'])->name('barangkeluar.detail');
        Route::get('/barangkeluar/print/{id_transaksi}', [BarangKeluarController::class, 'printDetail'])->name('barangkeluar.print');
        Route::get('/barangkeluar/export/excel', [BarangKeluarController::class, 'exportExcel'])->name('barangkeluar.export.excel');
        Route::get('/barangkeluar/export/pdf', [BarangKeluarController::class, 'exportPdf'])->name('barangkeluar.export.pdf');
        Route::get('/barangkeluar/{id}/download-pdf', [BarangKeluarController::class, 'downloadPdf'])->name('barangkeluar.download-pdf');
    }); // AKHIR DARI GRUP ROLE:ADMIN,GUDANG

});

// File rute otentikasi (Login, Register, dll)
require __DIR__.'/auth.php';
