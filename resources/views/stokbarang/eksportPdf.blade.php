<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Barang</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }
        h3 {
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        p {
            text-align: center;
            font-size: 11px;
            margin-top: 0;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>

    <h3>Laporan Stok Barang</h3>
    <p>Bulan: {{ $bulanNama }} {{ $tahun }}</p>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Barang</th>
                <th class="text-center" width="10%">Stok</th>
                <th class="text-right" width="15%">Harga</th>
                <th class="text-right" width="20%">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stokbarang as $i => $barang)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $barang->nama_barang }}</td>
                    <td class="text-center">{{ $barang->stok }}</td>
                    <td class="text-right">Rp {{ number_format($barang->harga, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($barang->stok * $barang->harga, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data stok pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
