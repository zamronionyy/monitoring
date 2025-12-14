<?php

namespace App\Imports;

use App\Models\StokBarang;
use App\Models\Barang;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date; // Library untuk baca tanggal Excel

class StokBarangImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
     * Menentukan baris keberapa Header berada.
     * PENTING: Harus 7 karena di Template ada Kop Surat di baris 1-6.
     */
    public function headingRow(): int
    {
        return 7;
    }

    /**
    * Mapping data dari Excel ke Database
    */
    public function model(array $row)
    {
        // Ambil data berdasarkan nama kolom (slug snake_case)
        $kodeBarang   = $row['kode_barang'] ?? null;
        $stok         = $row['stok'] ?? null;
        $tanggalMasuk = $row['tanggal_masuk'] ?? null;

        // 1. Validasi: Jika data penting kosong, lewati baris ini
        if (empty($kodeBarang) || empty($stok) || empty($tanggalMasuk)) {
            return null; 
        }

        // 2. Cek apakah Kode Barang ada di database master Barang
        $barang = Barang::where('kode_barang', $kodeBarang)->first();
        
        // Jika barang tidak ditemukan, lewati (jangan error)
        if (!$barang) {
            return null; 
        }

        // 3. Parsing Tanggal (Menangani Format Excel Serial Number & Text)
        try {
            if (is_numeric($tanggalMasuk)) {
                // Jika format angka Excel (misal: 45260)
                $parsedDate = Date::excelToDateTimeObject($tanggalMasuk);
            } else {
                // Jika format teks (misal: 2025-12-08)
                $parsedDate = \Carbon\Carbon::parse($tanggalMasuk);
            }
        } catch (\Exception $e) {
            return null; // Skip jika format tanggal rusak
        }

        // 4. Simpan Data ke Database
        return new StokBarang([
            'id_barang'     => $barang->id,
            'stok'          => (int) $stok,
            'tanggal_masuk' => $parsedDate,
        ]);
    }

    /**
     * Memproses per 1000 baris agar tidak memakan banyak memori
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}