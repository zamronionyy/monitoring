<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BarangTemplateExport implements WithHeadings, WithDrawings, WithCustomStartCell, WithEvents, ShouldAutoSize
{
    /**
     * Header Kolom (Baris 7)
     * Pastikan urutan dan nama ini sesuai dengan logika di BarangImport.php
     */
    public function headings(): array
    {
        return [
            'NAMA KATEGORI', // Kolom A
            'KODE BARANG',   // Kolom B
            'NAMA BARANG',   // Kolom C
            'HARGA',         // Kolom D
        ];
    }

    /**
     * Posisi Awal Header Tabel
     * (Mulai baris ke-7 agar ada ruang untuk Kop Surat & Logo)
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
            base_path('public/image/logo.png')         // Path Absolut
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
            $drawing->setHeight(75); // Tinggi logo
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            
            return [$drawing];
        }

        return [];
    }

    /**
     * Styling Tampilan (Warna, Border, Lebar Kolom)
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                // === 1. MEMBUAT KOP SURAT ===
                
                // Nama Perusahaan (Geser ke kolom B agar tidak menumpuk logo)
                $sheet->mergeCells('B1:D1');
                $sheet->setCellValue('B1', 'CV. BIMA PERAGA NUSANTARA');
                $sheet->getStyle('B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => '111827']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Alamat
                $sheet->mergeCells('B2:D2');
                $sheet->setCellValue('B2', 'Jl. Raya Contoh No. 123, Kota Bandung, Jawa Barat');
                $sheet->getStyle('B2')->applyFromArray(['font' => ['size' => 10, 'color' => ['argb' => '4B5563']]]);

                // Kontak
                $sheet->mergeCells('B3:D3');
                $sheet->setCellValue('B3', 'Email: admin@bima.com | Telp: (022) 1234567');
                $sheet->getStyle('B3')->applyFromArray(['font' => ['size' => 10, 'color' => ['argb' => '4B5563']]]);

                // Atur Tinggi Baris Header
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(10); // Spasi

                // === 2. JUDUL TEMPLATE ===
                $sheet->mergeCells('A5:D5');
                $sheet->setCellValue('A5', 'TEMPLATE IMPORT DATA BARANG');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => '4F46E5']], // Warna Indigo
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '4F46E5']]],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(25);
                
                // Catatan Penting
                $sheet->mergeCells('A6:D6');
                $sheet->setCellValue('A6', '*PENTING: Isi data mulai baris ke-8. Jangan ubah baris Header (Baris 7).');
                $sheet->getStyle('A6')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'DC2626']], // Merah
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getRowDimension(6)->setRowHeight(20);

                // === 3. HEADER TABEL (Baris 7) ===
                $sheet->getStyle('A7:D7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '4F46E5']], // Background Indigo
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']]],
                ]);
                $sheet->getRowDimension(7)->setRowHeight(25);

                // === 4. CONTOH DATA (Baris 8 - Dummy) ===
                $sheet->setCellValue('A8', 'Alat Peraga (Contoh)');
                $sheet->setCellValue('B8', 'CONTOH-001');
                $sheet->setCellValue('C8', 'Nama Barang Contoh Yang Sangat Panjang Agar Kelihatan Rapi');
                $sheet->setCellValue('D8', '50000');

                // Style khusus baris contoh (Abu-abu)
                $sheet->getStyle('A8:D8')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => '666666']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'F3F4F6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'CCCCCC']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                // Alignment khusus kolom C (Nama) & D (Harga)
                $sheet->getStyle('C8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('D8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // === 5. LEBAR KOLOM (MODIFIKASI DI SINI) ===
                $sheet->getColumnDimension('A')->setWidth(25); // Kategori
                $sheet->getColumnDimension('B')->setWidth(20); // Kode
                
                // PERLEBAR KOLOM NAMA BARANG
                $sheet->getColumnDimension('C')->setWidth(60); // Nama Barang (Diperlebar agar tidak potong)
                $sheet->getStyle('C')->getAlignment()->setWrapText(true); // Agar teks turun ke bawah jika sangat panjang
                
                $sheet->getColumnDimension('D')->setWidth(20); // Harga
            },
        ];
    }
}