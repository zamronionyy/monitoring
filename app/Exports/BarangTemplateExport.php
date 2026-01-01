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
    public function headings(): array
    {
        return [
            'NAMA KATEGORI', // Kolom A
            'KODE BARANG',   // Kolom B
            'NAMA BARANG',   // Kolom C
            'HARGA',         // Kolom D
        ];
    }

    public function startCell(): string
    {
        return 'A7';
    }

 
    public function drawings()
    {
        $possiblePaths = [
          
            public_path('images/logo_cv.png'),
            
            
            base_path('../public_html/images/logo_cv.png'),
            
         
            $_SERVER['DOCUMENT_ROOT'] . '/images/logo_cv.png',
            
          
            storage_path('app/public/images/logo_cv.png'),
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
            $drawing->setHeight(75);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            
            return [$drawing];
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                // Kop Surat
                $sheet->mergeCells('B1:D1');
                $sheet->setCellValue('B1', 'CV. BIMA PERAGA NUSANTARA');
                $sheet->getStyle('B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => '111827']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->mergeCells('B2:D2');
                $sheet->setCellValue('B2', 'Jl. Raya Contoh No. 123, Kota Bandung, Jawa Barat');
                $sheet->getStyle('B2')->applyFromArray(['font' => ['size' => 10, 'color' => ['argb' => '4B5563']]]);

                $sheet->mergeCells('B3:D3');
                $sheet->setCellValue('B3', 'Email: admin@bima.com | Telp: (022) 1234567');
                $sheet->getStyle('B3')->applyFromArray(['font' => ['size' => 10, 'color' => ['argb' => '4B5563']]]);

                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(10); 

                // Judul
                $sheet->mergeCells('A5:D5');
                $sheet->setCellValue('A5', 'TEMPLATE IMPORT DATA BARANG');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => '4F46E5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '4F46E5']]],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(25);
                
                // Catatan
                $sheet->mergeCells('A6:D6');
                $sheet->setCellValue('A6', '*PENTING: Isi data mulai baris ke-8. Jangan ubah baris Header (Baris 7).');
                $sheet->getStyle('A6')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'DC2626']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $sheet->getRowDimension(6)->setRowHeight(20);

                // Header Tabel
                $sheet->getStyle('A7:D7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '4F46E5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']]],
                ]);
                $sheet->getRowDimension(7)->setRowHeight(25);

                // Contoh Data
                $sheet->setCellValue('A8', 'Alat Peraga (Contoh)');
                $sheet->setCellValue('B8', 'CONTOH-001');
                $sheet->setCellValue('C8', 'Nama Barang Contoh Yang Sangat Panjang Agar Kelihatan Rapi');
                $sheet->setCellValue('D8', '50000');

                $sheet->getStyle('A8:D8')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => '666666']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'F3F4F6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'CCCCCC']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                $sheet->getStyle('C8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('D8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Lebar Kolom
                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(60); 
                $sheet->getStyle('C')->getAlignment()->setWrapText(true); 
                $sheet->getColumnDimension('D')->setWidth(20); 
            },
        ];
    }
}