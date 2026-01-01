<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BarangExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithDrawings, WithCustomStartCell, WithEvents, WithColumnFormatting
{
    public function collection()
    {
        return Barang::with('kategori')->get();
    }

    public function map($barang): array
    {
        $stokMasuk = $barang->stokBarangs->sum('stok');
        $stokKeluar = $barang->detailBarangKeluars->sum('jumlah');
        $stokAkhir = $stokMasuk - $stokKeluar;

        return [
            $barang->kode_barang,
            $barang->nama_barang,
            $barang->kategori->nama_kategori ?? '-', 
            $barang->harga, 
            $stokAkhir,
        ];
    }

    public function headings(): array
    {
        return ['KODE BARANG', 'NAMA BARANG', 'KATEGORI', 'HARGA SATUAN (RP)', 'STOK SAAT INI'];
    }

    public function columnFormats(): array
    {
        return ['D' => '#,##0_-', 'E' => '0'];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A6:E6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
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
            $drawing->setHeight(80);
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
                
                $sheet->getRowDimension(1)->setRowHeight(30); 
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(10);
                $sheet->getRowDimension(5)->setRowHeight(30);

                // Kop Surat
                $sheet->mergeCells('B1:E1'); 
                $sheet->setCellValue('B1', 'CV. BIMA PERAGA NUSANTARA');
                $sheet->getStyle('B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => '111827']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->mergeCells('B2:E2');
                $sheet->setCellValue('B2', 'Jl. Raya Contoh No. 123, Kota Bandung, Jawa Barat');
                $sheet->getStyle('B2')->applyFromArray(['font' => ['size' => 11, 'color' => ['argb' => '4B5563']]]);

                $sheet->mergeCells('B3:E3');
                $sheet->setCellValue('B3', 'Email: admin@bima.com | Telp: (022) 1234567');
                $sheet->getStyle('B3')->applyFromArray(['font' => ['size' => 11, 'color' => ['argb' => '4B5563']]]);

                // Judul
                $sheet->mergeCells('A5:E5');
                $sheet->setCellValue('A5', 'LAPORAN MASTER DATA BARANG');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => '4F46E5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '4F46E5']]],
                ]);

                // Border & Align
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A6:E' . $highestRow)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getStyle('A7:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B7:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                $sheet->getStyle('C7:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D7:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('E7:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $sheet->getColumnDimension('A')->setWidth(20); 
                $sheet->getColumnDimension('B')->setWidth(50); 
                $sheet->getColumnDimension('C')->setWidth(25); 
                $sheet->getColumnDimension('D')->setWidth(20); 
                $sheet->getColumnDimension('E')->setWidth(15); 
            },
        ];
    }
}