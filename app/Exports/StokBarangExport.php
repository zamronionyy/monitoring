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
    /**
    * Mengambil data dari database
    */
    public function collection()
    {
        return StokBarang::with(['barang', 'barang.kategori'])
                            ->orderBy('tanggal_masuk', 'desc')
                            ->get();
    }

    /**
    * Mapping data ke kolom Excel
    */
    public function map($stok): array
    {
        return [
            $stok->id,
            \Carbon\Carbon::parse($stok->tanggal_masuk)->format('d-m-Y'), // Format Tanggal
            $stok->barang->kode_barang ?? '-',
            $stok->barang->nama_barang ?? 'Barang Dihapus',
            $stok->barang->kategori->nama_kategori ?? '-',
            $stok->stok,
        ];
    }

    /**
    * Judul Kolom (Header Tabel)
    */
    public function headings(): array
    {
        return [
            'ID TRANSAKSI',
            'TANGGAL MASUK',
            'KODE BARANG',
            'NAMA BARANG',
            'KATEGORI',
            'JUMLAH MASUK',
        ];
    }

    /**
    * Format Kolom
    */
    public function columnFormats(): array
    {
        return [
            'F' => '0', // Kolom F (Stok) format angka bulat
        ];
    }

    /**
    * Posisi Awal Data (Baris ke-7 agar ada spasi untuk Kop Surat)
    */
    public function startCell(): string
    {
        return 'A7';
    }

    /**
    * Styling Header Tabel (Baris 7)
    */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A7:F7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '4F46E5']], // Indigo Background
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }

    /**
    * Menambahkan Logo Perusahaan
    */
    public function drawings()
    {
        // Logika Path Logo yang Aman (Cek Storage, Public, Base)
        $possiblePaths = [
            storage_path('app/public/image/logo.png'),
            public_path('image/logo.png'),
            public_path('storage/image/logo.png'),
            base_path('public/image/logo.png')
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

    /**
    * Event untuk Styling Lanjutan (Kop Surat & Border)
    */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // === 1. KOP SURAT ===
                
                // Nama Perusahaan
                $sheet->mergeCells('B1:F1'); 
                $sheet->setCellValue('B1', 'CV. BIMA PERAGA NUSANTARA');
                $sheet->getStyle('B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => '111827']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Alamat & Kontak
                $sheet->mergeCells('B2:F2');
                $sheet->setCellValue('B2', 'Gg. Tower 4A Unggahan, Banjarangung, Mojokerto');
                $sheet->getStyle('B2')->applyFromArray(['font' => ['size' => 10, 'color' => ['argb' => '4B5563']]]);

                $sheet->mergeCells('B3:F3');
                $sheet->setCellValue('B3', 'Telp: 0321-330850 | Email:bimaperaga.com');
                $sheet->getStyle('B3')->applyFromArray(['font' => ['size' => 10, 'color' => ['argb' => '4B5563']]]);
                
                // Atur Tinggi Baris Header
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);

                // === 2. JUDUL LAPORAN (Baris 5) ===
                $sheet->mergeCells('A5:F5');
                $sheet->setCellValue('A5', 'LAPORAN RIWAYAT STOK BARANG MASUK');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => '4F46E5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '4F46E5']]],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(30);

                // === 3. BORDER & ALIGNMENT DATA ===
                $highestRow = $sheet->getHighestRow();
                
                // Border Tabel
                $sheet->getStyle('A7:F' . $highestRow)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']]],
                ]);

                // Alignment Kolom (Tengah)
                $sheet->getStyle('A8:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // ID, Tgl, Kode
                $sheet->getStyle('E8:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Kategori, Stok
                
                // Nama Barang (Kiri)
                $sheet->getStyle('D8:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Auto Size Columns Manual Adjust
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(18); // Tanggal
                $sheet->getColumnDimension('C')->setWidth(20); // Kode
                $sheet->getColumnDimension('D')->setWidth(40); // Nama Barang
                $sheet->getColumnDimension('E')->setWidth(25); // Kategori
                $sheet->getColumnDimension('F')->setWidth(15); // Stok
            },
        ];
    }
}