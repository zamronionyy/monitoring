<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori; // Import Model Kategori
use Illuminate\Http\Request;
use App\Imports\BarangImport; 
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\BarangTemplateExport;
use App\Exports\BarangExport;
use Illuminate\Support\Facades\DB; 

class BarangController extends Controller
{
    /**
     * Menampilkan daftar MASTER BARANG (Katalog) dengan filter kategori.
     */
    public function index(Request $request) 
    {
        // Ambil kata kunci pencarian dari URL
        $search = $request->input('search');
        // BARU: Ambil parameter filter kategori dari URL
        $kategoriId = $request->input('kategori_id');

        // Mulai query
        $query = Barang::with('kategori')
                        ->withSum('stokBarangs', 'stok')
                        ->withSum('detailBarangKeluars', 'jumlah');
        
        // === LOGIKA FILTER KATEGORI (BARU) ===
        if ($kategoriId) {
            $query->where('id_kategori', $kategoriId);
        }
        // === BATAS LOGIKA FILTER KATEGORI ===

        // === LOGIKA PENCARIAN ===
        if ($search) {
            $query->where(function($q) use ($search) {
                // Cari berdasarkan Kode Barang
                $q->where('kode_barang', 'like', '%' . $search . '%')
                  // ATAU Cari berdasarkan Nama Barang
                  ->orWhere('nama_barang', 'like', '%' . $search . '%')
                  // ATAU Cari berdasarkan Nama Kategori (via relasi)
                  ->orWhereHas('kategori', function($kategoriQuery) use ($search) {
                      $kategoriQuery->where('nama_kategori', 'like', '%' . $search . '%');
                  });
            });
        }
        // === BATAS LOGIKA PENCARIAN ===

        $barangs = $query->orderBy('kode_barang', 'asc')
                         ->paginate(10)
                         ->appends($request->query());
                             
        // Ambil semua kategori untuk dropdown
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();

        return view('barang.index', [
            'barangs' => $barangs,
            'kategoris' => $kategoris, // Kirim data kategori
            'selectedKategoriId' => $kategoriId, // Kirim ID kategori yang sedang aktif
        ]);
    }

    /**
     * Menampilkan form Admin untuk mendaftarkan barang baru.
     */
    public function create()
    {
        $kategoris = Kategori::all(); 
        return view('barang.create', ['kategoris' => $kategoris]);
    }

    /**
     * Menyimpan barang baru (HANYA DATA MASTER) ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_kategori' => 'required|exists:kategoris,id',
            'kode_barang' => 'required|string|max:255|unique:barangs',
            'nama_barang' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
        ]);

        Barang::create($request->all());
        return redirect()->route('barang.index')->with('success', 'Barang baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit master barang.
     */
    public function edit(Barang $barang)
    {
        $kategoris = Kategori::all();
        
        return view('barang.edit', [
            'barang' => $barang,
            'kategoris' => $kategoris
        ]);
    }

    /**
     * Menyimpan perubahan data master barang ke database.
     */
    public function update(Request $request, Barang $barang)
    {
        $request->validate([
            'id_kategori' => 'required|exists:kategoris,id',
            'kode_barang' => 'required|string|max:255|unique:barangs,kode_barang,' . $barang->id,
            'nama_barang' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
        ]);

        $barang->update($request->all());
        return redirect()->route('barang.index')->with('success', 'Barang berhasil diupdate.');
    }

    // === METHOD BARU UNTUK HAPUS BANYAK (FORCE DELETE MULTIPLE) ===
    public function deleteMultiple(Request $request)
    {
        // 1. Validasi input array ID
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:barangs,id', // Pastikan setiap ID ada di tabel
        ]);

        $ids = $request->ids;

        if (empty($ids)) {
            return redirect()->route('barang.index')->with('error', 'Tidak ada barang yang dipilih untuk dihapus.');
        }

        try {
            DB::beginTransaction();
            
            $deletedCount = 0;
            
            // Lakukan pengecekan satu per satu (PENTING untuk menjaga integritas)
            foreach ($ids as $id) {
                $barang = Barang::findOrFail($id);
                
                // Cek apakah barang pernah dijual atau ada stok
                $stokMasuk = $barang->stokBarangs()->count();
                $stokKeluar = $barang->detailBarangKeluars()->count();

                if ($stokMasuk > 0 || $stokKeluar > 0) {
                    // Skip item ini jika memiliki riwayat
                    continue; 
                }
                
                // PANGGILAN delete() INI SEKARANG BERSIFAT FORCE DELETE
                $barang->delete(); 
                $deletedCount++;
            }

            DB::commit();

            if ($deletedCount > 0) {
                return redirect()->route('barang.index')->with('success', "{$deletedCount} barang berhasil dihapus secara permanen.");
            } else {
                return redirect()->route('barang.index')->with('warning', 'Tidak ada barang yang dihapus. Barang yang dipilih mungkin memiliki riwayat stok/penjualan.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('barang.index')->with('error', 'Gagal menghapus beberapa barang: ' . $e->getMessage());
        }
    }
    // ========================================================


    /**
     * Menghapus barang dari database (SINGLE FORCE DELETE).
     */
    public function destroy(Barang $barang)
    {
        // Cek dulu apakah barang ini pernah dijual atau ada stok
        $stokMasuk = $barang->stokBarangs()->count();
        $stokKeluar = $barang->detailBarangKeluars()->count();

        if ($stokMasuk > 0 || $stokKeluar > 0) {
            return redirect()->route('barang.index')->with('error', 'Barang tidak bisa dihapus karena memiliki riwayat stok atau penjualan.');
        }

        try {
            // PANGGILAN delete() INI SEKARANG BERSIFAT FORCE DELETE
            $barang->delete();
            return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus secara permanen.');
        } catch (\Exception $e) {
            return redirect()->route('barang.index')->with('error', 'Barang tidak bisa dihapus.');
        }
    }

    // ===================================
    // === FUNGSI IMPORT/EXPORT YANG SUDAH ADA ===
    // ===================================

    public function showImportForm()
    {
        return view('barang.import'); 
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_barang' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new BarangImport, $request->file('file_barang'));
            
            return redirect()->route('barang.index')->with('success', 'Data barang berhasil di-import.');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
             return redirect()->back()->with('error', 'Gagal, ada error validasi di file Excel.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal meng-import data. Pastikan format file Anda benar. Pesan error: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        return Excel::download(new BarangExport, 'daftar_master_barang.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new BarangTemplateExport, 'template_master_barang.xlsx');
    }
}