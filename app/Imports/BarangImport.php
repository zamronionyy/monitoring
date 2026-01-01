<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\Kategori;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation; // WAJIB: Untuk Error
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;     // WAJIB: Untuk Cek Header
use Maatwebsite\Excel\Events\BeforeSheet;
use Illuminate\Validation\ValidationException;

class BarangImport implements ToModel, WithHeadingRow, WithUpserts, WithValidation, SkipsEmptyRows, WithEvents
{
    /**
     * Baris Header (Sesuai Template)
     */
    public function headingRow(): int
    {
        return 7;
    }

    /**
     * Kolom Kunci (Update jika Kode Barang sama)
     */
    public function uniqueBy()
    {
        return 'kode_barang';
    }

    /**
     * === EVENT PENGAMAN UTAMA ===
     * Code ini berjalan SEBELUM data dibaca.
     * Fungsinya mendeteksi apakah file ini ASLI template Barang atau file asal-asalan.
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function(BeforeSheet $event) {
                $sheet = $event->sheet;
                
                // 1. Ambil Judul Header dari File yang diupload (Baris 7)
                $hA = strtoupper(trim((string)$sheet->getCell('A7')->getValue())); // NAMA KATEGORI
                $hB = strtoupper(trim((string)$sheet->getCell('B7')->getValue())); // KODE BARANG
                $hC = strtoupper(trim((string)$sheet->getCell('C7')->getValue())); // NAMA BARANG
                $hD = strtoupper(trim((string)$sheet->getCell('D7')->getValue())); // HARGA

                // 2. Cek Kesesuaian Header
                // Jika header tidak sama persis dengan template, TOLAK FILE.
                if ($hA !== 'NAMA KATEGORI' || $hB !== 'KODE BARANG' || $hC !== 'NAMA BARANG' || $hD !== 'HARGA') {
                    throw ValidationException::withMessages([
                        'file' => 'Format Salah! Anda mungkin mengupload file yang salah atau template rusak. Pastikan Header ada di Baris 7 (NAMA KATEGORI, KODE BARANG, NAMA BARANG, HARGA).'
                    ]);
                }

                // 3. Cek Apakah Ada Isinya?
                // getHighestRow() menghitung baris terbawah yang ada datanya.
                // Jika < 8, berarti cuma ada Header (Baris 1-7), datanya kosong.
                $highestRow = $sheet->getHighestRow();
                if ($highestRow < 8) {
                     throw ValidationException::withMessages([
                        'file' => 'File Excel kosong! Silakan isi data barang mulai baris ke-8.'
                    ]);
                }
            }
        ];
    }

    /**
     * Validasi Data per Baris
     */
    public function rules(): array
    {
        return [
            'nama_kategori' => ['required'],
            
            'kode_barang' => function($attribute, $value, $fail) {
                // Skip validasi untuk baris contoh template
                if ($value === 'CONTOH-001') return;

                if (empty($value)) {
                    $fail('Kode Barang wajib diisi.');
                }
            },

            'nama_barang' => ['required'],
            'harga'       => ['required', 'numeric', 'min:0'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_kategori.required' => 'Nama Kategori kosong.',
            'nama_barang.required'   => 'Nama Barang kosong.',
            'harga.required'         => 'Harga kosong.',
            'harga.numeric'          => 'Harga harus angka.',
        ];
    }

    /**
     * Proses Masuk Database
     */
    public function model(array $row)
    {
        // 1. SKIP DATA CONTOH (Agar tidak masuk database)
        if ($row['kode_barang'] === 'CONTOH-001') {
            return null;
        }

        // 2. Logic Kategori (Auto Create)
        $namaKategori = trim($row['nama_kategori']);
        $kategori = Kategori::firstOrCreate(
            ['nama_kategori' => $namaKategori],
            ['nama_kategori' => $namaKategori]
        );

        // 3. Return Model Barang
        return new Barang([
            'id_kategori'   => $kategori->id,
            'kode_barang'   => $row['kode_barang'],
            'nama_barang'   => $row['nama_barang'],
            'harga'         => $row['harga'],
        ]);
    }
}