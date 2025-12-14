<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\Barang;
use Phpml\Clustering\KMeans;
use Illuminate\Support\Carbon; 



class KMeansController extends Controller
{
    /**
     * Menampilkan halaman K-Means (form input) dan menentukan batas maksimal K.
     */
    public function index()
    {
        // Hitung jumlah total kategori untuk batasan Maksimal K pada View
        $totalKategori = Kategori::count(); 

        return view('k_means.index', [
            'results' => null,
            'inputs' => [],
            'maxK' => $totalKategori > 1 ? $totalKategori : 1, 
        ]);
    }

    /**
     * Jalankan proses K-Means Clustering dengan Normalisasi Min-Max
     */
    public function run(Request $request)
    {
        // 1️⃣ Validasi input dasar
        $request->validate([
            'jumlah_cluster' => 'required|numeric|min:2', 
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);
        
        $k_requested = (int) $request->input('jumlah_cluster');
        $tanggalMulai = Carbon::parse($request->input('tanggal_mulai'))->startOfDay();
        $tanggalSelesai = Carbon::parse($request->input('tanggal_selesai'))->endOfDay();
        
        // 2️⃣ Ambil data penjualan dari Model
        $kategoriData = Kategori::getSalesPerformance($tanggalMulai, $tanggalSelesai);
        
        // 3️⃣ Persiapkan data mentah dan kumpulkan nilai Min/Max
        $samplesMentah = []; 
        $dataUntukLabel = [];
        $kuantitasValues = [];
        $omsetValues = [];

        foreach ($kategoriData as $kategori) {
            $kuantitas = (int) ($kategori->total_kuantitas ?? 0);
            $omset = (int) ($kategori->total_omset ?? 0);

            // Hanya ambil kategori yang memiliki penjualan
            if ($kuantitas > 0) {
                $samplesMentah[$kategori->id] = [$kuantitas, $omset];
                $dataUntukLabel[$kategori->id] = $kategori;
                
                $kuantitasValues[] = $kuantitas;
                $omsetValues[] = $omset;
            }
        }
        
        // 6️⃣ Cek data cukup untuk clustering dan VALIDASI K
        $jumlahData = count($samplesMentah); 
        $k = $k_requested;

        if ($jumlahData < 2) {
            return back()->withInput()->with('error', 'Jumlah Kategori yang memiliki data penjualan (' . $jumlahData . ') terlalu sedikit. Clustering memerlukan minimal 2 titik data.');
        }

        // PERBAIKAN LOGIKA UTAMA: Paksakan K menjadi jumlah data yang ada jika K yang diminta terlalu besar
        if ($k_requested > $jumlahData) {
            $k = $jumlahData; 
            // Tambahkan warning di session (warning akan ditampilkan di view)
            session()->flash('warning', 'Jumlah Cluster (K) disesuaikan dari ' . $k_requested . ' menjadi ' . $k . ' karena hanya ada ' . $k . ' Kategori yang memiliki data penjualan pada periode tersebut.');
        }


        // 4️⃣ HITUNG NILAI MIN & MAX (Tetap sama)
        $minKuantitas = empty($kuantitasValues) ? 0 : min($kuantitasValues);
        $maxKuantitas = empty($kuantitasValues) ? 0 : max($kuantitasValues);
        $minOmset = empty($omsetValues) ? 0 : min($omsetValues);
        $maxOmset = empty($omsetValues) ? 0 : max($omsetValues);
        
        $rangeKuantitas = $maxKuantitas - $minKuantitas;
        $rangeOmset = $maxOmset - $minOmset;

        $samplesNormalized = [];
        $samplesToMap = []; 

        // 5️⃣ LAKUKAN NORMALISASI MIN-MAX (Tetap sama)
        foreach ($samplesMentah as $kategoriId => $sample) {
            $kuantitas = $sample[0];
            $omset = $sample[1];
            
            $normKuantitas = $rangeKuantitas > 0 ? ($kuantitas - $minKuantitas) / $rangeKuantitas : 0;
            $normOmset = $rangeOmset > 0 ? ($omset - $minOmset) / $rangeOmset : 0;
            
            $samplesNormalized[$kategoriId] = [$normKuantitas, $normOmset];
            $samplesToMap[] = [$normKuantitas, $normOmset];
        }

        // 7️⃣ Jalankan algoritma K-Means dengan K yang sudah disesuaikan
        $kmeans = new KMeans($k); 
        $clusters = $kmeans->cluster($samplesToMap); 
        
        // 8️⃣ Hubungkan hasil cluster ke kategori (Menggunakan data MENTAH untuk laporan)
        $hasilAnggota = [];
        $tempSamplesNormalized = $samplesNormalized; 
        $clusterOmsetTotals = [];
        
        $clusterIdsWithMembers = []; 
        $chartDataSets = [];

        foreach ($clusters as $clusterId => $clusterSamples) {
            
            // HANYA PROSES CLUSTER YANG MEMILIKI ANGGOTA
            if (empty($clusterSamples)) {
                continue; 
            }
            
            $clusterIdsWithMembers[] = $clusterId;
            // AMBIL WARNA BERDASARKAN ID ASLI SEBELUM PENGURUTAN
            $clusterColor = $this->getClusterColor($clusterId); 

            $dataSet = [
                'label' => 'Cluster ' . ($clusterId + 1), 
                'data' => [],
                'backgroundColor' => $clusterColor, 
                'pointRadius' => 6, 
            ];
            
            $clusterOmsetTotals[$clusterId] = [
                'omset' => 0,
                'kuantitas' => 0,
            ];

             foreach ($clusterSamples as $sampleNormalized) {
                
                foreach ($tempSamplesNormalized as $kategoriId => $normSample) {
                    
                    if ($normSample[0] === $sampleNormalized[0] && $normSample[1] === $sampleNormalized[1]) {
                        
                        $kategori = $dataUntukLabel[$kategoriId];
                        $mentahSample = $samplesMentah[$kategoriId];

                        // LOGIC: Ambil Top 5 Barang Terlaris di Kategori ini
                        $topBarang = $this->getTopBarang($kategoriId, $tanggalMulai, $tanggalSelesai);
                                     
                        $hasilAnggota[] = [
                            'id_kategori' => $kategoriId,
                            'nama_kategori' => $kategori->nama_kategori ?? $kategori->nama ?? '-',
                            'kuantitas' => $mentahSample[0], 
                            'omset' => $mentahSample[1], 
                            'cluster' => $clusterId,
                            'top_barang' => $topBarang, 
                        ];
                        
                        $dataSet['data'][] = [
                            'x' => $mentahSample[0], 
                            'y' => $mentahSample[1], 
                            'kategori' => $kategori->nama_kategori,
                            'clusterId' => $clusterId
                        ];
                        
                        $clusterOmsetTotals[$clusterId]['omset'] += $mentahSample[1];
                        $clusterOmsetTotals[$clusterId]['kuantitas'] += $mentahSample[0];
                        
                        unset($tempSamplesNormalized[$kategoriId]);
                        break; 
                    }
                }
            }
            $chartDataSets[$clusterId] = $dataSet; 
        }

        // 9️⃣ & 🔟 Hitung Centroid dari Data MENTAH dan Urutkan
        $centroidsMentah = [];
        $clusterCounts = [];
        
        foreach ($hasilAnggota as $anggota) {
            $c = $anggota['cluster'];
            $centroidsMentah[$c]['totalKuantitas'] = ($centroidsMentah[$c]['totalKuantitas'] ?? 0) + $anggota['kuantitas'];
            $centroidsMentah[$c]['totalOmset'] = ($centroidsMentah[$c]['totalOmset'] ?? 0) + $anggota['omset'];
            $clusterCounts[$c] = ($clusterCounts[$c] ?? 0) + 1;
        }

        $hasilCentroids = [];
        
        // Simpan data Centroid, termasuk warna asli
        foreach ($clusterIdsWithMembers as $clusterId) {
            $totals = $centroidsMentah[$clusterId] ?? ['totalKuantitas' => 0, 'totalOmset' => 0];
            $count = $clusterCounts[$clusterId] ?? 0;
            
            // Simpan warna asli (berdasarkan ID asli) sebelum pengurutan
            $hasilCentroids[] = [
                'cluster' => $clusterId,
                'avg_kuantitas' => round($count > 0 ? $totals['totalKuantitas'] / $count : 0, 2),
                'avg_omset' => round($count > 0 ? $totals['totalOmset'] / $count : 0, 2),
                'color' => $this->getClusterColor($clusterId), // SIMPAN WARNA DI CENTROID
            ];
        }
        
        // URUTKAN CENTROID BERDASARKAN RATA-RATA KUANTITAS
        usort($hasilCentroids, fn($a, $b) => $b['avg_kuantitas'] <=> $a['avg_kuantitas']);


        // 11️⃣ Beri label otomatis dan update Chart DataSets
        $labels = ['Kategori Keras (Laris)', 'Kategori Biasa', 'Kategori Rendah', 'Kategori Mati', 'Cluster 5', 'Cluster 6', 'Cluster 7', 'Cluster 8', 'Cluster 9', 'Cluster 10'];
        $actual_k = count($clusterIdsWithMembers);
        $finalLabels = array_slice($labels, 0, $actual_k); 
        $clusterLabelMap = [];
        
        $finalChartDataSets = [];
        $finalPieChartData = [];

        foreach ($hasilCentroids as $newIndex => &$centroid) {
            $originalClusterId = $centroid['cluster'];
            
            // Tetapkan label baru yang logis
            $newLabel = $finalLabels[$newIndex] ?? 'Cluster ' . ($newIndex + 1);
            $centroid['label'] = $newLabel;
            
            $clusterLabelMap[$originalClusterId] = $newLabel;
            
            // 1. Update label pada chartDataSet yang sudah ada (Scatter Chart)
            if (isset($chartDataSets[$originalClusterId])) {
                $chartDataSets[$originalClusterId]['label'] = $newLabel;
                // MENAMBAHKAN TITIK CENTROID KE DATASET SCATTER CHART
                $totals = $centroidsMentah[$originalClusterId] ?? ['totalKuantitas' => 0, 'totalOmset' => 0];
                $count = $clusterCounts[$originalClusterId] ?? 0;
                $chartDataSets[$originalClusterId]['data'][] = [
                    'x' => round($count > 0 ? $totals['totalKuantitas'] / $count : 0, 2),
                    'y' => round($count > 0 ? $totals['totalOmset'] / $count : 0, 2),
                    'kategori' => 'Centroid',
                    'clusterId' => $originalClusterId,
                    'pointStyle' => 'crossRot', 
                    'radius' => 8,
                    'borderWidth' => 2,
                    'backgroundColor' => '#000000',
                ];
                $finalChartDataSets[] = $chartDataSets[$originalClusterId];
            }

            // 2. Isi Pie Chart Data dengan label baru dan warna yang disimpan di Centroid (SINKRON)
            $finalPieChartData[] = [
                'label' => $newLabel, 
                'omset' => $clusterOmsetTotals[$originalClusterId]['omset'],
                'color' => $centroid['color'], 
            ];
        }

        // Perbarui label pada hasil anggota
        foreach ($hasilAnggota as &$anggota) {
            $anggota['label'] = $clusterLabelMap[$anggota['cluster']] ?? 'Cluster ?';
        }

        // 12️⃣ Kirim hasil ke view
        return view('k_means.index', [
            'results' => [
                'anggota' => $hasilAnggota,
                'centroids' => $hasilCentroids,
                'chartData' => $finalChartDataSets, // Mengirim yang sudah difilter
                'pieChartData' => $finalPieChartData, // Mengirim yang sudah difilter
            ],
            'inputs' => $request->all(),
            'maxK' => $jumlahData, 
        ]);
    }
    
    // Helper function untuk memberikan warna berbeda per cluster
    private function getClusterColor($clusterId)
    {
        $colors = [
            'rgba(255, 99, 132, 0.8)', // 0: Merah (Keras)
            'rgba(54, 162, 235, 0.8)', // 1: Biru (Biasa)
            'rgba(255, 206, 86, 0.8)', // 2: Kuning (Rendah)
            'rgba(75, 192, 192, 0.8)', // 3: Hijau (Mati)
            'rgba(153, 102, 255, 0.8)', // 4: Ungu
            'rgba(255, 159, 64, 0.8)', // 5: Oranye
            'rgba(199, 199, 199, 0.8)', // 6: Abu-abu
            'rgba(100, 150, 200, 0.8)', // 7: Biru Muda
            'rgba(200, 100, 150, 0.8)', // 8: Merah Muda
            'rgba(150, 200, 100, 0.8)', // 9: Hijau Muda
        ];
        return $colors[$clusterId % count($colors)];
    }
    
    // Helper function untuk top barang (untuk meringkas code di run())
    private function getTopBarang($kategoriId, $tanggalMulai, $tanggalSelesai)
    {
        return Barang::select('nama_barang')
            ->withSum(['detailBarangKeluars as total_jual' => function ($query) use ($tanggalMulai, $tanggalSelesai) {
                $query->whereHas('barangKeluar', function($q) use ($tanggalMulai, $tanggalSelesai) {
                    $q->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]); 
                });
            }], 'jumlah')
            ->where('id_kategori', $kategoriId)
            ->orderByDesc('total_jual')
            ->limit(5)
            ->get();
    }
}