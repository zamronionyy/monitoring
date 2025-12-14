<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Barang Keluar</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #374151; line-height: 1.5; margin: 0; padding: 0; }
        .header-container { text-align: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 3px solid #4f46e5; }
        .company-name { font-size: 18px; font-weight: bold; color: #111827; text-transform: uppercase; margin-bottom: 5px; }
        .company-address { font-size: 10px; color: #6b7280; margin-bottom: 2px; }
        .report-title { text-align: center; margin-bottom: 25px; }
        .report-title h3 { font-size: 16px; margin: 0 0 5px 0; color: #312e81; }
        .meta-info { font-size: 11px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background-color: #fff; }
        th { background-color: #e0e7ff; color: #3730a3; font-weight: bold; text-transform: uppercase; font-size: 10px; padding: 10px 8px; border-bottom: 2px solid #c7d2fe; text-align: left; }
        th.center, td.center { text-align: center; } th.right, td.right { text-align: right; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        tfoot tr td { background-color: #f3f4f6; font-weight: bold; border-top: 2px solid #9ca3af; color: #111827; }
        .signature-section { margin-top: 50px; width: 100%; }
        .signature-box { width: 40%; float: right; text-align: center; }
        .signature-line { margin-top: 60px; border-bottom: 1px solid #374151; width: 80%; margin-left: auto; margin-right: auto; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>

    <div class="header-container">
        <div class="company-name">CV. BIMA PERAGA NUSANTARA</div>
        <div class="company-address">Unggahan Gg.III Banjaragung Puri - Mojokerto | Jl. Diponegoro VI/3 Dinoyo Jatirejo - Mojokerto</div>
        <div class="company-address">Telp. 0321 330850 | 0321 496768 &bull; Email: bimaperaga@gmail.com</div>
    </div>

    @php
        use Carbon\Carbon;
        $namaBulan = $bulan ? Carbon::create()->month((int)$bulan)->translatedFormat('F') : 'Semua Bulan';
        $tahunCetak = $tahun ?? 'Semua Tahun';
        $grandTotal = $barangKeluar->sum('total_harga'); 
    @endphp

    <div class="report-title">
        <h3>LAPORAN BARANG KELUAR</h3>
        <div class="meta-info">Periode: <strong>{{ $namaBulan }} {{ $tahunCetak }}</strong></div>
        <div class="meta-info">Tanggal Cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="center" width="5%">No</th>
                <th width="15%">ID Transaksi</th>
                <th width="20%">Tanggal</th>
                <th width="25%">Nama Pelanggan</th>
                <th width="15%">ID Pelanggan</th>
                <th class="right" width="20%">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($barangKeluar as $item)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td style="font-family: monospace;">{{ $item->id_transaksi }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                    <td>{{ $item->nama_pelanggan }}</td>
                    <td>{{ $item->id_pelanggan }}</td>
                    <td class="right">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center" style="padding: 20px; font-style: italic; color: #999;">
                        Tidak ada data transaksi untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($barangKeluar->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="5" class="right">TOTAL PENDAPATAN</td>
                <td class="right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="signature-section clearfix">
        <div class="signature-box">
            <p>Mojokerto, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p style="margin-bottom: 5px;">Mengetahui,</p>
            <div class="signature-line"></div>
            <p style="font-weight: bold; margin-top: 5px;">Admin / Pimpinan</p>
        </div>
    </div>

</body>
</html>