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
    /**
    * Mengambil data dari database
    */
    public function collection()
    {
        return Barang::with('kategori')->get();
    }

    /**
    * Mapping data ke kolom Excel (NO SYSTEM DIHAPUS)
    */
    public function map($barang): array
    {
        // Hitung stok saat ini
        $stokMasuk = $barang->stokBarangs->sum('stok');
        $stokKeluar = $barang->detailBarangKeluars->sum('jumlah');
        $stokAkhir = $stokMasuk - $stokKeluar;

        return [
            // Kolom A
            $barang->kode_barang,
            // Kolom B
            $barang->nama_barang,
            // Kolom C
            $barang->kategori->nama_kategori ?? '-', 
            // Kolom D
            $barang->harga, 
            // Kolom E
            $stokAkhir,
        ];
    }

    /**
    * Judul Kolom (Header Tabel) - 5 Kolom Saja
    */
    public function headings(): array
    {
        return [
            'KODE BARANG',
            'NAMA BARANG',
            'KATEGORI',
            'HARGA SATUAN (RP)',
            'STOK SAAT INI',
        ];
    }

    /**
    * Format Kolom (Uang & Angka)
    */
    public function columnFormats(): array
    {
        return [
            'D' => '#,##0_-', // Kolom D (Harga)
            'E' => '0',       // Kolom E (Stok)
        ];
    }

    /**
    * Posisi Awal Data (Baris ke-6)
    */
    public function startCell(): string
    {
        return 'A6';
    }

    /**
    * Styling Header Tabel
    */
    public function styles(Worksheet $sheet)
    {
        // Style Header Tabel (Baris 6, Kolom A-E)
        $sheet->getStyle('A6:E6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '4F46E5']], // Indigo
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }

    /**
    * Menambahkan Logo Perusahaan
    */
    public function drawings()
    {
        // Cek path logo secara urut agar pasti ketemu
        $possiblePaths = [
            base_path('public/image/logo.png'),     // Path fisik langsung (Paling aman)
            storage_path('app/public/image/logo.png'),
            public_path('image/logo.png'),
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
            $drawing->setHeight(80); // Tinggi logo
            $drawing->setCoordinates('A1'); // Posisi di cell A1
            
            // Offset agar tidak mepet garis
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
                
                // === 1. PENGATURAN TINGGI BARIS ===
                $sheet->getRowDimension(1)->setRowHeight(30); 
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(10); // Spacer
                $sheet->getRowDimension(5)->setRowHeight(30); // Judul

                // === 2. KOP SURAT (Kolom B sampai E) ===
                
                // Nama Perusahaan
                $sheet->mergeCells('B1:E1'); 
                $sheet->setCellValue('B1', 'CV. BIMA PERAGA NUSANTARA');
                $sheet->getStyle('B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => '111827']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Alamat
                $sheet->mergeCells('B2:E2');
                $sheet->setCellValue('B2', 'Jl. Raya Contoh No. 123, Kota Bandung, Jawa Barat');
                $sheet->getStyle('B2')->applyFromArray(['font' => ['size' => 11, 'color' => ['argb' => '4B5563']]]);

                // Kontak
                $sheet->mergeCells('B3:E3');
                $sheet->setCellValue('B3', 'Email: admin@bima.com | Telp: (022) 1234567');
                $sheet->getStyle('B3')->applyFromArray(['font' => ['size' => 11, 'color' => ['argb' => '4B5563']]]);

                // === 3. JUDUL LAPORAN (Tengah A-E) ===
                $sheet->mergeCells('A5:E5');
                $sheet->setCellValue('A5', 'LAPORAN MASTER DATA BARANG');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => '4F46E5']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '4F46E5']]],
                ]);

                // === 4. BORDER & ALIGNMENT DATA ===
                $highestRow = $sheet->getHighestRow();
                
                // Border Tabel (A6 sampai E terakhir)
                $sheet->getStyle('A6:E' . $highestRow)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Alignment Kolom Spesifik
                // A: Kode (Tengah)
                $sheet->getStyle('A7:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // B: Nama (Kiri, Wrap Text jika panjang)
                $sheet->getStyle('B7:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
                
                // C: Kategori (Tengah)
                $sheet->getStyle('C7:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // D: Harga (Kanan)
                $sheet->getStyle('D7:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // E: Stok (Tengah)
                $sheet->getStyle('E7:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // === 5. LEBAR KOLOM MANUAL ===
                $sheet->getColumnDimension('A')->setWidth(20); // Kode
                $sheet->getColumnDimension('B')->setWidth(50); // Nama Barang (Lebar)
                $sheet->getColumnDimension('C')->setWidth(25); // Kategori
                $sheet->getColumnDimension('D')->setWidth(20); // Harga
                $sheet->getColumnDimension('E')->setWidth(15); // Stok
            },
        ];
    }
}