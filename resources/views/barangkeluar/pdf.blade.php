<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota - {{ $barangKeluar->id_transaksi }}</title>
    <style>
        @page { margin: 1cm 1.5cm; }
        
        /* === GLOBAL STYLE === */
        * {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-weight: bold !important; /* Memaksa semua teks menjadi tebal */
            color: #000; /* Hitam pekat */
        }

        body { 
            font-size: 10pt; 
            line-height: 1.2; 
        }

        table { width: 100%; border-collapse: collapse; }
        
        /* === HEADER === */
        .header-table { margin-bottom: 10px; border-bottom: 3px solid #000; padding-bottom: 8px; }
        .company-name { font-size: 16pt; margin: 0; text-transform: uppercase; }
        .company-address { font-size: 9pt; margin: 2px 0; }
        
        .nota-title-box { 
            border: 3px solid #000; 
            padding: 5px 15px; 
            display: inline-block; 
            font-size: 16pt;
            text-transform: uppercase;
        }

        /* === INFO SECTION === */
        .info-table { margin-bottom: 15px; } 
        .info-box { border: 2px solid #000; padding: 5px; }
        .info-label { width: 80px; }

        /* === ITEM TABLE === */
        .items-table { margin-bottom: 10px; width: 100%; font-size: 9pt; }
        .items-table th { 
            background-color: #d1d5db; 
            border: 2px solid #000; 
            padding: 6px; 
            text-align: center; 
            text-transform: uppercase; 
        }
        .items-table td { 
            border: 1px solid #000; 
            padding: 5px; 
            vertical-align: middle; 
        }
        
        /* === UTILS === */
        .text-center { text-align: center; } 
        .text-right { text-align: right; }
        
        /* === FOOTER === */
        .footer-table { margin-top: 5px; }
        .notes-box { 
            border: 1px solid #000; 
            padding: 6px; 
            font-size: 8pt; 
            font-style: italic; 
        }
        
        .totals-table td { padding: 2px 0; font-size: 10pt; }
        .grand-total { 
            font-size: 12pt; 
            border-top: 2px solid #000; 
            border-bottom: 4px double #000; 
            padding: 5px 0; 
            margin-top: 5px;
        }

        /* === SIGNATURE === */
        .signature-table { margin-top: 20px; text-align: center; }
        .signature-line { height: 50px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="60%">
                
                @php
        
                    $possiblePaths = [
                        $_SERVER['DOCUMENT_ROOT'] . '/images/logo_cv.png', // Prioritas 1: public_html/images/
                        $_SERVER['DOCUMENT_ROOT'] . '/image/logo_cv.png',  // Prioritas 2: public_html/image/
                        public_path('images/logo_cv.png'),                 // Fallback Laravel default
                    ];

                    $finalPath = null;
                    foreach ($possiblePaths as $path) {
                        if (file_exists($path)) {
                            $finalPath = $path;
                            break;
                        }
                    }
                @endphp

                @if($finalPath)
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($finalPath)) }}" style="height: 55px; float: left; margin-right: 15px;">
                @else
                    {{-- Debugging Text jika tetap gagal (Hapus nanti jika sudah berhasil) --}}
                    <div style="float:left; margin-right:15px; border:1px dashed red; padding:5px; font-size:9px;">
                        Logo 404.<br>Cek: public_html/images
                    </div>
                @endif
                
                <div>
                    <p class="company-name">CV. BIMA PERAGA NUSANTARA</p>
                    <p class="company-address">Gg. Tower 4A Unggahan, Banjarangung, Mojokerto</p>
                    <p class="company-address">Telp: 0321-330850 | Email: bimaperaga.com</p>
                </div>
            </td>
            <td width="40%" align="right" style="vertical-align: middle;">
                <div class="nota-title-box">NOTA PENJUALAN</div>
            </td>
        </tr>
    </table>

    {{-- INFO TRANSAKSI --}}
    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr>
            <td width="48%" style="vertical-align: top;">
                <div class="info-box">
                    <table width="100%">
                        <tr><td class="info-label">No. Nota</td><td>: {{ $barangKeluar->id_transaksi }}</td></tr>
                        <tr><td class="info-label">Tanggal</td><td>: {{ \Carbon\Carbon::parse($barangKeluar->tanggal)->locale('id')->isoFormat('D MMMM Y') }}</td></tr>
                        <tr><td class="info-label">Kasir</td><td>: {{ $barangKeluar->user->name ?? 'Admin' }}</td></tr>
                    </table>
                </div>
            </td>
            <td width="4%"></td>
            <td width="48%" style="vertical-align: top;">
                <div class="info-box">
                    <table width="100%">
                        <tr><td class="info-label">Kepada Yth.</td><td>: {{ $barangKeluar->pelanggan->nama_pelanggan ?? 'Umum' }}</td></tr>
                        <tr><td class="info-label">Alamat</td><td>: {{ $barangKeluar->pelanggan->alamat ?? '-' }}</td></tr>
                        <tr><td class="info-label">Telp</td><td>: {{ $barangKeluar->pelanggan->no_telp ?? '-' }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- TABEL BARANG --}}
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode</th>
                <th width="40%">Nama Barang</th>
                <th width="10%">Qty</th>
                <th width="15%">Harga</th>
                <th width="15%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($barangKeluar->detailBarangKeluars as $index => $detail)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $detail->barang->kode_barang ?? '-' }}</td>
                <td>{{ $detail->barang->nama_barang ?? 'Item Terhapus' }}</td>
                <td class="text-center">{{ $detail->jumlah }}</td>
                <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- FOOTER --}}
    <table class="footer-table">
        <tr>
            <td width="55%" style="vertical-align: top; padding-right: 15px;">
                <div class="notes-box">
                    <strong>Catatan:</strong>
                    <ul style="margin: 2px 0 0 15px; padding:0;">
                        <li>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</li>
                        <li>Pembayaran via Transfer:<br>
                            <div style="margin-top: 5px; margin-left: 5px;">
                                <strong>Rekening A.n Sri Winanti</strong><br>
                                <table style="width: 100%; border: none; font-size: 8pt; margin-top: 2px;">
                                    <tr><td style="padding: 0; width: 60px;">Mandiri</td><td style="padding: 0;">: 142 0021001023</td></tr>
                                    <tr><td style="padding: 0;">BCA</td><td style="padding: 0;">: 0501188882</td></tr>
                                </table>
                            </div>
                        </li>
                    </ul>
                </div>
            </td>
            <td width="45%" style="vertical-align: top;">
                <table class="totals-table" width="100%">
                    <tr>
                        <td>Subtotal Barang</td>
                        <td class="text-right">: Rp {{ number_format($barangKeluar->total_harga - $barangKeluar->biaya_kirim, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Biaya Kirim</td>
                        <td class="text-right">: Rp {{ number_format($barangKeluar->biaya_kirim, 0, ',', '.') }}</td>
                    </tr>
                    <tr style="border-bottom: 2px solid #000;">
                        <td>Uang Muka (DP)</td>
                        <td class="text-right">: Rp {{ number_format($barangKeluar->uang_muka, 0, ',', '.') }}</td>
                    </tr>
                    @php $sisa = $barangKeluar->total_harga - $barangKeluar->uang_muka; @endphp
                    <tr>
                        <td class="grand-total">TOTAL TAGIHAN</td>
                        <td class="text-right grand-total">Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="signature-table">
        <tr>
            <td width="50%">
                <p>Penerima,</p>
                <div class="signature-line"></div>
                <p>( {{ $barangKeluar->pelanggan->nama_pelanggan ?? '....................' }} )</p>
            </td>
            <td width="50%">
                <p>Hormat Kami,</p>
                <div class="signature-line"></div>
                <p>( {{ $barangKeluar->user->name ?? 'Admin' }} )</p>
            </td>
        </tr>
    </table>
</body>
</html>