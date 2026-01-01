<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StokBarangTemplateImport implements FromArray, WithHeadings, WithDrawings, WithCustomStartCell, WithEvents, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['BRG-CONTOH', 10, date('Y-m-d')], // Data Dummy
        ];
    }

    public function headings(): array
    {
        return ['KODE BARANG', 'STOK', 'TANGGAL MASUK'];
    }

    public function startCell(): string
    {
        return 'A7'; // Header mulai di A7
    }

    public function drawings()
    {
        // Logika pencarian logo yang lebih aman (Laravel standard)
        $path = public_path('images/logo_cv.png');
        
        if (file_exists($path)) {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setPath($path);
            $drawing->setHeight(75);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(10);
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

                // === 1. FORMATTING DATA (CRUCIAL) ===
                // Format Kolom A jadi TEXT agar kode "001" tidak berubah jadi "1"
                $sheet->getStyle('A8:A100')
                      ->getNumberFormat()
                      ->setFormatCode(NumberFormat::FORMAT_TEXT);
                
                // Format Kolom C jadi DATE
                $sheet->getStyle('C8:C100')
                      ->getNumberFormat()
                      ->setFormatCode('yyyy-mm-dd');

                // === 2. KOP SURAT ===
                // Merge B1:D1 (bukan C1 agar lebih lebar)
                $sheet->mergeCells('B1:D1');
                $sheet->setCellValue('B1', 'CV. BIMA PERAGA NUSANTARA');
                $sheet->getStyle('B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                $sheet->mergeCells('B2:D2');
                $sheet->setCellValue('B2', 'Gg. Tower 4A Unggahan, Banjarangung, Mojokerto');
                
                $sheet->mergeCells('B3:D3');
                $sheet->setCellValue('B3', 'Telp: 0321-330850 | Email: bimaperaga.com');

                // === 3. HEADER STYLE ===
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '4F46E5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                $sheet->getStyle('A7:C7')->applyFromArray($headerStyle);
                
                // === 4. INSTRUCTION ===
                $sheet->mergeCells('A6:C6');
                $sheet->setCellValue('A6', '*Isi data mulai baris 8. Format Tanggal: YYYY-MM-DD');
                $sheet->getStyle('A6')->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));

                // Atur Lebar
                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(20);
            },
        ];
    }
}