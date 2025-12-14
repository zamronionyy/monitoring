<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // (Ini untuk kode otomatis)

class PelangganController extends Controller
{
    /**
     * Menampilkan daftar semua pelanggan.
     */
    public function index()
    {
        $pelanggans = Pelanggan::paginate(10);
        return view('pelanggan.index', ['pelanggans' => $pelanggans]);
    }

    /**
     * Menampilkan form untuk membuat pelanggan baru.
     */
    public function create()
    {
        return view('pelanggan.create');
    }

    /**
     * Menyimpan pelanggan baru ke database.
     */
    public function store(Request $request)
    {
        // 1. Validasi data
        $request->validate([
            // --- INI YANG DIPERBARUI ---
            // 'regex:/^[a-zA-Z\s]+$/' = Hanya izinkan huruf (a-z, A-Z) dan spasi
            'nama_pelanggan' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            
            // 'numeric' = Hanya izinkan angka
            // 'digits_between:8,15' = Opsional: pastikan panjangnya antara 8-15 digit
            'no_telp' => ['nullable', 'numeric', 'digits_between:8,15'],
            
            'alamat' => 'nullable|string',
            // --- BATAS PERUBAHAN ---
        ]);

        // 2. Buat Kode Pelanggan Otomatis
        $kodeOtomatis = 'PLG-' . Str::upper(Str::random(6));

        // 3. Simpan data
        Pelanggan::create([
            'kode_pelanggan' => $kodeOtomatis,
            'nama_pelanggan' => $request->nama_pelanggan,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
        ]);

        // 4. Redirect ke halaman index
        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit pelanggan.
     */
    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggan.edit', ['pelanggan' => $pelanggan]);
    }

    /**
     * Menyimpan perubahan data pelanggan ke database.
     */
    public function update(Request $request, Pelanggan $pelanggan)
    {
        // 1. Validasi data
        $request->validate([
            // --- INI YANG DIPERBARUI ---
            'nama_pelanggan' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'no_telp' => ['nullable', 'numeric', 'digits_between:8,15'],
            'alamat' => 'nullable|string',
            // --- BATAS PERUBAHAN ---
        ]);

        // 2. Update data
        $pelanggan->update($request->all()); // 'kode_pelanggan' tidak akan ter-update

        // 3. Redirect
        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diupdate.');
    }

    /**
     * Menghapus pelanggan dari database.
     */
    public function destroy(Pelanggan $pelanggan)
    {
        try {
            $pelanggan->delete();
            return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('pelanggan.index')->with('error', 'Pelanggan tidak bisa dihapus.');
        }
    }
}