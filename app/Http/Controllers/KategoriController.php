<?php

namespace App\Http\Controllers;

use App\Models\Kategori; // <-- PENTING: Panggil Model Kategori
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    /**
     * Menampilkan daftar semua kategori.
     */
    public function index()
    {
        $kategoris = Kategori::paginate(10); // Ambil semua data, 10 per halaman
        return view('kategori.index', ['kategoris' => $kategoris]);
    }

    /**
     * Menampilkan form untuk membuat kategori baru.
     */
    public function create()
    {
        return view('kategori.create');
    }

    /**
     * Menyimpan kategori baru ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi data
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategoris',
        ]);

        // 2. Simpan data
        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        // 3. Redirect ke halaman index
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit kategori.
     */
    public function edit(Kategori $kategori) // Laravel otomatis mencari data berdasarkan ID
    {
        return view('kategori.edit', ['kategori' => $kategori]);
    }

    /**
     * Menyimpan perubahan data kategori ke database.
     */
    public function update(Request $request, Kategori $kategori)
    {
        // 1. Validasi data
        $request->validate([
            // 'unique' perlu dicek agar tidak bentrok dengan nama lain, KECUALI namanya sendiri
            'nama_kategori' => 'required|string|max:255|unique:kategoris,nama_kategori,' . $kategori->id,
        ]);

        // 2. Update data
        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        // 3. Redirect
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diupdate.');
    }

    /**
     * Menghapus kategori dari database.
     */
    public function destroy(Kategori $kategori)
    {
        // HATI-HATI: Nanti kita harus cek dulu apakah kategori ini dipakai oleh barang
        // Untuk sekarang, kita hapus langsung
        
        try {
            $kategori->delete();
            return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            // Ini untuk menangkap error jika kategori tidak bisa dihapus (karena terhubung)
            return redirect()->route('kategori.index')->with('error', 'Kategori tidak bisa dihapus karena masih digunakan.');
        }
    }
}