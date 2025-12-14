<?php

namespace App\Http\Controllers;

use App\Models\StokBarang;
use App\Models\Barang; 
use App\Models\Kategori; 
use Illuminate\Http\Request; 
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StokBarangImport; 
use App\Imports\StokBarangTemplateImport; 
use App\Exports\StokBarangExport; 
use Illuminate\Support\Facades\DB; 

class StokBarangController extends Controller
{
    /**
     * Menampilkan RIWAYAT stok masuk.
     */
    public function index(Request $request) 
    {
        $search = $request->input('search');
        $kategoriId = $request->input('kategori_id'); 
        
        $query = StokBarang::with(['barang', 'barang.kategori']);

        if ($search) {
            $query->whereHas('barang', function ($q) use ($search) {
                $q->where('kode_barang', 'like', '%' . $search . '%')
                  ->orWhere('nama_barang', 'like', '%' . $search . '%')
                  ->orWhereHas('kategori', function($kategoriQuery) use ($search) {
                      $kategoriQuery->where('nama_kategori', 'like', '%' . $search . '%');
                  });
            });
        }
        
        if ($kategoriId) {
            $query->whereHas('barang', function ($q) use ($kategoriId) {
                $q->where('id_kategori', $kategoriId);
            });
        }
        
        $stokBarangs = $query->orderBy('tanggal_masuk', 'desc')
                             ->orderBy('id', 'desc')
                             ->paginate(10)
                             ->appends($request->query());

        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();

        return view('stokbarang.index', [
            'stokBarangs' => $stokBarangs,
            'kategoris' => $kategoris, 
            'selectedKategoriId' => $kategoriId, 
        ]);
    }

    public function create()
    {
        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        return view('stokbarang.create', ['barangs' => $barangs]);
    }

    /**
     * Store dengan Validasi Tanggal (Tidak Boleh Lebih dari Hari Ini)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_barang' => 'required|exists:barangs,id',
            'stok' => 'required|numeric|min:1',
            // VALIDASI: Tanggal wajib diisi & tidak boleh lebih dari hari ini
            'tanggal_masuk' => 'required|date|before_or_equal:today', 
        ]);

        StokBarang::create([
            'id_barang' => $request->id_barang,
            'stok' => $request->stok,
            'tanggal_masuk' => $request->tanggal_masuk,
        ]);

        return redirect()->route('stokbarang.index')->with('success', 'Stok berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $stok = StokBarang::with('barang.kategori')->findOrFail($id);
        return view('stokbarang.edit', ['stok' => $stok]);
    }

    /**
     * Update dengan Validasi Tanggal
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'stok' => 'required|numeric|min:0',
            // VALIDASI: Tanggal wajib diisi & tidak boleh lebih dari hari ini
            'tanggal_masuk' => 'required|date|before_or_equal:today',
        ]);

        $stok = StokBarang::findOrFail($id);
        $stok->update([
            'stok' => $request->stok,
            'tanggal_masuk' => $request->tanggal_masuk,
        ]);

        return redirect()->route('stokbarang.index')->with('success', 'Catatan stok berhasil diupdate.');
    }

    public function destroy($id)
    {
        try {
            $stok = StokBarang::findOrFail($id);
            $stok->delete();
            return redirect()->route('stokbarang.index')->with('success', 'Catatan stok berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('stokbarang.index')->with('error', 'Gagal menghapus data.');
        }
    }
    
    public function deleteMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:stok_barangs,id', 
        ]);

        $ids = $request->ids;

        if (empty($ids)) {
            return redirect()->route('stokbarang.index')->with('error', 'Tidak ada catatan stok yang dipilih untuk dihapus.');
        }

        try {
            DB::beginTransaction();
            $deletedCount = StokBarang::destroy($ids);
            DB::commit();

            if ($deletedCount > 0) {
                return redirect()->route('stokbarang.index')->with('success', "{$deletedCount} catatan stok masuk berhasil dihapus.");
            } else {
                return redirect()->route('stokbarang.index')->with('error', 'Tidak ada catatan stok yang berhasil dihapus.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('stokbarang.index')->with('error', 'Gagal menghapus beberapa catatan stok: ' . $e->getMessage());
        }
    }
    
    public function showImportForm()
    {
        return view('stokbarang.import'); 
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_stok' => 'required|mimes:xlsx,xls,csv' 
        ]);

        try {
            Excel::import(new StokBarangImport, $request->file('file_stok'));
            return redirect()->route('stokbarang.index')->with('success', 'Data stok masuk berhasil di-import.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             return redirect()->back()->withInput()->with('error', 'Gagal, ada error validasi di file Excel. Silakan periksa formatnya.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal meng-import data. Pesan error: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new StokBarangTemplateImport, 'template_stok_masuk.xlsx');
    }

    public function exportExcel()
    {
        return Excel::download(new StokBarangExport, 'riwayat_stok_masuk.xlsx');
    }
}