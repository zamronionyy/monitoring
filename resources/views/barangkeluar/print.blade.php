<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Nota - {{ $barangKeluar->id_transaksi }}</title>
    <style>
        /* === PENGATURAN KERTAS === */
        /* DIUBAH: Tinggi kertas jadi 15cm */
        @page { 
            size: 21cm 15cm landscape; 
            margin: 1.5cm 0.5cm 0.2cm 0.5cm; 
        }
        
        /* === GLOBAL STYLE === */
        * {
            font-weight: 900 !important;
            text-shadow: 0px 0px 0.5px #000; 
            -webkit-font-smoothing: antialiased;
            box-sizing: border-box;
        }

        body, table, th, td, p, span, div, h1, h2, h3, h4, h5, h6, ul, li, strong, b {
            font-family: 'Arial', sans-serif; 
            font-size: 9pt; 
            color: #000; 
            line-height: 1; 
            margin: 0; 
            padding: 0;
            font-weight: 900 !important; 
        }

        /* === HEADER === */
        .header { 
            border-bottom: 2px solid #000; 
            padding-bottom: 2px; 
            margin-bottom: 2px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            height: 50px; 
            overflow: hidden;
        }
        .header-left { display: flex; align-items: center; }
        
        .logo { 
            height: 40px; 
            width: auto; 
            margin-right: 10px; 
            object-fit: contain; 
        }
        
        .company-info h1 { 
            font-size: 14pt; 
            margin: 0; 
            text-transform: uppercase; 
        }
        .company-info p { font-size: 8pt; margin: 0; }

        .nota-title { 
            border: 2px solid #000; 
            padding: 2px 8px; 
            font-size: 14pt; 
            display: inline-block; 
            text-transform: uppercase;
        }

        /* === INFO SECTION === */
        .info-section { display: flex; justify-content: space-between; margin-bottom: 2px; gap: 5px; }
        .info-box { width: 49%; border: 1px solid #000; padding: 2px; font-size: 8pt; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { vertical-align: top; padding: 0; }
        .label { width: 60px; } 

        /* === TABEL BARANG === */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
        
        .items-table th { 
            border: 1px solid #000; 
            background-color: #d1d5db; 
            padding: 2px; 
            text-align: center; 
            text-transform: uppercase;
            font-size: 8pt; 
        }
        
        .items-table td { 
            border: 1px solid #000; 
            padding: 1px 3px; 
            vertical-align: middle;
            font-size: 8pt; 
            height: 14px; 
        }
        
        .col-item-name {
            white-space: normal; 
        }
        
        .col-no { width: 20px; text-align: center; }
        
        /* === FOOTER === */
        .footer-section { display: flex; justify-content: space-between; margin-top: 2px; }
        .notes-box { width: 58%; font-size: 7pt; font-style: italic; border: 1px solid #000; padding: 2px; height: fit-content; }
        .totals-box { width: 38%; }
        
        .total-row { display: flex; justify-content: space-between; font-size: 8pt; padding: 0; }
        
        .grand-total { 
            border-top: 1px solid #000; 
            border-bottom: 3px double #000; 
            padding: 1px 0; margin: 1px 0; 
            font-size: 10pt;
        }

        /* === TANDA TANGAN (BAGIAN YANG DIUBAH) === */
        .signatures { 
            margin-top: 5px; /* Tambah jarak sedikit dari footer */
            display: flex; 
            justify-content: space-between; 
            font-size: 9pt; 
            text-align: center; 
        }
        .sig-box { width: 40%; } 
        
        /* DIUBAH: Tinggi area tanda tangan diperbesar agar "lebar ke bawah" */
        .sig-space { 
            height: 60px; /* Jarak vertikal tanda tangan */
        } 

        .sig-name {
            white-space: nowrap; 
            overflow: hidden;
            text-overflow: ellipsis;
            padding-top: 5px; /* Tambahan padding top kecil tepat di atas nama */
        }

        /* KHUSUS PRINT */
        @media print {
            /* DIUBAH: Ikuti ukuran baru (15cm) */
            @page { margin: 1.5cm 0.5cm 0.2cm 0.5cm; }
            body { margin: 0; }
            /* Safety net diperbesar ke 14.9cm karena kertas sekarang 15cm */
            html, body { height: 14.9cm; overflow: hidden; } 
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <img src="/images/logo_cv.png" 
                 class="logo" 
                 alt="Logo CV"
                 onerror="this.style.display='none'; document.getElementById('logo-text').style.display='block';">
            <div id="logo-text" style="display:none; font-size:20px; margin-right:10px; border:2px solid #000; padding:2px;">[LOGO]</div>

            <div class="company-info">
                <h1>CV. BIMA PERAGA NUSANTARA</h1>
                <p>Gg. Tower 4A Unggahan, Banjarangung, Mojokerto</p>
                <p>Telp: 0321-330850 | Email: bimaperaga@gmail.com</p>
            </div>
        </div>
        <div><div class="nota-title">NOTA PENJUALAN</div></div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <table class="info-table">
                <tr><td class="label">No. Nota</td><td>: {{ $barangKeluar->id_transaksi }}</td></tr>
                <tr><td class="label">Tanggal</td><td>: {{ \Carbon\Carbon::parse($barangKeluar->tanggal)->locale('id')->isoFormat('D MMMM Y') }}</td></tr>
                <tr><td class="label">Kasir</td><td>: {{ $barangKeluar->user->name ?? 'Admin' }}</td></tr>
            </table>
        </div>
        <div class="info-box">
            <table class="info-table">
                <tr><td class="label">Pelanggan</td><td>: {{ $barangKeluar->pelanggan->nama_pelanggan ?? 'Umum' }}</td></tr>
                <tr><td class="label">Alamat</td><td>: {{ $barangKeluar->pelanggan->alamat ?? '-' }}</td></tr>
                <tr><td class="label">Telp</td><td>: {{ $barangKeluar->pelanggan->no_telp ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    <table class="items-table">
        <thead><tr><th class="col-no">No</th><th>Kode</th><th>Nama Barang</th><th class="col-qty">Qty</th><th>Harga</th><th>Jumlah</th></tr></thead>
        <tbody>
            @php $maxRows=10; $itemCount=count($barangKeluar->detailBarangKeluars); $emptyRows=max(0,$maxRows-$itemCount); @endphp
            @foreach($barangKeluar->detailBarangKeluars as $index => $detail)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $detail->barang->kode_barang ?? '-' }}</td>
                <td class="col-item-name">{{ $detail->barang->nama_barang ?? 'Item Terhapus' }}</td>
                <td class="text-center">{{ $detail->jumlah }}</td>
                <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @for($i=0;$i<$emptyRows;$i++)<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>@endfor
        </tbody>
    </table>

    <div class="footer-section">
        <div class="notes-box">
            <strong>Catatan:</strong>
            <ul style="margin: 0 0 0 15px; padding:0;">
                <li>Barang sudah dibeli tidak dpt ditukar/dikembalikan.</li>
                <li>Transfer ke <strong>A.n Sri Winanti</strong>:<br>
                    <div style="display:flex; gap: 10px; margin-top:0;">
                        <span>Mandiri: 142 0021001023</span>
                        <span>BCA: 0501188882</span>
                    </div>
                </li>
            </ul>
        </div>
        <div class="totals-box">
            <div class="total-row"><span>Subtotal :</span><span>Rp {{ number_format($barangKeluar->total_harga - $barangKeluar->biaya_kirim, 0, ',', '.') }}</span></div>
            <div class="total-row"><span>Ongkir :</span><span>Rp {{ number_format($barangKeluar->biaya_kirim, 0, ',', '.') }}</span></div>
            <div class="total-row" style="border-bottom: 1px solid #000;"><span>DP :</span><span>Rp {{ number_format($barangKeluar->uang_muka, 0, ',', '.') }}</span></div>
            @php $sisa = $barangKeluar->total_harga - $barangKeluar->uang_muka; @endphp
            <div class="total-row grand-total"><span>SISA TAGIHAN :</span><span>Rp {{ number_format($sisa, 0, ',', '.') }}</span></div>
        </div>
    </div>

    <div class="signatures">
        <div class="sig-box">
            <p>Penerima,</p>
            <div class="sig-space"></div>
            <p class="sig-name">( {{ $barangKeluar->pelanggan->nama_pelanggan ?? '....................' }} )</p>
        </div>
        <div class="sig-box">
            <p>Hormat Kami,</p>
            <div class="sig-space"></div>
            <p class="sig-name">( {{ $barangKeluar->user->name ?? 'Admin' }} )</p>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener("load", function() {
            window.print();
        });
    </script>
</body>
</html>