<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\BarangKeluar;
use App\Models\DetailBarangKeluar;
use App\Models\Barang;
use App\Models\StokBarang;
use App\Models\Kategori;
use App\Models\AturanAsosiasi; 
use Symfony\Component\HttpFoundation\StreamedResponse; 

// ASUMSI: Jika Anda menggunakan Maatwebsite/Excel
// use Maatwebsite\Excel\Facades\Excel; 
// use App\Exports\LaporanPenjualanExport; 

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman utama laporan dengan periode default (bulanan saat ini).
     */
    public function index()
    {
        $tipePeriode = 'bulanan';
        // Tentukan tanggal default (Bulan ini)
        [$start, $end] = $this->determineDateRange($tipePeriode, null, null);

        $dataLaporan = $this->generateLaporan($tipePeriode, $start, $end);
        
        return view('laporan.index', [
            'results' => $dataLaporan,
            'tipe_periode' => $tipePeriode,
            'start_date' => $start,
            'end_date' => $end,
        ]);
    }

    /**
     * Memproses filter dan menampilkan laporan sesuai permintaan.
     */
    public function filter(Request $request)
    {
        $request->validate([
            'tipe_periode' => 'required|in:mingguan,bulanan,tahunan,custom',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $tipePeriode = $request->input('tipe_periode');
        $start = $request->input('tanggal_mulai');
        $end = $request->input('tanggal_selesai');
        
        // Tentukan Tanggal (menggunakan input user atau default)
        [$start, $end] = $this->determineDateRange($tipePeriode, $start, $end);

        $dataLaporan = $this->generateLaporan($tipePeriode, $start, $end);

        return view('laporan.index', [
            'results' => $dataLaporan,
            'tipe_periode' => $tipePeriode,
            'start_date' => $start,
            'end_date' => $end,
        ]);
    }
    
    // =======================================================
    // === FUNGSI UTAMA UNTUK MENGAMBIL DAN MENGHITUNG DATA ===
    // =======================================================
    private function generateLaporan(string $tipe, string $start, string $end)
    {
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();
        
        $penjualan = $this->getPenjualanSummary($startDate, $endDate);
        $stok = $this->getStokSummary();
        $rekomendasi = $this->getRecommendationSummary($startDate, $endDate);

        return [
            'penjualan' => $penjualan,
            'stok' => $stok,
            'rekomendasi' => $rekomendasi,
            'tanggal_laporan' => $startDate->format('d M Y') . ' s/d ' . $endDate->format('d M Y'),
            'tipe' => $tipe,
        ];
    }
    
    /**
     * Menentukan rentang tanggal berdasarkan tipe periode.
     */
    private function determineDateRange(string $tipe, ?string $start, ?string $end): array
    {
        // Prioritas: Jika user memasukkan tanggal kustom, gunakan itu.
        if ($tipe === 'custom' && !empty($start) && !empty($end)) {
            return [$start, $end];
        }
        
        $now = Carbon::now();
        
        switch ($tipe) {
            case 'mingguan':
                $start = $now->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
                $end = $now->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
                break;
            case 'bulanan':
                $start = $now->copy()->startOfMonth()->toDateString();
                $end = $now->copy()->endOfMonth()->toDateString();
                break;
            case 'tahunan':
                $start = $now->copy()->startOfYear()->toDateString();
                $end = $now->copy()->endOfYear()->toDateString();
                break;
            default:
                // Default ke Bulanan jika custom/input tidak lengkap
                $start = $now->copy()->startOfMonth()->toDateString();
                $end = $now->copy()->endOfMonth()->toDateString();
                break;
        }
        return [$start, $end];
    }
    
    /**
     * Menghitung Rangkuman Penjualan (Omset, Transaksi, Barang Terjual) DAN TOP PELANGGAN.
     */
    private function getPenjualanSummary($startDate, $endDate)
    {
        // RANGKUMAN UTAMA
        $summary = BarangKeluar::whereBetween('tanggal', [$startDate, $endDate])
                            ->select(
                                DB::raw('COUNT(id) AS total_transaksi'),
                                DB::raw('SUM(total_harga) AS total_omset')
                            )
                            ->first();

        // BARANG TERJUAL PER ITEM (Top 10)
        $topBarangTerjual = DetailBarangKeluar::join('barang_keluars', 'detail_barang_keluars.barang_keluar_id', '=', 'barang_keluars.id')
                                                ->join('barangs', 'detail_barang_keluars.barang_id', '=', 'barangs.id')
                                                // Memuat relasi Kategori secara Eager Loading
                                                ->with('barang.kategori') 
                                                ->whereBetween('barang_keluars.tanggal', [$startDate, $endDate])
                                                ->select(
                                                    'barangs.id as barang_id', // Ambil ID barang untuk relasi
                                                    'barangs.kode_barang',
                                                    'barangs.nama_barang',
                                                    DB::raw('SUM(detail_barang_keluars.jumlah) AS total_qty'),
                                                    DB::raw('SUM(detail_barang_keluars.total_harga) AS total_nilai')
                                                )
                                                // Kelompokkan berdasarkan kolom yang dipilih
                                                ->groupBy(
                                                    'barangs.id',
                                                    'barangs.kode_barang', 
                                                    'barangs.nama_barang',
                                                )
                                                ->orderByDesc('total_qty')
                                                ->limit(10)
                                                ->get();

        // Setelah mengambil data, petakan kembali untuk menyertakan relasi yang dimuat
        $topBarangTerjual = $topBarangTerjual->map(function ($item) {
            // Ambil objek Barang lengkap, yang sudah memiliki relasi Kategori
            $barang = Barang::with('kategori')->find($item->barang_id);
            if ($barang) {
                $item->nama_kategori = $barang->kategori->nama_kategori ?? 'Tanpa Kategori';
            } else {
                 $item->nama_kategori = 'Data Hilang';
            }
            return $item;
        });

        // TOP 10 PELANGGAN BERDASARKAN JUMLAH TRANSAKSI (NOTA)
        $topPelanggan = BarangKeluar::whereBetween('tanggal', [$startDate, $endDate])
            ->whereNotNull('pelanggan_id') 
            ->select(
                'pelanggan_id',
                DB::raw('COUNT(id) AS total_nota'),
                DB::raw('SUM(total_harga) AS total_nilai_pembelian')
            )
            ->groupBy('pelanggan_id')
            ->orderByDesc('total_nota')
            ->limit(10)
            ->with('pelanggan') // Eager load relasi pelanggan untuk mengambil nama
            ->get();
        // ===============================================

        $totalQtyTerjual = $topBarangTerjual->sum('total_qty');
                                                
        return [
            'total_omset' => $summary->total_omset ?? 0,
            'total_transaksi' => $summary->total_transaksi ?? 0,
            'total_qty_terjual' => $totalQtyTerjual,
            'top_terjual' => $topBarangTerjual,
            'top_pelanggan' => $topPelanggan,
        ];
    }
    
    /**
     * Menghitung Rangkuman Stok Barang (Saat Ini, Kritis, Fast/Slow Moving).
     */
   private function getStokSummary()
    {
        $stokBarang = Barang::select('id', 'nama_barang', 'kode_barang')
                             ->withSum('stokBarangs', 'stok') 
                             ->withSum('detailBarangKeluars', 'jumlah') 
                             ->get();
                             
        $totalStokAkhir = 0;
        $stokKritisCount = 0;
        $dataStok = [];
        
        $BATAS_STOK_MINIMAL_DEFAULT = 5; 

        foreach ($stokBarang as $barang) {
            $totalMasuk = (int) $barang->stok_barangs_sum_stok;
            $totalKeluar = (int) $barang->detail_barang_keluars_sum_jumlah;
            $stokAkhir = $totalMasuk - $totalKeluar;
            
            $totalStokAkhir += $stokAkhir;
            
            $stokMinimal = $BATAS_STOK_MINIMAL_DEFAULT; 
            
            // === PERBAIKAN DISINI ===
            // Hapus syarat "$stokAkhir > 0" agar stok 0 juga terhitung kritis
            if ($stokAkhir <= $stokMinimal) { 
                $stokKritisCount++;
            }
            // ========================
            
            $dataStok[] = [
                'nama_barang' => $barang->nama_barang,
                'kode_barang' => $barang->kode_barang,
                'stok_akhir' => $stokAkhir,
                'stok_minimal' => $stokMinimal, 
            ];
        }
        
        // Filter stok yang lebih dari 0 untuk fast/slow moving
        $dataStokCollection = collect($dataStok)->filter(fn($item) => $item['stok_akhir'] > 0);
        
        // Barang Fast/Slow Moving (berdasarkan Stok Akhir)
        $fastMoving = $dataStokCollection->sortByDesc('stok_akhir')->take(5)->values()->all();
        $slowMoving = $dataStokCollection->sortBy('stok_akhir')->take(5)->values()->all();
        
        return [
            'total_stok_sku' => count($stokBarang),
            'total_unit_stok' => $totalStokAkhir,
            'stok_kritis_count' => $stokKritisCount,
            'fast_moving' => $fastMoving,
            'slow_moving' => $slowMoving,
            'data' => $dataStok, 
        ];
    }
    
    /**
     * Menghitung Rangkuman Rekomendasi (K-Means/Apriori).
     */
    private function getRecommendationSummary($startDate, $endDate)
    {
        // 1. Ambil Kategori Terlaris (Omset)
        $topKategoriOmset = Kategori::getSalesPerformance($startDate, $endDate)
            ->sortByDesc('total_omset')
            ->take(3)
            ->values();
        
        // Catatan: Apriori dihapus total dari hasil return.
        
        return [
            'kategori_terlaris' => $topKategoriOmset, 
        ];
    }
    
    // =========================================================
    // === METHOD UNTUK EXPORT LAPORAN =========================
    // =========================================================

    /**
     * Export seluruh data laporan ringkasan ke CSV/Excel.
     */
    public function exportExcel(Request $request)
    {
        // Ambil filter yang digunakan di halaman laporan
        $tipePeriode = $request->input('tipe_periode', 'bulanan');
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        
        // Tentukan Rentang Tanggal yang Benar
        [$start, $end] = $this->determineDateRange($tipePeriode, $start, $end);
        
        // Generate seluruh data laporan untuk ekspor
        $dataLaporan = $this->generateLaporan($tipePeriode, $start, $end);
        
        $filename = 'laporan_ringkasan_' . $start . '_sd_' . $end . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($dataLaporan) {
            $file = fopen('php://output', 'w');

            // --- Bagian 1: Rangkuman Kinerja Utama ---
            fputcsv($file, ['RINGKASAN KINERJA']);
            fputcsv($file, ['Total Omset', 'Total Transaksi', 'Total Unit Terjual', 'SKU Stok Kritis']);
            fputcsv($file, [
                $dataLaporan['penjualan']['total_omset'] ?? 0,
                $dataLaporan['penjualan']['total_transaksi'] ?? 0,
                $dataLaporan['penjualan']['total_qty_terjual'] ?? 0,
                $dataLaporan['stok']['stok_kritis_count'] ?? 0,
            ]);
            fputcsv($file, ['']); // Baris kosong

            // --- Bagian 2: Top 10 Barang Terjual ---
            fputcsv($file, ['TOP 10 BARANG TERJUAL (UNIT)']);
            fputcsv($file, ['No', 'Kode Barang', 'Nama Barang', 'Kategori', 'Unit Terjual', 'Nilai Penjualan']);
            foreach (($dataLaporan['penjualan']['top_terjual'] ?? []) as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->kode_barang ?? ($item['kode_barang'] ?? ''),
                    $item->nama_barang ?? ($item['nama_barang'] ?? ''),
                    $item->nama_kategori ?? ($item['nama_kategori'] ?? ''),
                    $item->total_qty ?? ($item['total_qty'] ?? 0),
                    $item->total_nilai ?? ($item['total_nilai'] ?? 0),
                ]);
            }
            fputcsv($file, ['']); // Baris kosong

            // --- Bagian 3: Top 10 Pelanggan (Omset & Nota) ---
            fputcsv($file, ['TOP 10 PELANGGAN']);
            fputcsv($file, ['No', 'Nama Pelanggan', 'Total Nota', 'Total Pembelian']);
            
            // Urutkan ulang berdasarkan omset (total_nilai_pembelian) untuk ekspor
            $topPelanggan = collect($dataLaporan['penjualan']['top_pelanggan'] ?? [])->sortByDesc('total_nilai_pembelian');
            
            foreach ($topPelanggan as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->pelanggan->nama_pelanggan ?? 'Tidak Tercatat',
                    $item->total_nota ?? 0,
                    $item->total_nilai_pembelian ?? 0,
                ]);
            }
            fputcsv($file, ['']); // Baris kosong

            // --- Bagian 4: Stok Fast & Slow Moving ---
            fputcsv($file, ['BARANG FAST MOVING (STOK TERBANYAK)']);
            fputcsv($file, ['No', 'Nama Barang', 'Kode Barang', 'Stok Akhir']);
            foreach (($dataLaporan['stok']['fast_moving'] ?? []) as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item['nama_barang'] ?? '',
                    $item['kode_barang'] ?? '',
                    $item['stok_akhir'] ?? 0,
                ]);
            }
            fputcsv($file, ['']); // Baris kosong

            fputcsv($file, ['BARANG SLOW MOVING (STOK TERSISA)']);
            fputcsv($file, ['No', 'Nama Barang', 'Kode Barang', 'Stok Akhir']);
            foreach (($dataLaporan['stok']['slow_moving'] ?? []) as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item['nama_barang'] ?? '',
                    $item['kode_barang'] ?? '',
                    $item['stok_akhir'] ?? 0,
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }


    /**
     * Export data laporan penjualan ke PDF.
     */
    public function exportPdf(Request $request)
    {
        // 1. Ambil data laporan seperti di method filter
        $tipePeriode = $request->input('tipe_periode', 'bulanan');
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        
        [$start, $end] = $this->determineDateRange($tipePeriode, $start, $end);

        $dataLaporan = $this->generateLaporan($tipePeriode, $start, $end);

        // 2. Load View PDF (Asumsi Anda menggunakan pustaka seperti DomPDF/Barryvdh\DomPDF)
        
        // $pdf = \PDF::loadView('laporan.pdf_view', [
        //       'dataLaporan' => $dataLaporan
        // ]);
        // return $pdf->download('laporan_penjualan_' . $start . '_sd_' . $end . '.pdf');
        
        return redirect()->back()->with('warning', 'Fungsionalitas Export PDF belum diaktifkan. Harap instal pustaka PDF dan buat view laporan khusus PDF.');
    }
    
    /**
     * Export data Top 10 Pelanggan ke CSV (sebagai placeholder Excel).
     */
    public function exportTopPelangganExcel(Request $request)
    {
        // ... (Kode untuk export Top Pelanggan yang lama tetap ada untuk tombol di modal)
        $tipePeriode = $request->input('tipe_periode', 'bulanan');
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        
        [$start, $end] = $this->determineDateRange($tipePeriode, $start, $end);
        
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();
        
        // 2. Query data Top 10 Pelanggan
        $topPelanggan = BarangKeluar::whereBetween('tanggal', [$startDate, $endDate])
            ->whereNotNull('pelanggan_id')
            ->select(
                'pelanggan_id',
                DB::raw('COUNT(id) AS total_nota'),
                DB::raw('SUM(total_harga) AS total_nilai_pembelian')
            )
            ->groupBy('pelanggan_id')
            ->orderByDesc('total_nota')
            ->limit(10)
            ->with('pelanggan') 
            ->get();

        // 3. Persiapan header CSV/Excel
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="top_10_pelanggan_' . $start . '_sd_' . $end . '.csv"',
        ];

        // 4. Proses streaming data
        $callback = function() use ($topPelanggan) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Nama Pelanggan', 'Total Nota', 'Total Pembelian (Rp)']); // Header

            foreach ($topPelanggan as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->pelanggan->nama_pelanggan ?? 'Tidak Tercatat',
                    $item->total_nota,
                    $item->total_nilai_pembelian, // Biarkan angka tanpa format agar mudah diolah di Excel
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}