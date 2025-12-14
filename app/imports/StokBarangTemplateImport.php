<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Agar tulisan tidak terpotong
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StokBarangTemplateImport implements FromArray, WithHeadings, WithDrawings, WithCustomStartCell, WithEvents, ShouldAutoSize
{
    /**
     * Data Contoh (Dummy) agar user paham format pengisian
     */
    public function array(): array
    {
        return [
            // Contoh data dummy di baris pertama data
            ['BRG001', 50, date('Y-m-d')],
        ];
    }

    /**
     * Header Kolom (Baris 7)
     */
    public function headings(): array
    {
        return [
            'KODE BARANG',    // Kolom A
            'STOK',           // Kolom B
            'TANGGAL MASUK',  // Kolom C
        ];
    }

    /**
     * Posisi Awal Header Tabel
     * (Mulai baris ke-7 agar ada ruang untuk Kop Surat & Logo di atasnya)
     */
    public function startCell(): string
    {
        return 'A7';
    }

    /**
     * Menambahkan Logo Perusahaan
     */
    public function drawings()
    {
        // Cek semua kemungkinan lokasi logo agar PASTI KETEMU
        $possiblePaths = [
            storage_path('app/public/image/logo.png'), // Folder Storage Fisik
            public_path('image/logo.png'),             // Folder Public
            base_path('public/image/logo.png')         // Path Absolut Project
        ];

        $validPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $validPath = $path;
                break;
            }
        }

        if ($validPath) {
            $drawing = new Drawing();
            $drawing->setName('Logo Perusahaan');
            $drawing->setDescription('Logo');
            $drawing->setPath($validPath);
            $drawing->setHeight(75); // Tinggi logo disesuaikan
            $drawing->setCoordinates('A1'); // Posisi di kiri atas
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            
            // Kembalikan dalam array
            return [$drawing];
        }

        return [];
    }

    /**
     * Styling Tampilan (Kop Surat, Warna, Border, Lebar Kolom)
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                // === 1. MEMBUAT KOP SURAT (HEADER ATAS) ===
                
                // Nama Perusahaan (Geser ke kolom B agar tidak menumpuk logo)
                $sheet->mergeCells('B1:C1');
                $sheet->setCellValue('B1', 'CV. BIMA PERAGA NUSANTARA');
                $sheet->getStyle('B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => '111827']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Alamat
                $sheet->mergeCells('B2:C2');
                $sheet->setCellValue('B2', 'Gg. Tower 4A Unggahan, Banjarangung, Mojokerto');
                $sheet->getStyle('B2')->applyFromArray(['font' => ['size' => 10, 'color' => ['argb' => '4B5563']]]);

                // Kontak
                $sheet->mergeCells('B3:C3');
                $sheet->setCellValue('B3', 'Telp: 0321-330850 | Email:bimaperaga.com');
                $sheet->getStyle('B3')->applyFromArray(['font' => ['size' => 10, 'color' => ['argb' => '4B5563']]]);

                // Atur Tinggi Baris Header agar Logo Muat
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(10); // Spasi Kosong

                // === 2. JUDUL & PETUNJUK PENGISIAN ===
                $sheet->mergeCells('A5:C5');
                $sheet->setCellValue('A5', 'TEMPLATE IMPORT STOK BARANG MASUK');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => '4F46E5']], // Warna Indigo
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '4F46E5']]],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(25);
                
                // Catatan Penting
                $sheet->mergeCells('A6:C6');
                $sheet->setCellValue('A6', '*PENTING: Isi data mulai baris ke-8. Format Tanggal: YYYY-MM-DD (Contoh: 2024-12-30).');
                $sheet->getStyle('A6')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'DC2626']], // Merah
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getRowDimension(6)->setRowHeight(20);

                // === 3. HEADER TABEL (Baris 7) ===
                $sheet->getStyle('A7:C7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '4F46E5']], // Background Indigo
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']]],
                ]);
                $sheet->getRowDimension(7)->setRowHeight(25);

                // === 4. STYLING CONTOH DATA (Baris 8 - Dummy) ===
                // Memberikan warna abu-abu pada contoh data agar user tahu itu contoh
                $sheet->getStyle('A8:C8')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => '666666']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'F3F4F6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'CCCCCC']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // === 5. LEBAR KOLOM (FIXED) ===
                // Mengatur lebar kolom agar tulisan tidak terpotong (walaupun sudah ada ShouldAutoSize, ini lebih rapi)
                $sheet->getColumnDimension('A')->setWidth(25); // Kode Barang
                $sheet->getColumnDimension('B')->setWidth(15); // Stok
                $sheet->getColumnDimension('C')->setWidth(25); // Tanggal Masuk
            },
        ];
    }
}