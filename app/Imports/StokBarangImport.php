<?php

namespace App\Imports;

use App\Models\StokBarang;
use App\Models\Barang; // Pastikan Model Barang benar
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule; // Tambahan
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StokBarangImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithEvents
{
    public function headingRow(): int
    {
        return 7; // Header ada di baris 7
    }

    /**
     * VALIDASI STRUKTUR FILE (Agar file ngawur ditolak)
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function(BeforeSheet $event) {
                $sheet = $event->sheet;
                
                // Cek Header di A7, B7, C7
                $headerA = strtoupper(trim((string)$sheet->getCell('A7')->getValue()));
                $headerB = strtoupper(trim((string)$sheet->getCell('B7')->getValue()));
                $headerC = strtoupper(trim((string)$sheet->getCell('C7')->getValue()));

                // Validasi Judul Kolom (Fleksibel: Abaikan spasi ekstra)
                if ($headerA !== 'KODE BARANG' || $headerB !== 'STOK' || $headerC !== 'TANGGAL MASUK') {
                    throw ValidationException::withMessages([
                        'file' => 'Format Template Salah! Header harus berada di Baris 7 (KODE BARANG, STOK, TANGGAL MASUK).'
                    ]);
                }
            }
        ];
    }

    /**
     * VALIDASI DATA (Baris per baris)
     */
    public function rules(): array
    {
        return [
            'kode_barang' => function($attribute, $value, $fail) {
                // 1. Jika User lupa menghapus baris contoh, JANGAN ERROR, tapi loloskan saja (nanti di-skip di model)
                if ($value === 'BRG-CONTOH') {
                    return;
                }
                
                // 2. Cek apakah barang ada di database
                $exists = Barang::where('kode_barang', $value)->exists();
                if (!$exists) {
                    $fail("Kode Barang '$value' tidak ditemukan di database.");
                }
            },
            
            'stok' => ['required', 'numeric', 'min:1'],
            
            'tanggal_masuk' => ['required'], // Validasi tanggal dipindah ke logic model agar lebih kuat
        ];
    }

    public function customValidationMessages()
    {
        return [
            'stok.numeric' => 'Kolom Stok harus berupa angka.',
            'stok.min'     => 'Stok minimal 1.',
        ];
    }

    /**
     * PROSES IMPORT KE DATABASE
     */
    public function model(array $row)
    {
        // 1. SKIP DATA CONTOH (Agar tidak masuk database)
        if ($row['kode_barang'] === 'BRG-CONTOH') {
            return null;
        }

        // 2. Cari Barang
        $barang = Barang::where('kode_barang', $row['kode_barang'])->first();
        
        // Jaga-jaga jika validasi lolos tapi data hilang (race condition)
        if (!$barang) return null;

        // 3. Parsing Tanggal (Anti Gagal)
        $tanggal = $row['tanggal_masuk'];
        try {
            if (is_numeric($tanggal)) {
                // Jika format Excel (contoh: 44561)
                $parsedDate = Date::excelToDateTimeObject($tanggal);
            } else {
                // Jika format Text (contoh: 2024-01-01)
                $parsedDate = \Carbon\Carbon::parse($tanggal);
            }
        } catch (\Exception $e) {
            // Jika tanggal error, pakai hari ini
            $parsedDate = now();
        }

        return new StokBarang([
            'id_barang'     => $barang->id, // Sesuaikan dengan nama kolom foreign key di tabel stok_barang Anda
            'stok'          => $row['stok'],
            'tanggal_masuk' => $parsedDate,
        ]);
    }
}