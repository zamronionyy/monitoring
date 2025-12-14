<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\BarangKeluar;
use App\Models\DetailBarangKeluar;
use App\Models\Barang;

class AprioriController extends Controller
{
    /**
     * Menampilkan halaman Apriori (form input)
     */
    public function index()
    {
        return view('apriori.index', [
            'results' => null,
            'inputs' => []
        ]);
    }

    /**
     * Jalankan proses Apriori (Association Rule Mining)
     */
    public function run(Request $request)
    {
        // 1️⃣ Validasi Input Form
        $request->validate([
            'min_support' => 'required|numeric|min:1|max:100',
            'min_confidence' => 'required|numeric|min:1|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $minSupport = (float) $request->input('min_support') / 100;
        $minConfidence = (float) $request->input('min_confidence') / 100;
        $tanggalMulai = Carbon::parse($request->input('tanggal_mulai'))->startOfDay();
        $tanggalSelesai = Carbon::parse($request->input('tanggal_selesai'))->endOfDay();

        // 2️⃣ Ambil Data Transaksi (TID) dan Total Transaksi (N)
        $barangKeluars = BarangKeluar::whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]) // FIX: Asumsi kolom tanggal adalah 'tanggal'
                                     ->get(['id']);
                                     
        $N = $barangKeluars->count(); // Total Transaksi
        
        if ($N === 0) {
            return back()->withInput()->with('error', 'Tidak ada data transaksi pada rentang tanggal yang dipilih.');
        }

        $transaksiIds = $barangKeluars->pluck('id')->toArray();

        // Ambil semua item yang dibeli dalam transaksi tersebut
        $details = DetailBarangKeluar::whereIn('barang_keluar_id', $transaksiIds)
                                     ->with('barang:id,nama_barang') 
                                     ->get(['barang_keluar_id', 'barang_id']);

        // Kelompokkan item berdasarkan ID Transaksi (TID)
        $transactions = $details->groupBy('barang_keluar_id')->map(function ($items) {
            return $items->pluck('barang_id')->unique()->toArray();
        })->toArray();
        
        // 3️⃣ Hitung Frekuensi dan Candidate Sets (L1 dan L2)
        
        // Itemset 1 (C1 & L1)
        $itemCounts = [];
        $barangNames = []; 
        
        // MODIFIKASI: Ambil ID, Nama, dan KODE Barang
        $barangList = Barang::whereIn('id', $details->pluck('barang_id')->unique())->get(['id', 'nama_barang', 'kode_barang']);
        foreach ($barangList as $item) {
            // MODIFIKASI: Simpan nama dan kode barang dalam array
            $barangNames[$item->id] = [
                'nama' => $item->nama_barang,
                'kode' => $item->kode_barang, 
            ];
        }

        foreach ($transactions as $transaction) {
            foreach ($transaction as $itemId) {
                $itemCounts[$itemId] = ($itemCounts[$itemId] ?? 0) + 1;
            }
        }

        $L1 = [];
        foreach ($itemCounts as $itemId => $count) {
            if ($count / $N >= $minSupport) {
                $L1[$itemId] = $count;
            }
        }
        
        if (empty($L1) || count($L1) < 2) {
            return back()->withInput()->with('error', 'Data itemset yang memenuhi Minimum Support tidak cukup untuk membentuk aturan.');
        }
        
        // Itemset 2 (C2 & L2)
        $itemset1Keys = array_keys($L1);
        $L2 = [];
        $len = count($itemset1Keys);
        for ($i = 0; $i < $len; $i++) {
            for ($j = $i + 1; $j < $len; $j++) {
                $itemA = $itemset1Keys[$i];
                $itemB = $itemset1Keys[$j];
                
                $countAB = 0;
                foreach ($transactions as $transaction) {
                    if (in_array($itemA, $transaction) && in_array($itemB, $transaction)) {
                        $countAB++;
                    }
                }
                
                if ($countAB / $N >= $minSupport) {
                    $L2[implode(',', [$itemA, $itemB])] = $countAB;
                }
            }
        }

        // 4️⃣ Generate Association Rules dari L2
        $rules = [];
        
        foreach ($L2 as $itemsetString => $supportABCount) {
            $items = array_map('intval', explode(',', $itemsetString));
            $itemA = $items[0];
            $itemB = $items[1];
            
            // Aturan 1: A -> B
            $supportA = $L1[$itemA] ?? 0;
            if ($supportA > 0) {
                $confidence = $supportABCount / $supportA;
                if ($confidence >= $minConfidence) {
                    $supportAB = $supportABCount / $N;
                    $supportB = $L1[$itemB] / $N;
                    $lift = $supportAB / (($supportA / $N) * $supportB);
                    
                    $rules[] = [
                        // MODIFIKASI: Tambahkan Nama dan Kode
                        'antecedent_name' => $barangNames[$itemA]['nama'],
                        'antecedent_code' => $barangNames[$itemA]['kode'],
                        'consequent_name' => $barangNames[$itemB]['nama'],
                        'consequent_code' => $barangNames[$itemB]['kode'],
                        'support' => round($supportAB * 100, 2), 
                        'confidence' => round($confidence * 100, 2), 
                        'lift' => round($lift, 2),
                    ];
                }
            }

            // Aturan 2: B -> A
            $supportB = $L1[$itemB] ?? 0;
            if ($supportB > 0) {
                $confidence = $supportABCount / $supportB;
                if ($confidence >= $minConfidence) {
                    $supportAB = $supportABCount / $N;
                    $supportA = $L1[$itemA] / $N;
                    $lift = $supportAB / (($supportB / $N) * $supportA);
                    
                    $rules[] = [
                        // MODIFIKASI: Tambahkan Nama dan Kode
                        'antecedent_name' => $barangNames[$itemB]['nama'],
                        'antecedent_code' => $barangNames[$itemB]['kode'],
                        'consequent_name' => $barangNames[$itemA]['nama'],
                        'consequent_code' => $barangNames[$itemA]['kode'],
                        'support' => round($supportAB * 100, 2), 
                        'confidence' => round($confidence * 100, 2), 
                        'lift' => round($lift, 2),
                    ];
                }
            }
        }

        // 5️⃣ Urutkan hasil berdasarkan Confidence tertinggi
        usort($rules, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        // 6️⃣ Kirim hasil ke tampilan
        return view('apriori.index', [
            'results' => [
                'rules' => $rules,
                'total_transaksi' => $N,
            ],
            'inputs' => $request->all()
        ]);
    }
}