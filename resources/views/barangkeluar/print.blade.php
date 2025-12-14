<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Nota - {{ $barangKeluar->id_transaksi }}</title>
    <style>
        /* === PENGATURAN KERTAS === */
        @page { size: 21.5cm 14cm landscape; margin: 1cm 1.5cm; }
        
        /* === GLOBAL STYLE: PAKSA SEMUA JADI TEBAL === */
        * {
            font-weight: bold !important;
        }

        body, table, th, td, p, span, div, h1, h2, h3, h4, h5, h6, ul, li, strong, b {
            font-family: 'Arial', sans-serif; 
            font-size: 10pt;
            color: #000; 
            line-height: 1.2; 
            margin: 0; 
            padding: 0;
            font-weight: bold !important; /* Pastikan Bold */
        }

        /* === UTILITIES === */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        /* === HEADER === */
        .header { 
            border-bottom: 3px solid #000; 
            padding-bottom: 8px; 
            margin-bottom: 12px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
        }
        .header-left { display: flex; align-items: center; }
        .logo { height: 55px; width: auto; margin-right: 15px; object-fit: contain; }
        
        .company-info h1 { font-size: 16pt; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .company-info p { font-size: 9pt; margin: 0; }

        .nota-title { 
            border: 3px solid #000; 
            padding: 4px 15px; 
            font-size: 16pt; 
            display: inline-block; 
            text-transform: uppercase;
        }

        /* === INFO SECTION === */
        .info-section { display: flex; justify-content: space-between; margin-bottom: 10px; gap: 15px; }
        .info-box { width: 48%; border: 2px solid #000; padding: 6px 8px; font-size: 9pt; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { vertical-align: top; padding: 2px 0; }
        .label { width: 85px; }

        /* === TABEL BARANG === */
        .items-table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 8px; }
        .items-table th { 
            border: 2px solid #000; 
            background-color: #d1d5db; 
            padding: 6px; 
            text-align: center; 
            text-transform: uppercase;
        }
        .items-table td { 
            border: 1px solid #000; 
            padding: 5px; 
            vertical-align: middle; 
        }
        .col-no { width: 30px; text-align: center; }
        
        /* === FOOTER === */
        .footer-section { display: flex; justify-content: space-between; margin-top: 5px; }
        .notes-box { width: 55%; font-size: 8pt; font-style: italic; border: 1px solid #000; padding: 6px; height: fit-content; }
        .totals-box { width: 40%; }
        
        .total-row { display: flex; justify-content: space-between; font-size: 10pt; padding: 2px 0; }
        
        .grand-total { 
            border-top: 2px solid #000; 
            border-bottom: 4px double #000; 
            padding: 5px 0; margin: 3px 0; 
            font-size: 12pt; 
        }

        /* === TANDA TANGAN === */
        .signatures { margin-top: 20px; display: flex; justify-content: space-between; font-size: 10pt; text-align: center; }
        .sig-box { width: 30%; }
        .sig-space { height: 50px; }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div class="header-left">
            @php $pathLogo = storage_path('app/public/image/logo.png'); if (!file_exists($pathLogo)) { $pathLogo = public_path('image/logo.png'); } @endphp
            @if(file_exists($pathLogo)) <img src="data:image/png;base64,{{ base64_encode(file_get_contents($pathLogo)) }}" class="logo" alt="Logo"> @else <div style="font-size:24px; margin-right:15px;">[LOGO]</div> @endif
            <div class="company-info">
                <h1>CV. BIMA PERAGA NUSANTARA</h1>
                <p>Gg. Tower 4A Unggahan, Banjarangung, Mojokerto</p>
                <p>Telp: 0321-330850 | Email: bimaperaga.com</p>
            </div>
        </div>
        <div><div class="nota-title">NOTA PENJUALAN</div></div>
    </div>

    <div class="info-section">
        <div class="info-box"><table class="info-table"><tr><td class="label">No. Nota</td><td>: {{ $barangKeluar->id_transaksi }}</td></tr><tr><td class="label">Tanggal</td><td>: {{ \Carbon\Carbon::parse($barangKeluar->tanggal)->locale('id')->isoFormat('D MMMM Y') }}</td></tr><tr><td class="label">Kasir</td><td>: {{ $barangKeluar->user->name ?? 'Admin' }}</td></tr></table></div>
        <div class="info-box"><table class="info-table"><tr><td class="label">Kepada Yth.</td><td>: {{ $barangKeluar->pelanggan->nama_pelanggan ?? 'Umum' }}</td></tr><tr><td class="label">Alamat</td><td>: {{ $barangKeluar->pelanggan->alamat ?? '-' }}</td></tr><tr><td class="label">Telp</td><td>: {{ $barangKeluar->pelanggan->no_telp ?? '-' }}</td></tr></table></div>
    </div>

    <table class="items-table">
        <thead><tr><th class="col-no">No</th><th>Kode</th><th>Nama Barang</th><th class="col-qty">Qty</th><th>Harga</th><th>Jumlah</th></tr></thead>
        <tbody>
            @php $maxRows=10; $itemCount=count($barangKeluar->detailBarangKeluars); $emptyRows=max(0,$maxRows-$itemCount); @endphp
            @foreach($barangKeluar->detailBarangKeluars as $index => $detail)<tr><td class="text-center">{{ $index + 1 }}</td><td class="text-center">{{ $detail->barang->kode_barang ?? '-' }}</td><td>{{ \Illuminate\Support\Str::limit($detail->barang->nama_barang ?? 'Item Terhapus', 45) }}</td><td class="text-center">{{ $detail->jumlah }}</td><td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td><td class="text-right">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td></tr>@endforeach
            @for($i=0;$i<$emptyRows;$i++)<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>@endfor
        </tbody>
    </table>

    <div class="footer-section">
        <div class="notes-box">
            <strong>Catatan:</strong>
            <ul style="margin: 2px 0 0 15px; padding:0;">
                <li>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</li>
                <li>Pembayaran via Transfer:<br>
                    <div style="margin-top: 5px; margin-left: 5px;">
                        <strong>Rekening A.n Sri Winanti</strong><br>
                        <table style="width: auto; border: none; font-size: 8pt; margin-top: 2px;">
                            <tr><td style="padding: 0; width: 60px;">Mandiri</td><td style="padding: 0;">: 142 0021001023</td></tr>
                            <tr><td style="padding: 0;">BCA</td><td style="padding: 0;">: 0501188882</td></tr>
                        </table>
                    </div>
                </li>
            </ul>
        </div>
        <div class="totals-box">
            <div class="total-row"><span>Subtotal Barang :</span><span>Rp {{ number_format($barangKeluar->total_harga - $barangKeluar->biaya_kirim, 0, ',', '.') }}</span></div>
            <div class="total-row"><span>Biaya Kirim :</span><span>Rp {{ number_format($barangKeluar->biaya_kirim, 0, ',', '.') }}</span></div>
            <div class="total-row" style="border-bottom: 2px solid #000;"><span>Uang Muka (DP) :</span><span>Rp {{ number_format($barangKeluar->uang_muka, 0, ',', '.') }}</span></div>
            @php $sisa = $barangKeluar->total_harga - $barangKeluar->uang_muka; @endphp
            <div class="total-row grand-total"><span>TOTAL TAGIHAN :</span><span>Rp {{ number_format($sisa, 0, ',', '.') }}</span></div>
        </div>
    </div>

    <div class="signatures">
        <div class="sig-box"><p>Penerima,</p><div class="sig-space"></div><p>( {{ $barangKeluar->pelanggan->nama_pelanggan ?? '....................' }} )</p></div>
        <div class="sig-box"><p>Hormat Kami,</p><div class="sig-space"></div><p>( {{ $barangKeluar->user->name ?? 'Admin' }} )</p></div>
    </div>
</body>
</html>