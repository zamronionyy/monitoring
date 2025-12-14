<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\StokBarang;
use App\Models\BarangKeluar;
use App\Models\DetailBarangKeluar;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. TENTUKAN RENTANG WAKTU BERDASARKAN FILTER
        $filter = $request->query('filter', 'harian'); // Default Hari Ini
        $now = Carbon::now();

        if ($filter == 'harian') {
            $startDate = $now->copy()->startOfDay();
            $endDate   = $now->copy()->endOfDay();
            $labelChart = 'Jam';
        } elseif ($filter == 'mingguan') {
            $startDate = $now->copy()->startOfWeek(); // Senin
            $endDate   = $now->copy()->endOfWeek();   // Minggu
            $labelChart = 'Hari';
        } elseif ($filter == 'bulanan') {
            $startDate = $now->copy()->startOfMonth();
            $endDate   = $now->copy()->endOfMonth();
            $labelChart = 'Tanggal';
        } elseif ($filter == 'tahunan') {
            $startDate = $now->copy()->startOfYear();
            $endDate   = $now->copy()->endOfYear();
            $labelChart = 'Bulan';
        }

        // ============================================================
        // 2. HITUNG KARTU (KPI) - TERFILTER WAKTU
        // ============================================================
        
        // Total Barang (Tetap Global / Snapshot saat ini)
        $totalJenisBarang = Barang::count();

        // Total Transaksi (Sesuai Filter)
        $totalPenjualan = BarangKeluar::whereBetween('tanggal', [$startDate, $endDate])->count();

        // Pelanggan yang Bertransaksi (Sesuai Filter)
        // Jika filter harian/bulanan, hitung pelanggan yg belanja di periode itu saja
        $totalCustomer = BarangKeluar::whereBetween('tanggal', [$startDate, $endDate])
                                     ->distinct('pelanggan_id')
                                     ->count('pelanggan_id');
        
        // Total Omset (Sesuai Filter)
        $totalOmset = BarangKeluar::whereBetween('tanggal', [$startDate, $endDate])->sum('total_harga');

        // Riwayat Stok Masuk (Sesuai Filter)
        $sumTotalStokMasuk = StokBarang::whereBetween('tanggal_masuk', [$startDate, $endDate])->sum('stok');

        // Riwayat Stok Keluar (Sesuai Filter)
        $sumTotalBarangKeluar = DetailBarangKeluar::whereHas('barangKeluar', function($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal', [$startDate, $endDate]);
        })->sum('jumlah');

        // Stok Fisik & Kritis (Snapshot Saat Ini - Tidak Terpengaruh Filter Waktu)
        // Stok fisik adalah kondisi real-time gudang saat ini, tidak bisa di-filter "stok bulan lalu" tanpa sistem history snapshot
        $allStokMasuk = StokBarang::sum('stok');
        $allStokKeluar = DetailBarangKeluar::sum('jumlah');
        $totalStokAkhir = $allStokMasuk - $allStokKeluar;

        // Hitung Stok Kritis
        $barangAll = Barang::withSum('stokBarangs', 'stok')
                           ->withSum('detailBarangKeluars', 'jumlah')
                           ->get();
        
        $criticalItemsList = [];
        foreach($barangAll as $b) {
            $in = (int) $b->stok_barangs_sum_stok;
            $out = (int) $b->detail_barang_keluars_sum_jumlah;
            $sisa = $in - $out;
            if ($sisa <= 5) {
                $b->stok_akhir = $sisa;
                $criticalItemsList[] = $b;
            }
        }
        $stokKritis = count($criticalItemsList);


        // ============================================================
        // 3. GENERATE GRAFIK (SESUAI RENTANG WAKTU)
        // ============================================================
        
        $grafikData = [];       // Admin: Penjualan
        $gudangMasukData = [];  // Gudang: Masuk
        $gudangKeluarData = []; // Gudang: Keluar
        $grafikLabel = [];      // Label X

        if ($filter == 'harian') {
            // Loop per Jam (07:00 - 21:00)
            for ($i = 7; $i <= 21; $i++) {
                $jam = sprintf("%02d:00", $i);
                $grafikLabel[] = $jam;
                
                // Query per jam hari ini
                $grafikData[] = BarangKeluar::whereBetween('tanggal', [$startDate, $endDate])
                    ->whereTime('created_at', '>=', "$i:00:00")->whereTime('created_at', '<=', "$i:59:59")->count();

                $gudangMasukData[] = StokBarang::whereBetween('tanggal_masuk', [$startDate, $endDate])
                    ->whereTime('created_at', '>=', "$i:00:00")->whereTime('created_at', '<=', "$i:59:59")->sum('stok');
                
                $gudangKeluarData[] = DetailBarangKeluar::whereHas('barangKeluar', function($q) use ($startDate, $endDate, $i) {
                    $q->whereBetween('tanggal', [$startDate, $endDate])
                      ->whereTime('created_at', '>=', "$i:00:00")->whereTime('created_at', '<=', "$i:59:59");
                })->sum('jumlah');
            }

        } elseif ($filter == 'mingguan') {
            // Loop per Hari (Senin - Minggu)
            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $grafikLabel[] = $date->translatedFormat('l'); // Nama Hari
                $d = $date->format('Y-m-d');

                $grafikData[] = BarangKeluar::whereDate('tanggal', $d)->count();
                $gudangMasukData[] = StokBarang::whereDate('tanggal_masuk', $d)->sum('stok');
                $gudangKeluarData[] = DetailBarangKeluar::whereHas('barangKeluar', function($q) use ($d) {
                    $q->whereDate('tanggal', $d);
                })->sum('jumlah');
            }

        } elseif ($filter == 'bulanan') {
            // Loop per Tanggal (1 - 31)
            $daysInMonth = $now->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $grafikLabel[] = strval($i); // Tgl 1, 2, 3...
                
                $grafikData[] = BarangKeluar::whereDate('tanggal', $now->format('Y-m-') . sprintf("%02d", $i))->count();
                $gudangMasukData[] = StokBarang::whereDate('tanggal_masuk', $now->format('Y-m-') . sprintf("%02d", $i))->sum('stok');
                $gudangKeluarData[] = DetailBarangKeluar::whereHas('barangKeluar', function($q) use ($now, $i) {
                    $q->whereDate('tanggal', $now->format('Y-m-') . sprintf("%02d", $i));
                })->sum('jumlah');
            }

        } elseif ($filter == 'tahunan') {
            // Loop per Bulan (Jan - Des)
            for ($i = 1; $i <= 12; $i++) {
                $grafikLabel[] = Carbon::create()->month($i)->translatedFormat('F'); // Nama Bulan
                
                $grafikData[] = BarangKeluar::whereYear('tanggal', $now->year)->whereMonth('tanggal', $i)->count();
                $gudangMasukData[] = StokBarang::whereYear('tanggal_masuk', $now->year)->whereMonth('tanggal_masuk', $i)->sum('stok');
                $gudangKeluarData[] = DetailBarangKeluar::whereHas('barangKeluar', function($q) use ($now, $i) {
                    $q->whereYear('tanggal', $now->year)->whereMonth('tanggal', $i);
                })->sum('jumlah');
            }
        }

        // ============================================================
        // 4. TABEL TRANSAKSI TERBARU (SESUAI FILTER)
        // ============================================================
        $penjualanTerbaru = BarangKeluar::with(['pelanggan', 'detailBarangKeluars.barang'])
                                        ->whereBetween('tanggal', [$startDate, $endDate]) // Filter Waktu
                                        ->orderBy('tanggal', 'desc')
                                        ->orderBy('id', 'desc')
                                        ->limit(10) // Ambil 10 transaksi terakhir di periode ini
                                        ->get();
        
        $recentSales = collect();
        foreach ($penjualanTerbaru as $nota) {
            foreach ($nota->detailBarangKeluars as $detail) {
                $recentSales->push([
                    'nama_barang' => $detail->barang->nama_barang ?? 'N/A',
                    'jumlah' => $detail->jumlah,
                    'tanggal' => Carbon::parse($nota->tanggal)->translatedFormat('d M'),
                    'pelanggan' => $nota->pelanggan->nama_pelanggan ?? 'Umum',
                ]);
            }
        }
        $recentSales = $recentSales->take(10); // Batasi tampilan tabel 10 baris

        return view('dashboard.index', [
            'totalJenisBarang' => $totalJenisBarang, 
            'totalPenjualan' => $totalPenjualan,
            'totalCustomer' => $totalCustomer,
            'totalOmset' => $totalOmset,
            'sumTotalStokMasuk' => $sumTotalStokMasuk,
            'sumTotalBarangKeluar' => $sumTotalBarangKeluar,
            'totalStokAkhir' => $totalStokAkhir,
            'stokKritis' => $stokKritis,
            'criticalItemsList' => $criticalItemsList,
            'recentSales' => $recentSales,
            'activeFilter' => $filter,
            'grafikLabel' => $grafikLabel,
            'grafikData' => $grafikData,
            
            // Untuk Kartu Gudang Dashboard (Masih menggunakan logic yang sama dengan Admin cards di atas)
            'stokMasukHariIni' => $sumTotalStokMasuk,   // Menggunakan nilai terfilter
            'stokKeluarHariIni' => $sumTotalBarangKeluar, // Menggunakan nilai terfilter
            
            'gudangMasukData' => $gudangMasukData,
            'gudangKeluarData' => $gudangKeluarData,
        ]);
    }
}