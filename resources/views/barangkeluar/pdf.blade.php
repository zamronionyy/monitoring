<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota - {{ $barangKeluar->id_transaksi }}</title>
    <style>
        @page { margin: 1.5cm 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        .header-table { margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .company-name { font-size: 16pt; font-weight: bold; color: #000; margin: 0; text-transform: uppercase; }
        .company-address { font-size: 9pt; margin: 2px 0; color: #555; }
        .nota-title { font-size: 18pt; font-weight: bold; text-align: right; border: 2px solid #333; padding: 5px 15px; display: inline-block; text-transform: uppercase; }
        .info-table { margin-bottom: 20px; } .info-label { font-weight: bold; width: 80px; }
        .items-table { margin-bottom: 20px; }
        .items-table th { background-color: #f3f4f6; border: 1px solid #999; padding: 8px; text-align: center; font-weight: bold; font-size: 9pt; text-transform: uppercase; }
        .items-table td { border: 1px solid #999; padding: 8px; vertical-align: middle; }
        .text-center { text-align: center; } .text-right { text-align: right; }
        .footer-table { margin-top: 10px; }
        .notes { font-size: 9pt; font-style: italic; border: 1px solid #ccc; padding: 10px; background-color: #fcfcfc; }
        .totals-table td { padding: 5px 0; }
        .grand-total { font-size: 12pt; font-weight: bold; color: #000; border-top: 2px solid #333; border-bottom: 2px double #333; padding: 8px 0; }
        .signature-table { margin-top: 50px; text-align: center; }
        .signature-line { border-bottom: 1px solid #333; width: 80%; margin: 60px auto 5px auto; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="60%">
                @php $pathLogo = storage_path('app/public/image/logo.png'); if (!file_exists($pathLogo)) { $pathLogo = public_path('image/logo.png'); } @endphp
                @if(file_exists($pathLogo)) <img src="data:image/png;base64,{{ base64_encode(file_get_contents($pathLogo)) }}" style="height: 60px; float: left; margin-right: 15px;"> @endif
                <div><p class="company-name">CV. BIMA PERAGA NUSANTARA</p>
                    <p class="company-address">Gg. Tower 4A Unggahan, Banjarangung, Mojokerto</p>
                    <p class="company-address">Telp: 0321-330850 | Email:bimaperaga.com</p></div>
            </td>
            <td width="40%" align="right" style="vertical-align: top;"><div class="nota-title">NOTA PENJUALAN</div><p style="margin-top: 5px; font-size: 10pt;">ID: <strong>{{ $barangKeluar->id_transaksi }}</strong></p></td>
        </tr>
    </table>
    <table class="info-table"><tr><td width="50%" style="vertical-align: top; padding-right: 20px;"><table width="100%"><tr><td class="info-label">Kepada Yth</td><td>: <strong>{{ $barangKeluar->pelanggan->nama_pelanggan ?? 'Umum' }}</strong></td></tr><tr><td class="info-label">Alamat</td><td>: {{ $barangKeluar->pelanggan->alamat ?? '-' }}</td></tr><tr><td class="info-label">Telepon</td><td>: {{ $barangKeluar->pelanggan->no_telp ?? '-' }}</td></tr></table></td><td width="50%" style="vertical-align: top; padding-left: 20px;"><table width="100%"><tr><td class="info-label">Tanggal</td><td>: {{ \Carbon\Carbon::parse($barangKeluar->tanggal)->locale('id')->isoFormat('D MMMM Y') }}</td></tr><tr><td class="info-label">Kasir</td><td>: {{ $barangKeluar->user->name ?? 'Admin' }}</td></tr></table></td></tr></table>
    <table class="items-table"><thead><tr><th width="5%">No</th><th width="15%">Kode Barang</th><th width="40%">Nama Barang</th><th width="10%">Qty</th><th width="15%">Harga Satuan</th><th width="15%">Total</th></tr></thead><tbody>@foreach($barangKeluar->detailBarangKeluars as $index => $detail)<tr><td class="text-center">{{ $index + 1 }}</td><td class="text-center">{{ $detail->barang->kode_barang ?? '-' }}</td><td>{{ \Illuminate\Support\Str::limit($detail->barang->nama_barang ?? 'Item Terhapus', 45) }}</td><td class="text-center">{{ $detail->jumlah }}</td><td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td><td class="text-right">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td></tr>@endforeach</tbody></table>
    <table class="footer-table">
        <tr>
            <td width="60%" style="vertical-align: top; padding-right: 20px;"><div class="notes"><strong>Catatan:</strong><ul style="margin: 5px 0 0 15px; padding:0; font-size: 9pt;"><li>Barang yang sudah dibeli tidak dapat ditukar atau dikembalikan.</li><li>Mohon simpan bukti pembayaran ini sebagai jaminan garansi (jika ada).</li><li>Pembayaran via Transfer: <br><strong>BCA 1234567890 a.n CV Bima Peraga Nusantara</strong></li></ul></div></td>
            <td width="40%" style="vertical-align: top;">
                <table class="totals-table" width="100%">
                    <tr><td>Subtotal Barang</td><td class="text-right">Rp {{ number_format($barangKeluar->total_harga - $barangKeluar->biaya_kirim, 0, ',', '.') }}</td></tr>
                    <tr><td>Biaya Kirim (Ongkir)</td><td class="text-right">Rp {{ number_format($barangKeluar->biaya_kirim, 0, ',', '.') }}</td></tr>
                    <tr style="border-bottom: 1px dotted #000;"><td>Uang Muka (DP)</td><td class="text-right">Rp {{ number_format($barangKeluar->uang_muka, 0, ',', '.') }}</td></tr>
                    @php $sisa = $barangKeluar->total_harga - $barangKeluar->uang_muka; @endphp
                    <tr><td class="grand-total">TOTAL TAGIHAN</td><td class="text-right grand-total">Rp {{ number_format($sisa, 0, ',', '.') }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="signature-table"><tr><td width="50%"><p>Penerima,</p><div class="signature-line"></div><p><strong>( {{ $barangKeluar->pelanggan->nama_pelanggan ?? '....................' }} )</strong></p></td><td width="50%"><p>Hormat Kami,</p><div class="signature-line"></div><p><strong>( {{ $barangKeluar->user->name ?? 'Admin' }} )</strong></p></td></tr></table>
</body>
</html>