<?php

namespace App\Http\Controllers;

use App\Models\BarangKeluar;
use App\Models\DetailBarangKeluar;
use App\Models\Barang;
use App\Models\Pelanggan;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class BarangKeluarController extends Controller
{
    /**
     * Menampilkan riwayat nota penjualan
     */
    public function index(Request $request) 
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = BarangKeluar::with(['pelanggan', 'user']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id_transaksi', 'like', '%' . $search . '%')
                  ->orWhereHas('pelanggan', function($pelangganQuery) use ($search) {
                      $pelangganQuery->where('nama_pelanggan', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($startDate && $endDate) {
            $query->whereDate('tanggal', '>=', $startDate)
                  ->whereDate('tanggal', '<=', $endDate);
        }

        $barangKeluars = $query->orderBy('tanggal', 'desc')
                               ->orderBy('id', 'desc')
                               ->paginate(10)
                               ->appends($request->all());

        return view('barangkeluar.index', [
            'barangKeluars' => $barangKeluars,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search
        ]);
    }

   public function create()
{
    $pelanggans = Pelanggan::orderBy('nama_pelanggan', 'asc')->get();
    
    // Ambil barang beserta kalkulasi stok akhir secara real-time
    $barangs = Barang::withSum('stokBarangs', 'stok')
                     ->withSum('detailBarangKeluars', 'jumlah')
                     ->orderBy('nama_barang', 'asc')
                     ->get()
                     ->map(function($barang) {
                         // Hitung sisa stok: Total Masuk - Total Keluar
                         $barang->stok_akhir = ($barang->stok_barangs_sum_stok ?? 0) - ($barang->detail_barang_keluars_sum_jumlah ?? 0);
                         return $barang;
                     });

    return view('barangkeluar.create', compact('pelanggans', 'barangs'));
}

    /**
     * Store dengan Validasi Tanggal, Biaya Kirim & DP
     */
    public function store(Request $request)
    {
        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            // VALIDASI TANGGAL: Tidak boleh lebih dari hari ini
            'tanggal' => 'required|date|before_or_equal:today', 
            'id_transaksi' => 'required|string|unique:barang_keluars,id_transaksi',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.jumlah' => 'required|numeric|min:1',
            'biaya_kirim' => 'nullable|numeric|min:0',
            'uang_muka' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction(); 

            $subTotalBarang = 0;
            $itemsToSave = [];

            // 1. Hitung Total Barang & Cek Stok
            foreach ($request->items as $item) {
                $barang = Barang::withSum('stokBarangs', 'stok')
                                 ->withSum('detailBarangKeluars', 'jumlah')
                                 ->find($item['barang_id']);
                
                $stokAkhir = ((int)$barang->stok_barangs_sum_stok) - ((int)$barang->detail_barang_keluars_sum_jumlah);

                if ($stokAkhir < $item['jumlah']) {
                    throw new \Exception("Stok " . $barang->nama_barang . " kurang! (Sisa: " . $stokAkhir . ")");
                }

                $hargaSatuan = $barang->harga; 
                $subTotal = $hargaSatuan * $item['jumlah'];
                $subTotalBarang += $subTotal;

                $key = $item['barang_id'];
                if (isset($itemsToSave[$key])) {
                     $itemsToSave[$key]['jumlah'] += $item['jumlah'];
                     $itemsToSave[$key]['total_harga'] += $subTotal;
                } else {
                     $itemsToSave[$key] = [
                        'barang_id' => $item['barang_id'],
                        'jumlah' => $item['jumlah'],
                        'harga_satuan' => $hargaSatuan,
                        'total_harga' => $subTotal,
                    ];
                }
            }

            // 2. Hitung Grand Total (Barang + Ongkir)
            $biayaKirim = $request->input('biaya_kirim', 0);
            $grandTotal = $subTotalBarang + $biayaKirim;

            // 3. Cek DP
            $uangMuka = $request->input('uang_muka', 0);
            if ($uangMuka > $grandTotal) {
                throw new \Exception("Uang muka tidak boleh melebihi Total Tagihan.");
            }

            $barangKeluar = BarangKeluar::create([
                'id_transaksi' => $request->id_transaksi,
                'pelanggan_id' => $request->pelanggan_id,
                'user_id' => Auth::id(), 
                'tanggal' => $request->tanggal,
                'total_harga' => $grandTotal, 
                'biaya_kirim' => $biayaKirim, 
                'uang_muka' => $uangMuka,
            ]);

            $barangKeluar->detailBarangKeluars()->createMany($itemsToSave);

            DB::commit(); 
            return redirect()->route('barangkeluar.index')->with('success', 'Transaksi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack(); 
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $barangKeluar = BarangKeluar::findOrFail($id);
        $pelanggans = Pelanggan::all();
        $barangs = Barang::all();
        $detailBarangKeluars = DetailBarangKeluar::where('barang_keluar_id', $barangKeluar->id)->get();
        
        $itemsForAlpine = $detailBarangKeluars->map(function ($item) {
            return [
                'barang_id'     => $item->barang_id,
                'jumlah'        => $item->jumlah,
                'harga_satuan'  => $item->harga_satuan,
                'subtotal'      => $item->total_harga,
            ];
        });

        return view('barangkeluar.edit', compact('barangKeluar','pelanggans','barangs','detailBarangKeluars','itemsForAlpine'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            // VALIDASI TANGGAL: Tidak boleh lebih dari hari ini
            'tanggal' => 'required|date|before_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'biaya_kirim' => 'nullable|numeric|min:0',
            'uang_muka' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $barangKeluar = BarangKeluar::with('detailBarangKeluars')->findOrFail($id);
            $subTotalBarang = 0;
            $itemsToSave = [];

            foreach ($request->items as $index => $it) {
                $barangId = $it['barang_id'];
                $jumlah = (int) $it['jumlah'];
                $barang = Barang::withSum('stokBarangs', 'stok')->withSum('detailBarangKeluars', 'jumlah')->findOrFail($barangId);

                $totalMasuk = (int) $barang->stok_barangs_sum_stok;
                $totalKeluarSaatIni = (int) $barang->detail_barang_keluars_sum_jumlah;
                $detailLama = $barangKeluar->detailBarangKeluars()->where('barang_id', $barangId)->first();
                $jumlahLama = $detailLama ? (int) $detailLama->jumlah : 0;
                
                $stokTersedia = ($totalMasuk - ($totalKeluarSaatIni - $jumlahLama));

                if ($stokTersedia < $jumlah) {
                    throw new \Exception("Stok {$barang->nama_barang} kurang! (Sisa: {$stokTersedia})");
                }

                $hargaSatuan = $barang->harga;
                $subTotal = $hargaSatuan * $jumlah;
                $subTotalBarang += $subTotal;

                $key = $barangId;
                if (isset($itemsToSave[$key])) {
                     $itemsToSave[$key]['jumlah'] += $jumlah;
                     $itemsToSave[$key]['total_harga'] += $subTotal;
                } else {
                     $itemsToSave[$key] = [
                        'barang_id' => $barangId,
                        'jumlah' => $jumlah,
                        'harga_satuan' => $hargaSatuan,
                        'total_harga' => $subTotal,
                    ];
                }
            }

            $biayaKirim = $request->input('biaya_kirim', 0);
            $grandTotal = $subTotalBarang + $biayaKirim;

            $uangMuka = $request->input('uang_muka', 0);
            if ($uangMuka > $grandTotal) {
                 throw new \Exception("Uang muka melebihi Total Tagihan.");
            }

            $barangKeluar->update([
                'pelanggan_id' => $request->pelanggan_id,
                'tanggal' => $request->tanggal,
                'user_id' => Auth::id(),
                'total_harga' => $grandTotal,
                'biaya_kirim' => $biayaKirim,
                'uang_muka' => $uangMuka,
            ]);

            $barangKeluar->detailBarangKeluars()->delete();
            $barangKeluar->detailBarangKeluars()->createMany($itemsToSave);

            DB::commit();
            return redirect()->route('barangkeluar.index')->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $barangKeluar = BarangKeluar::findOrFail($id);
            $barangKeluar->detailBarangKeluars()->delete();
            $barangKeluar->delete();
            DB::commit();
            return redirect()->route('barangkeluar.index')->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('barangkeluar.index')->with('error', 'Gagal menghapus transaksi.');
        }
    }

    public function detail($id)
    {
        $transaksi = BarangKeluar::with(['pelanggan', 'user', 'detailBarangKeluars.barang'])->findOrFail($id);
        return view('barangkeluar.detail', compact('transaksi'));
    }

    public function printDetail($id_transaksi)
    {
        $barangKeluar = BarangKeluar::with(['pelanggan', 'user', 'detailBarangKeluars.barang'])->findOrFail($id_transaksi);
        return view('barangkeluar.print', compact('barangKeluar'));
    }

    public function downloadPdf($id)
    {
        $barangKeluar = BarangKeluar::with(['pelanggan', 'user', 'detailBarangKeluars.barang'])->findOrFail($id);

        $pdf = Pdf::loadView('barangkeluar.pdf', compact('barangKeluar'));

        // UBAH JADI A4 PORTRAIT (Standar Dokumen)
        $pdf->setPaper('a4', 'portrait'); 

        return $pdf->download('Nota-'.$barangKeluar->id_transaksi.'.pdf');
    }
}
