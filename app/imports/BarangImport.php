<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\Kategori;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class BarangImport implements ToModel, WithHeadingRow, WithUpserts
{
    /**
     * Tentukan baris mana yang berisi Header kolom.
     * PENTING: Diatur ke 7 karena Template memiliki Kop Surat di baris 1-6.
     */
    public function headingRow(): int
    {
        return 7;
    }

    /**
     * Tentukan kolom unik untuk Upsert (Update jika ada, Insert jika tidak ada).
     */
    public function uniqueBy()
    {
        return 'kode_barang';
    }

    /**
    * Mapping data dari Excel ke Database
    */
    public function model(array $row)
    {
        // 1. Validasi Data Kosong
        // Pastikan kolom penting ada isinya. Jika kosong, lewati baris ini.
        // Nama key harus huruf kecil sesuai header di Excel (NAMA KATEGORI -> nama_kategori)
        if (empty($row['nama_kategori']) || empty($row['kode_barang'])) {
            return null;
        }

        // 2. Cari atau Buat Kategori (Auto-create)
        $kategori = Kategori::firstOrCreate(
            ['nama_kategori' => $row['nama_kategori']],
            ['nama_kategori' => $row['nama_kategori']]
        );

        // 3. Buat atau Update Barang
        return new Barang([
            'id_kategori'   => $kategori->id,
            'kode_barang'   => $row['kode_barang'],
            'nama_barang'   => $row['nama_barang'],
            'harga'         => $row['harga'] ?? 0, // Default 0 jika kosong
        ]);
    }
}