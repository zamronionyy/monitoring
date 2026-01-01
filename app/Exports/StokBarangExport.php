<?php

namespace App\Exports;

use App\Models\StokBarang;
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

class StokBarangExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithDrawings, WithCustomStartCell, WithEvents, WithColumnFormatting
{
    public function collection()
    {
        return StokBarang::with(['barang', 'barang.kategori'])
                            ->orderBy('tanggal_masuk', 'desc')
                            ->get();
    }

    public function map($stok): array
    {
        return [
            $stok->id,
            \Carbon\Carbon::parse($stok->tanggal_masuk)->format('d-m-Y'), 
            $stok->barang->kode_barang ?? '-',
            $stok->barang->nama_barang ?? 'Barang Dihapus',
            $stok->barang->kategori->nama_kategori ?? '-',
            $stok->stok,
        ];
    }

    public function headings(): array
    {
        return ['ID TRANSAKSI', 'TANGGAL MASUK', 'KODE BARANG', 'NAMA BARANG', 'KATEGORI', 'JUMLAH MASUK'];
    }

    public function columnFormats(): array
    {
        return ['F' => '0'];
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A7:F7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '4F46E5']], 
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }

    /**
     * PERBAIKAN LOGO UNTUK HOSTING
     */
    public function drawings()
    {
        $possiblePaths = [
            // Cek path standar (Disamakan dengan file lain: images/logo_cv.png)
            public_path('images/logo_cv.png'),
            base_path('../public_html/images/logo_cv.png'),
            $_SERVER['DOCUMENT_ROOT'] . '/images/logo_cv.png',
            
           
            public_path('images/logo_cv.png'),
            base_path('../public_html/images/logo_cv.png'),
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
                
                // Kop Surat
                $sheet->mergeCells('B1:F1'); 
                $sheet->setCellValue('B1', 'CV. BIMA PERAGA NUSANTARA');
                $sheet->getStyle('B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => '111827']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->mergeCells('B2:F2');
                $sheet->setCellValue('B2', 'Gg. Tower 4A Unggahan, Banjarangung, Mojokerto');
                $sheet->getStyle('B2')->applyFromArray(['font' => ['size' => 10, 'color' => ['argb' => '4B5563']]]);

                $sheet->mergeCells('B3:F3');
                $sheet->setCellValue('B3', 'Telp: 0321-330850 | Email:bimaperaga.com');
                $sheet->getStyle('B3')->applyFromArray(['font' => ['size' => 10, 'color' => ['argb' => '4B5563']]]);
                
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);

                // Judul
                $sheet->mergeCells('A5:F5');
                $sheet->setCellValue('A5', 'LAPORAN RIWAYAT STOK BARANG MASUK');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => '4F46E5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '4F46E5']]],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(30);

                // Border
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A7:F' . $highestRow)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']]],
                ]);

                $sheet->getStyle('A8:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); 
                $sheet->getStyle('E8:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); 
                $sheet->getStyle('D8:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(18); 
                $sheet->getColumnDimension('C')->setWidth(20); 
                $sheet->getColumnDimension('D')->setWidth(40); 
                $sheet->getColumnDimension('E')->setWidth(25); 
                $sheet->getColumnDimension('F')->setWidth(15); 
            },
        ];
    }
}